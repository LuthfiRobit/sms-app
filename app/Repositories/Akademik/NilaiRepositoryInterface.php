<?php

namespace App\Repositories\Akademik;

use Illuminate\Support\Collection;

interface NilaiRepositoryInterface
{
    public function getByRombelMapelSemester(int $rombelId, int $mapelId, int $semesterId): Collection;
    public function upsert(array $rows): void;
    public function datatable(array $filters = []): mixed;
}
