<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\HasilSeleksi;

class HasilSeleksiRepository implements HasilSeleksiRepositoryInterface
{
    public function __construct(protected HasilSeleksi $model)
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

    public function findById(int $id, array $with = []): ?HasilSeleksi
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): HasilSeleksi
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): HasilSeleksi
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

    public function rankingByJalur(int $jalurId): \Illuminate\Database\Eloquent\Collection { return $this->model->whereHas('pendaftaran', function($q) use ($jalurId) { $q->where('jalur_pendaftaran_id', $jalurId); })->orderByDesc('total_nilai')->get(); }
}
