<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\RppBagian;

class RppBagianRepository implements RppBagianRepositoryInterface
{
    public function __construct(protected RppBagian $model) {}

    public function all(): mixed
    {
        return $this->model->with(['poin' => fn ($q) => $q->orderBy('urutan')])
            ->orderBy('urutan')
            ->get();
    }

    public function findById(int $id): ?object
    {
        return $this->model->with(['poin' => fn ($q) => $q->orderBy('urutan')])->find($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record;
    }

    public function delete(int $id): void
    {
        $record = $this->model->findOrFail($id);
        $record->delete();
    }
}
