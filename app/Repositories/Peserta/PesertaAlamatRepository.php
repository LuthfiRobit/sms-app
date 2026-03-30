<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaAlamat;

class PesertaAlamatRepository implements PesertaAlamatRepositoryInterface
{
    public function __construct(protected PesertaAlamat $model)
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

    public function findById(int $id, array $with = []): ?PesertaAlamat
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): PesertaAlamat
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): PesertaAlamat
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

    public function findByPesertaId(int $pesertaId): ?PesertaAlamat { return $this->model->where('peserta_id', $pesertaId)->first(); }
    public function upsert(int $pesertaId, array $data): PesertaAlamat { return $this->model->updateOrCreate(['peserta_id' => $pesertaId], $data); }
}
