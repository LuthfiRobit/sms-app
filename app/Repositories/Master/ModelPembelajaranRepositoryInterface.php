<?php

namespace App\Repositories\Master;

interface ModelPembelajaranRepositoryInterface
{
    public function datatable(): mixed;
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): void;
}
