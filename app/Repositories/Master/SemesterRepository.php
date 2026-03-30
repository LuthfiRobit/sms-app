<?php

namespace App\Repositories\Master;

use App\Models\Master\Semester;
use Illuminate\Database\Eloquent\Collection;

class SemesterRepository implements SemesterRepositoryInterface
{
    public function getAll(): Collection
    {
        return Semester::all();
    }

    public function getById(int $id): ?Semester
    {
        return Semester::with('tahunPelajaran')->findOrFail($id);
    }

    public function getDatatablesData()
    {
        return Semester::with('tahunPelajaran');
    }

    public function create(array $data): Semester
    {
        return Semester::create($data);
    }

    public function update(int $id, array $data): Semester
    {
        $semester = $this->getById($id);
        $semester->update($data);
        return $semester;
    }

    public function delete(int $id): bool
    {
        $semester = $this->getById($id);
        return $semester->delete();
    }
}
