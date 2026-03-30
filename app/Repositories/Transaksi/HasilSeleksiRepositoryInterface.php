<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\HasilSeleksi;

interface HasilSeleksiRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?HasilSeleksi;
    public function create(array $data): HasilSeleksi;
    public function update(int $id, array $data): HasilSeleksi;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function rankingByJalur(int $jalurId): \Illuminate\Database\Eloquent\Collection;
}
