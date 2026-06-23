<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\Nilai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NilaiRepository implements NilaiRepositoryInterface
{
    public function __construct(protected Nilai $model) {}

    public function getByRombelMapelSemester(int $rombelId, int $mapelId, int $semesterId): Collection
    {
        return $this->model
            ->with('peserta:id,nama_lengkap')
            ->where('rombel_id', $rombelId)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('semester_id', $semesterId)
            ->get();
    }

    public function upsert(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        Nilai::upsert(
            $rows,
            ['rombel_id', 'peserta_id', 'mata_pelajaran_id', 'semester_id'],
            ['nilai_harian', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'catatan']
        );
    }

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model
            ->with([
                'rombel:id,nama,tingkat',
                'mataPelajaran:id,nama',
                'semester:id,nama',
                'lembaga:id,nama',
            ]);

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }
}
