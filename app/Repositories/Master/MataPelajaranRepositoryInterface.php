<?php

namespace App\Repositories\Master;

use App\Models\Master\MataPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface MataPelajaranRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?MataPelajaran;
    public function create(array $data): MataPelajaran;
    public function update(int $id, array $data): MataPelajaran;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;
}
