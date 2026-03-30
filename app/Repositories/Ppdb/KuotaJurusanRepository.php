<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\KuotaJurusan;

class KuotaJurusanRepository implements KuotaJurusanRepositoryInterface
{
    public function __construct(protected KuotaJurusan $model)
    {
    }

    public function all(array $filters = [], array $with = []): Collection
    {
        $query = $this->model->with($with);
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->get();
    }

    public function findById(int $id, array $with = []): ?KuotaJurusan
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): KuotaJurusan
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): KuotaJurusan
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        throw new \Exception("Record not found");
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
            $query->where($key, $value);
        }
        return $query;
    }

    public function incrementTerisi(int $id): bool { return $this->model->where('id', $id)->increment('terisi'); }
    public function decrementTerisi(int $id): bool { return $this->model->where('id', $id)->decrement('terisi'); }
}
