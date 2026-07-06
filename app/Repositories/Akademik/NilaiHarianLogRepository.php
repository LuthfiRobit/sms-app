<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\NilaiHarianLog;
use Illuminate\Support\Collection;

class NilaiHarianLogRepository implements NilaiHarianLogRepositoryInterface
{
    public function __construct(protected NilaiHarianLog $model) {}

    public function getByRombelMapelSemester(int $rombelId, int $mapelId, int $semesterId): Collection
    {
        return $this->model
            ->where('rombel_id', $rombelId)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('semester_id', $semesterId)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
    }

    public function averagesByRombelMapelSemester(int $rombelId, int $mapelId, int $semesterId): Collection
    {
        return $this->model
            ->where('rombel_id', $rombelId)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('semester_id', $semesterId)
            ->selectRaw('peserta_id, AVG(nilai) as rata_rata')
            ->groupBy('peserta_id')
            ->get()
            ->keyBy('peserta_id');
    }

    public function createMany(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        NilaiHarianLog::insert($rows);
    }
}
