<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\RppMasterOpsi;

class RppMasterOpsiRepository implements RppMasterOpsiRepositoryInterface
{
    public function __construct(protected RppMasterOpsi $model) {}

    public function byKategori(string $kategori): mixed
    {
        return $this->model->kategori($kategori)->orderBy('urutan')->get();
    }

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
}
