<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\KuotaJurusan;

interface KuotaJurusanRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?KuotaJurusan;
    public function create(array $data): KuotaJurusan;
    public function update(int $id, array $data): KuotaJurusan;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function incrementTerisi(int $id): bool;
    public function decrementTerisi(int $id): bool;
}
