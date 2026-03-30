<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\JalurPendaftaran;

class JalurPendaftaranRepository implements JalurPendaftaranRepositoryInterface
{
    public function __construct(protected JalurPendaftaran $model)
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

    public function findById(int $id, array $with = []): ?JalurPendaftaran
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): JalurPendaftaran
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): JalurPendaftaran
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

    public function findAktifByPembukaan(int $pembukaanId): \Illuminate\Database\Eloquent\Collection { return $this->model->where('pembukaan_ppdb_id', $pembukaanId)->where('status', 'aktif')->get(); }
}
