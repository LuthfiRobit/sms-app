<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\Peserta;

class PesertaRepository implements PesertaRepositoryInterface
{
    public function __construct(protected Peserta $model)
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

    public function findById(int $id, array $with = []): ?Peserta
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): Peserta
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Peserta
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

    public function findByNisn(string $nisn): ?Peserta { return $this->model->where('nisn', $nisn)->first(); }
    public function findByNik(string $nik): ?Peserta { return $this->model->where('nik', $nik)->first(); }
    public function findByUserId(int $userId): ?Peserta { return $this->model->where('user_id', $userId)->first(); }
}
