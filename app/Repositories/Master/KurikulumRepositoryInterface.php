<?php

namespace App\Repositories\Master;

interface KurikulumRepositoryInterface
{
    public function getAll();
    public function getById($id);
    public function getDatatablesData();
}
