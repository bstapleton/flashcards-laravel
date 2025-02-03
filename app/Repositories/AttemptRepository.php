<?php

namespace App\Repositories;

use App\Models\Attempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class AttemptRepository implements EloquentRepositoryInterface
{
    public function all(): Builder
    {
        return Attempt::where('user_id', Auth::id())
            ->orderBy('answered_at', 'desc');
    }

    public function show(int $id): Attempt
    {
        $attempt = Attempt::where(['id' => $id, 'user_id' => Auth::id()])->first();

        if (!$attempt) {
            throw new ModelNotFoundException();
        }

        return $attempt;
    }

    public function store(array $data): void
    {
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
