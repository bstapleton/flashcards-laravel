<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\GivenAnswer;
use App\Models\Keyword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class AttemptService
{
    public function all(array $keywords = null)
    {
        if (!Gate::authorize('list', Attempt::class)) {
            throw new UnauthorizedException();
        }

        if ($keywords) {
            $chunks = $this->getForSpecifiedTags($keywords);
        } else {
            $chunks = $this->getForAllTags();
        }

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
        $attempt = Attempt::create([
            'user_id' => Auth::id(),
            'question' => $data['question'],
            'correctness' => $data['correctness'],
            'question_type' => $data['question_type'],
            'difficulty' => $data['difficulty'],
            'points_earned' => $data['points_earned'],
            'answered_at' => $data['answered_at'],
            'answers' => $data['answers'],
        ]);

        if ($data['tags']) {
            $keywords = explode(',', $data['tags']);

            foreach($keywords as $keyword) {
                $attempt->keywords()->save(new Keyword(['name' => $keyword]));
            }
        }

        return $attempt;
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

    private function getForAllTags()
    {
        return Attempt::where('user_id', Auth::id())
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
    }

    private function getForSpecifiedTags(array $keywords)
    {
        return Attempt::where('user_id', Auth::id())
            ->whereHas('keywords', function ($query) use ($keywords) {
                $query->whereIn('name', $keywords);
            })
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
    }
}
