<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\Rpp;
use Illuminate\Database\Eloquent\Builder;

class RppRepository implements RppRepositoryInterface
{
    public function __construct(protected Rpp $model) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->with([
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama',
            'tahunPelajaran:id,nama',
            'semester:id,nama',
            'lembaga:id,nama,kode',
            'modelPembelajaran:id,nama',
        ]);

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }

    public function findById(int $id): ?object
    {
        return $this->model->with([
            'guru:id,nama,gelar_depan,gelar_belakang',
            'mataPelajaran:id,nama',
            'tahunPelajaran:id,nama',
            'semester:id,nama',
            'lembaga:id,nama,kode',
            'modelPembelajaran.sintaks',
            'diverifikasiOleh:id_user,name',
            'nilaiPoin.poin.bagian',
            'inti.sintaks',
            'submateri',
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
        $record = $this->model->findOrFail($id);
        $record->delete();
    }
}
