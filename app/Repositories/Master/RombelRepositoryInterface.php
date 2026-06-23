<?php

namespace App\Repositories\Master;

use App\Models\Master\Rombel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface RombelRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?Rombel;
    public function create(array $data): Rombel;
    public function update(int $id, array $data): Rombel;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;
}
