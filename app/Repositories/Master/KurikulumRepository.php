<?php

namespace App\Repositories\Master;

use App\Models\Master\Kurikulum;

class KurikulumRepository implements KurikulumRepositoryInterface
{
    protected $model;

    public function __construct(Kurikulum $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getDatatablesData()
    {
        return $this->model->query();
    }
}
