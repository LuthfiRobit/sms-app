<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\PembukaanPpdb;

interface PembukaanPpdbRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PembukaanPpdb;
    public function create(array $data): PembukaanPpdb;
    public function update(int $id, array $data): PembukaanPpdb;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findAktif(): ?PembukaanPpdb;
    public function toggleStatus(int $id): bool;
}
