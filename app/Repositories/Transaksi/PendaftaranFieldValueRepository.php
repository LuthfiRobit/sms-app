<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\PendaftaranFieldValue;

class PendaftaranFieldValueRepository implements PendaftaranFieldValueRepositoryInterface
{
    public function __construct(protected PendaftaranFieldValue $model)
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

    public function findById(int $id, array $with = []): ?PendaftaranFieldValue
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): PendaftaranFieldValue
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): PendaftaranFieldValue
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

    public function findByPendaftaranId(int $pendaftaranId): Collection
    {
        return $this->model
            ->with('formulirField')
            ->where('pendaftaran_id', $pendaftaranId)
            ->get();
    }

    public function upsertFieldValue(int $pendaftaranId, int $formulirFieldId, mixed $value): PendaftaranFieldValue
    {
        $existing = $this->model
            ->where('pendaftaran_id', $pendaftaranId)
            ->where('formulir_field_id', $formulirFieldId)
            ->first();

        if ($existing) {
            $existing->update(['value' => $value]);
            return $existing;
        }

        return $this->model->create([
            'pendaftaran_id'   => $pendaftaranId,
            'formulir_field_id' => $formulirFieldId,
            'value'            => $value,
        ]);
    }

    public function deleteByPendaftaranId(int $pendaftaranId): int
    {
        return $this->model->where('pendaftaran_id', $pendaftaranId)->delete();
    }
}
