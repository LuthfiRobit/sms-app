<?php

namespace App\Repositories\Akademik;

use App\Models\Akademik\AkademikSetting;

class AkademikSettingRepository implements AkademikSettingRepositoryInterface
{
    public function __construct(protected AkademikSetting $model) {}

    public function findByLembaga(int $lembagaId): ?object
    {
        return $this->model->where('lembaga_id', $lembagaId)->first();
    }

    public function upsert(int $lembagaId, array $data): object
    {
        return $this->model->updateOrCreate(
            ['lembaga_id' => $lembagaId],
            $data
        );
    }
}
