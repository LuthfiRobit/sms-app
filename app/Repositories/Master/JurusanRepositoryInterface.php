<?php

namespace App\Repositories\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Master\Jurusan;

interface JurusanRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?Jurusan;
    public function create(array $data): Jurusan;
    public function update(int $id, array $data): Jurusan;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

}
