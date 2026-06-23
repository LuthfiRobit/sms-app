<?php

namespace App\Repositories\Master;

use App\Models\Master\MataPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MataPelajaranRepository implements MataPelajaranRepositoryInterface
{
    public function __construct(protected MataPelajaran $model) {}

    public function all(array $filters = [], array $with = []): Collection
    {
        $query = $this->model->with($with);
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->orderBy('urutan')->get();
    }

    public function findById(int $id, array $with = []): ?MataPelajaran
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): MataPelajaran
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): MataPelajaran
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        return $record ? $record->delete() : false;
    }

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->query();
        foreach ($filters as $key => $value) {
            if (is_null($value)) {
                $query->whereNull($key);
            } else {
                $query->where($key, $value);
            }
        }
        return $query;
    }
}
