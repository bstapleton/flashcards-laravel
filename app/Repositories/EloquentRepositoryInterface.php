<?php

namespace App\Repositories;

interface EloquentRepositoryInterface
{
    public function all();
    public function show(int $id);
    public function store(array $data);
    public function update(array $data, int $id);
    public function destroy(int $id);
    public function random();
}
