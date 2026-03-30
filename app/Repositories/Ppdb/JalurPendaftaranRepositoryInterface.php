<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\JalurPendaftaran;

interface JalurPendaftaranRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?JalurPendaftaran;
    public function create(array $data): JalurPendaftaran;
    public function update(int $id, array $data): JalurPendaftaran;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findAktifByPembukaan(int $pembukaanId): \Illuminate\Database\Eloquent\Collection;
}
