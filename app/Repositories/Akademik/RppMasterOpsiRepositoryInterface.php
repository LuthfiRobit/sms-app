<?php

namespace App\Repositories\Akademik;

interface RppMasterOpsiRepositoryInterface
{
    public function byKategori(string $kategori): mixed;
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): void;
}
