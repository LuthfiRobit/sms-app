<?php

namespace App\Services\Master;

use App\Models\Master\Kurikulum;
use App\Repositories\Master\KurikulumRepositoryInterface;

class KurikulumService
{
    protected $kurikulumRepository;

    public function __construct(KurikulumRepositoryInterface $kurikulumRepository)
    {
        $this->kurikulumRepository = $kurikulumRepository;
    }

    public function createKurikulum(array $data)
    {
        return Kurikulum::create($data);
    }

    public function updateKurikulum(array $data, $id)
    {
        $kurikulum = $this->kurikulumRepository->getById($id);
        $kurikulum->update($data);
        return $kurikulum;
    }

    public function deleteKurikulum($id)
    {
        $kurikulum = $this->kurikulumRepository->getById($id);
        return $kurikulum->delete();
    }

    public function toggleStatus($id)
    {
        $kurikulum = $this->kurikulumRepository->getById($id);
        $kurikulum->status = $kurikulum->status === 'aktif' ? 'nonaktif' : 'aktif';
        return $kurikulum->save();
    }
}
