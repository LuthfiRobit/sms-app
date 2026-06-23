<?php

namespace App\Repositories\Akademik;

interface AkademikSettingRepositoryInterface
{
    public function findByLembaga(int $lembagaId): ?object;

    public function upsert(int $lembagaId, array $data): object;
}
