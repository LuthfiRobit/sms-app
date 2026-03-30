<?php

namespace App\Repositories\Peserta;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Peserta\PesertaDokumenPribadi;

interface PesertaDokumenPribadiRepositoryInterface
{
    public function all(array $filters = [], array $with = []): Collection;
    public function findById(int $id, array $with = []): ?PesertaDokumenPribadi;
    public function create(array $data): PesertaDokumenPribadi;
    public function update(int $id, array $data): PesertaDokumenPribadi;
    public function delete(int $id): bool;
    public function datatable(array $filters = []): Builder;

}
