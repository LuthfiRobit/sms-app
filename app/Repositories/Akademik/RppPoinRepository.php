<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\RppPoin;

class RppPoinRepository implements RppPoinRepositoryInterface
{
    public function __construct(protected RppPoin $model) {}

    public function findById(int $id): ?object
    {
        return $this->model->find($id);
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

    /** @param array<int,int> $urutanByid [poin_id => urutan] */
    public function reorder(array $urutanByid): void
    {
        foreach ($urutanByid as $id => $urutan) {
            $this->model->whereKey($id)->update(['urutan' => $urutan]);
        }
    }
}
