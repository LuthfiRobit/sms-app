<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\Absensi;
use App\Models\Akademik\AbsensiDetail;
use Illuminate\Database\Eloquent\Builder;

class AbsensiRepository implements AbsensiRepositoryInterface
{
    public function __construct(protected Absensi $model) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->with([
            'rombel:id,nama,tingkat',
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama',
            'lembaga:id,nama',
        ]);

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query->latest('tanggal');
    }

    public function findById(int $id, array $with = []): ?object
    {
        return $this->model->with(array_merge([
            'rombel:id,nama,tingkat',
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama',
            'lembaga:id,nama',
            'detail.peserta:id,nama_lengkap',
        ], $with))->find($id);
    }

    public function findByRombelMapelTanggal(int $rombelId, int $mapelId, string $tanggal): ?object
    {
        return $this->model
            ->where('rombel_id', $rombelId)
            ->where('mata_pelajaran_id', $mapelId)
            ->whereDate('tanggal', $tanggal)
            ->with('detail')
            ->first();
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function saveDetail(int $absensiId, array $details): void
    {
        AbsensiDetail::where('absensi_id', $absensiId)->delete();

        $rows = array_map(fn ($d) => array_merge($d, ['absensi_id' => $absensiId]), $details);

        if (! empty($rows)) {
            AbsensiDetail::insert($rows);
        }
    }

    public function delete(int $id): void
    {
        $record = $this->model->findOrFail($id);
        $record->detail()->delete();
        $record->delete();
    }
}
