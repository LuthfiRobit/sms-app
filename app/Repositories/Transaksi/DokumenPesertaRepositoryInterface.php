<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\DokumenPeserta;

interface DokumenPesertaRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?DokumenPeserta;
    public function create(array $data): DokumenPeserta;
    public function update(int $id, array $data): DokumenPeserta;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    public function findByPendaftaranId(int $pendaftaranId): \Illuminate\Database\Eloquent\Collection;
    public function verifikasi(int $id, string $status, ?string $keterangan, int $userId): bool;
}
