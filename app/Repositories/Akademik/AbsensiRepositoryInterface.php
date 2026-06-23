<?php

namespace App\Repositories\Akademik;

interface AbsensiRepositoryInterface
{
    public function datatable(array $filters = []): mixed;
    public function findById(int $id, array $with = []): ?object;
    public function create(array $data): object;
    public function saveDetail(int $absensiId, array $details): void;
    public function delete(int $id): void;
}
