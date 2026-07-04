<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\AbsensiGuru;

interface AbsensiGuruRepositoryInterface
{
    /** Cari absensi guru untuk tanggal tertentu (1 record per hari). */
    public function findByGuruTanggal(int $guruId, string $tanggal): ?AbsensiGuru;

    public function create(array $data): AbsensiGuru;

    public function update(AbsensiGuru $absensi, array $data): AbsensiGuru;

    /** Query berfilter untuk DataTables admin (with guru+lembaga eager loaded). */
    public function datatable(array $filters = []): mixed;

    public function findById(int $id): ?AbsensiGuru;

    public function updateOrCreateByGuruTanggal(int $guruId, int $lembagaId, string $tanggal, array $data): AbsensiGuru;
}
