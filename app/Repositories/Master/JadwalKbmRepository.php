<?php

namespace App\Repositories\Master;

use App\Models\Master\JadwalKbm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class JadwalKbmRepository implements JadwalKbmRepositoryInterface
{
    public function __construct(protected JadwalKbm $model) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->with([
            'rombel:id,nama,tingkat',
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama,kode',
            'lembaga:id,nama,kode',
        ]);

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }

    public function findById(int $id): ?object
    {
        return $this->model->with([
            'rombel:id,nama,tingkat',
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama,kode',
            'lembaga:id,nama,kode',
            'tahunPelajaran:id,nama',
        ])->find($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): void
    {
        $this->model->findOrFail($id)->delete();
    }

    public function getByRombel(int $rombelId): Collection
    {
        return $this->model->with([
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama,kode',
        ])
            ->where('rombel_id', $rombelId)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get();
    }
}
