<?php

namespace App\Repositories;

interface FlashcardRepositoryInterface extends EloquentRepositoryInterface
{
    public function buried();
    public function alive();
    public function revive(int $id);
    public function attachTag(int $id, int $tagId);
    public function detachTag(int $id, int $tagId);
    public function random();
}
