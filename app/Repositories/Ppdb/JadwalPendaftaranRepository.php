<?php

namespace App\Repositories\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ppdb\JadwalPendaftaran;

class JadwalPendaftaranRepository implements JadwalPendaftaranRepositoryInterface
{
    public function __construct(protected JadwalPendaftaran $model)
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

    public function findById(int $id, array $with = []): ?JadwalPendaftaran
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): JadwalPendaftaran
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): JadwalPendaftaran
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

    public function findAktifByJalurAndTipe(int $jalurId, string $tipe): ?JadwalPendaftaran
    {
        return $this->model
            ->where('jalur_pendaftaran_id', $jalurId)
            ->where('tipe', $tipe)
            ->where('status', 'aktif')
            ->where('mulai', '<=', now())
            ->where('selesai', '>=', now())
            ->first();
    }

    public function findAktifByJalur(int $jalurId): Collection
    {
        return $this->model
            ->where('jalur_pendaftaran_id', $jalurId)
            ->where('status', 'aktif')
            ->get();
    }
}
