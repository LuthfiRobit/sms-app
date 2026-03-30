<?php

namespace App\Repositories\Master;

use App\Models\Master\ProfilSekolah;
use Illuminate\Database\Eloquent\Collection;

interface ProfilSekolahRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?ProfilSekolah;
    public function getDatatablesData();
}
