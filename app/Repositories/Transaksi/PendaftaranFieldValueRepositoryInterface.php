<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\PendaftaranFieldValue;

interface PendaftaranFieldValueRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PendaftaranFieldValue;
    public function create(array $data): PendaftaranFieldValue;
    public function update(int $id, array $data): PendaftaranFieldValue;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    /**
     * Ambil semua field values untuk pendaftaran tertentu, beserta relasi formulirField.
     */
    public function findByPendaftaranId(int $pendaftaranId): Collection;

    /**
     * Upsert nilai satu field untuk pendaftaran.
     * Jika sudah ada record dengan pendaftaran_id + formulir_field_id yang sama,
     * lakukan update. Jika belum ada, lakukan insert.
     *
     * @param int    $pendaftaranId     ID pendaftaran
     * @param int    $formulirFieldId   ID field formulir
     * @param mixed  $value             Nilai yang akan disimpan
     */
    public function upsertFieldValue(int $pendaftaranId, int $formulirFieldId, mixed $value): PendaftaranFieldValue;

    /**
     * Hapus semua field values milik pendaftaran tertentu.
     */
    public function deleteByPendaftaranId(int $pendaftaranId): int;
}
