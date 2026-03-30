<?php

namespace App\Repositories\Master;

use App\Models\Master\ProfilSekolah;
use Illuminate\Database\Eloquent\Collection;

class ProfilSekolahRepository implements ProfilSekolahRepositoryInterface
{
    public function getAll(): Collection
    {
        return ProfilSekolah::all();
    }

    public function getById(int $id): ?ProfilSekolah
    {
        return ProfilSekolah::findOrFail($id);
    }

    public function getDatatablesData()
    {
        return ProfilSekolah::query();
    }
}
