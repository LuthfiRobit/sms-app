<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaDokumenPribadi;

class PesertaDokumenPribadiRepository implements PesertaDokumenPribadiRepositoryInterface
{
    public function __construct(protected PesertaDokumenPribadi $model)
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

    public function findById(int $id, array $with = []): ?PesertaDokumenPribadi
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): PesertaDokumenPribadi
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): PesertaDokumenPribadi
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

}
