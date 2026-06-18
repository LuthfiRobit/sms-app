<?php

namespace App\Repositories\Master;

use App\Models\Master\Lembaga;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LembagaRepository implements LembagaRepositoryInterface
{
    public function __construct(protected Lembaga $model)
    {
    }

    public function all(array $filters = [], array $with = []): Collection
    {
        $query = $this->model->with($with)->orderBy('urutan')->orderBy('nama');
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->get();
    }

    public function findById(int $id, array $with = []): ?Lembaga
    {
        return $this->model->with($with)->find($id);
    }

    public function create(array $data): Lembaga
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Lembaga
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->model->findOrFail($id);
        return $record->delete();
    }

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->query();
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function syncAdmins(int $lembagaId, array $userIds): void
    {
        $lembaga = $this->model->findOrFail($lembagaId);
        $lembaga->users()->sync($userIds);
    }
}
