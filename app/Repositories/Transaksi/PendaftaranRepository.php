<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\Pendaftaran;

class PendaftaranRepository implements PendaftaranRepositoryInterface
{
    public function __construct(protected Pendaftaran $model)
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

    public function findById(int $id, array $with = []): ?Pendaftaran
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): Pendaftaran
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Pendaftaran
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

    public function generateNoPendaftaran(int $tahunPelajaranId): string { $count = $this->model->where('tahun_pelajaran_id', $tahunPelajaranId)->count() + 1; return 'PPDB' . date('Y') . str_pad($count, 5, '0', STR_PAD_LEFT); }
    public function updateStatus(int $id, string $status, array $extra = []): bool { return $this->model->where('id', $id)->update(array_merge(['status' => $status], $extra)); }
    public function findByNoPendaftaran(string $no): ?Pendaftaran { return $this->model->where('no_pendaftaran', $no)->first(); }
}
