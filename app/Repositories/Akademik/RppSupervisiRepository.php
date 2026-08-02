<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\RppSupervisi;
use Illuminate\Database\Eloquent\Builder;

class RppSupervisiRepository implements RppSupervisiRepositoryInterface
{
    public function __construct(protected RppSupervisi $model) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->with([
            'rpp:id,guru_id,mata_pelajaran_id,materi,fase_kelas',
            'rpp.guru:id,nama,gelar_depan,gelar_belakang',
            'rpp.mataPelajaran:id,nama',
            'skor',
        ]);

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query;
    }

    public function findById(int $id): ?object
    {
        return $this->model->with([
            'rpp.guru', 'rpp.mataPelajaran', 'rpp.tahunPelajaran', 'rpp.semester', 'rpp.lembaga',
            'lembaga', 'skor',
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
