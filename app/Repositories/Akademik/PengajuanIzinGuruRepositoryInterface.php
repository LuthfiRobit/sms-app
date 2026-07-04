<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\PengajuanIzinGuru;

interface PengajuanIzinGuruRepositoryInterface
{
    public function datatable(array $filters = []): mixed;

    public function findById(int $id): ?PengajuanIzinGuru;

    public function daftarMilikGuru(int $guruId): mixed;

    public function create(array $data): PengajuanIzinGuru;

    public function update(PengajuanIzinGuru $row, array $data): PengajuanIzinGuru;

    public function hasOverlap(int $guruId, string $mulai, string $akhir, ?int $excludeId = null): bool;
}
