<?php

namespace App\Repositories\Transaksi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Transaksi\DokumenPeserta;

class DokumenPesertaRepository implements DokumenPesertaRepositoryInterface
{
    public function __construct(protected DokumenPeserta $model)
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

    public function findById(int $id, array $with = []): ?DokumenPeserta
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): DokumenPeserta
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): DokumenPeserta
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

    public function findByPendaftaranId(int $pendaftaranId): \Illuminate\Database\Eloquent\Collection { return $this->model->where('pendaftaran_id', $pendaftaranId)->get(); }
    public function verifikasi(int $id, string $status, ?string $keterangan, int $userId): bool { return $this->model->where('id', $id)->update(['status_verifikasi' => $status, 'keterangan_verifikasi' => $keterangan, 'verified_by' => $userId, 'verified_at' => now()]); }
}
