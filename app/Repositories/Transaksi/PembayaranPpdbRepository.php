<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\PembayaranPpdb;

class PembayaranPpdbRepository implements PembayaranPpdbRepositoryInterface
{
    public function __construct(protected PembayaranPpdb $model)
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

    public function findById(int $id, array $with = []): ?PembayaranPpdb
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): PembayaranPpdb
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): PembayaranPpdb
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

    public function findByOrderId(string $orderId): ?PembayaranPpdb { return $this->model->where('order_id', $orderId)->first(); }
    public function updateByOrderId(string $orderId, array $data): bool { return $this->model->where('order_id', $orderId)->update($data); }
}
