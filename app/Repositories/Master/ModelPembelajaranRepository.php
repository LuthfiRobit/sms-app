<?php

namespace App\Repositories\Master;

use App\Models\Master\ModelPembelajaran;
use Illuminate\Database\Eloquent\Builder;

class ModelPembelajaranRepository implements ModelPembelajaranRepositoryInterface
{
    public function __construct(protected ModelPembelajaran $model) {}

    public function datatable(): Builder
    {
        return $this->model->withCount('sintaks')->orderBy('urutan');
    }

    public function findById(int $id): ?object
    {
        return $this->model->with('sintaks')->find($id);
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
