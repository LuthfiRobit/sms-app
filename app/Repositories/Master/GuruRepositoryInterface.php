<?php

namespace App\Repositories\Master;

interface GuruRepositoryInterface
{
    public function datatable(array $filters = []): mixed;
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): void;
}
