<?php

namespace App\Services;

use App\Models\Attempt;
use App\Repositories\AttemptRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\UnauthorizedException;

class AttemptService
{
    protected Attempt $attempt;

    public function __construct(protected AttemptRepository $repository)
    {
    }

    public function all()
    {
        if (!Gate::authorize('list', Attempt::class)) {
            throw new UnauthorizedException();
        }

        return $this->repository->all();
    }

    public function show(int $id)
    {
        $attempt = $this->repository->show($id);

        if (!Gate::authorize('show', $attempt)) {
            throw new UnauthorizedException();
        }

        return $attempt;
    }

    public function destroy(int $id): void
    {
        $flashcard = $this->repository->show($id);

        if (!Gate::authorize('delete', $flashcard)) {
            throw new UnauthorizedException();
        }

        $this->repository->destroy($id);
    }
}
