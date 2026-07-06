<?php

namespace App\Repositories\Akademik;

use Illuminate\Support\Collection;

interface NilaiHarianLogRepositoryInterface
{
    /** Semua entri untuk satu rombel+mapel+semester, terbaru dulu — dipakai membangun riwayat per siswa. */
    public function getByRombelMapelSemester(int $rombelId, int $mapelId, int $semesterId): Collection;

    /** Rata-rata nilai per peserta_id untuk rombel+mapel+semester ini — dasar nilai_harian pada tabel `nilai`. */
    public function averagesByRombelMapelSemester(int $rombelId, int $mapelId, int $semesterId): Collection;

    public function createMany(array $rows): void;
}
