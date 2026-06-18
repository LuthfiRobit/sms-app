<?php

namespace App\Repositories\Master;

use App\Models\Master\Lembaga;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface LembagaRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?Lembaga;
    public function create(array $data): Lembaga;
    public function update(int $id, array $data): Lembaga;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;
    public function syncAdmins(int $lembagaId, array $userIds): void;
}
