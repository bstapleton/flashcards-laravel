<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\GivenAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class AttemptService
{
    public function all()
    {
        if (!Gate::authorize('list', Attempt::class)) {
            throw new UnauthorizedException();
        }

        $chunks = Attempt::where('user_id', Auth::id())
            ->orderBy('answered_at', 'desc')
            ->get()
            ->groupBy('question')
            ->map(function ($group) {
                $latest = $group->first();
                $previousAttempts = $group->filter(function ($attempt) use ($latest) {
                    return $attempt->id !== $latest->id;
                });

                $latest->previous_attempts = $previousAttempts;
                return $latest;
            });

        return new LengthAwarePaginator(
            $chunks,
            count($chunks),
            Auth::user()->page_limit,
        );
    }

    public function show(Attempt $attempt): Attempt
    {
        if (!Gate::authorize('show', $attempt)) {
            throw new UnauthorizedException();
        }

        return $attempt;
    }

    /**
     * Get all other attempts with the same question text
     *
     * @param Attempt $attempt
     * @return Builder
     */
    public function related(Attempt $attempt): Builder
    {
        return Attempt::where('question', Attempt::find($attempt)->question)
            ->where('id', '<>', $attempt->id)
            ->orderBy('answered_at', 'desc');
    }

    public function store(array $data): Attempt
    {
        return Attempt::create([
            'user_id' => Auth::id(),
            'question' => $data['question'],
            'correctness' => $data['correctness'],
            'question_type' => $data['question_type'],
            'difficulty' => $data['difficulty'],
            'points_earned' => $data['points_earned'],
            'answered_at' => $data['answered_at'],
            'answers' => $data['answers'],
            'tags' => $data['tags'],
        ]);
    }

    public function destroy(Attempt $attempt): void
    {
        if (!Gate::authorize('delete', $attempt)) {
            throw new UnauthorizedException();
        }

        $attempt->delete();
    }

    public function createGivenAnswer(string $text, bool $isCorrect, bool $wasSelected, ?int $id = null, ?string $explanation = null)
    {
        $givenAnswer = new GivenAnswer();
        $givenAnswer->setText($text);
        $givenAnswer->setIsCorrect($isCorrect);
        $givenAnswer->setWasSelected($wasSelected);

        if ($id) {
            $givenAnswer->setId($id);
        }

        if ($explanation) {
            $givenAnswer->setExplanation($explanation);
        }

        return $givenAnswer;
    }
}
