<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\JadwalPendaftaran;

interface JadwalPendaftaranRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?JadwalPendaftaran;
    public function create(array $data): JadwalPendaftaran;
    public function update(int $id, array $data): JadwalPendaftaran;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

    /**
     * Cari jadwal aktif untuk jalur tertentu berdasarkan tipe.
     * Digunakan untuk validasi: apakah pendaftaran masih dalam periode.
     *
     * @param int    $jalurId  ID jalur pendaftaran
     * @param string $tipe     Tipe jadwal: 'pendaftaran', 'verifikasi', 'seleksi', dll
     */
    public function findAktifByJalurAndTipe(int $jalurId, string $tipe): ?JadwalPendaftaran;

    /**
     * Ambil semua jadwal aktif untuk suatu jalur pendaftaran.
     */
    public function findAktifByJalur(int $jalurId): Collection;
}
