<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaOrangTua;

class PesertaOrangTuaRepository implements PesertaOrangTuaRepositoryInterface
{
    public function __construct(protected PesertaOrangTua $model)
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

    public function findById(int $id, array $with = []): ?PesertaOrangTua
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): PesertaOrangTua
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): PesertaOrangTua
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

    public function findByPesertaIdAndTipe(int $pesertaId, string $tipe): ?PesertaOrangTua { return $this->model->where('peserta_id', $pesertaId)->where('tipe', $tipe)->first(); }
    public function upsertByTipe(int $pesertaId, string $tipe, array $data): PesertaOrangTua { return $this->model->updateOrCreate(['peserta_id' => $pesertaId, 'tipe' => $tipe], $data); }
}
