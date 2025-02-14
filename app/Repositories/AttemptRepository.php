<?php

namespace App\Repositories;

use App\Models\Attempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AttemptRepository implements EloquentRepositoryInterface
{
    public function all()
    {
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

        $paginator = new LengthAwarePaginator(
            $chunks,
            count($chunks),
            25,
        );

        return $paginator;
    }

    public function show(int $id): Attempt
    {
        $attempt = Attempt::where(['id' => $id, 'user_id' => Auth::id()])->first();

        if (!$attempt) {
            throw new ModelNotFoundException();
        }

        return $attempt;
    }

    public function related(int $id): Builder
    {
        return Attempt::where('question', Attempt::find($id)->question)
            ->where('id', '<>', $id)
            ->orderBy('answered_at', 'desc');
    }

    public function store(array $data)
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

    public function update(array $data, int $id): Attempt
    {
        $this->show($id)->update($data);

        return $this->show($id);
    }

    public function destroy(int $id): void
    {
        $this->show($id)->delete();
    }
}
