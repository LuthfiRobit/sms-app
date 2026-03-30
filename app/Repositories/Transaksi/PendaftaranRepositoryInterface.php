<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\Pendaftaran;

interface PendaftaranRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?Pendaftaran;
    public function create(array $data): Pendaftaran;
    public function update(int $id, array $data): Pendaftaran;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function generateNoPendaftaran(int $tahunPelajaranId): string;
    public function updateStatus(int $id, string $status, array $extra = []): bool;
    public function findByNoPendaftaran(string $no): ?Pendaftaran;
}
