<?php

namespace App\Repositories\Master;

use App\Models\Master\Guru;
use Illuminate\Database\Eloquent\Builder;

class GuruRepository implements GuruRepositoryInterface
{
    public function __construct(protected Guru $model) {}

    public function datatable(array $filters = []): Builder
    {
        $query = $this->model->query()->with('lembaga:id,nama,kode');
        foreach ($filters as $key => $value) {
            if (is_null($value)) {
                $query->whereNull($key);
            } else {
                $query->where($key, $value);
            }
        }
        return $query;
    }

    public function findById(int $id): ?Guru
    {
        return $this->model->with('lembaga:id,nama,kode')->find($id);
    }

    public function create(array $data): Guru
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Guru
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): void
    {
        $this->model->where('id', $id)->delete();
    }
}
