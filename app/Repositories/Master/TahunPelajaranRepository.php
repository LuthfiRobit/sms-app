<?php

namespace App\Repositories\Master;

use App\Models\Master\TahunPelajaran;
use Illuminate\Database\Eloquent\Collection;

class TahunPelajaranRepository implements TahunPelajaranRepositoryInterface
{
    public function getAll(): Collection
    {
        return TahunPelajaran::all();
    }

    public function getById(int $id): ?TahunPelajaran
    {
        return TahunPelajaran::findOrFail($id);
    }

    public function getDatatablesData()
    {
        return TahunPelajaran::query();
    }

    public function create(array $data): TahunPelajaran
    {
        return TahunPelajaran::create($data);
    }

    public function update(int $id, array $data): TahunPelajaran
    {
        $tahun = $this->getById($id);
        $tahun->update($data);
        return $tahun;
    }

    public function delete(int $id): bool
    {
        $tahun = $this->getById($id);
        return $tahun->delete();
    }
}
