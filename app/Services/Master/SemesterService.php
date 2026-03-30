<?php

namespace App\Services\Master;

use App\Repositories\Master\SemesterRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;

class SemesterService
{
    protected $semesterRepository;
    protected $logActivityService;

    public function __construct(
        SemesterRepositoryInterface $semesterRepository,
        LogActivityService $logActivityService
    ) {
        $this->semesterRepository = $semesterRepository;
        $this->logActivityService = $logActivityService;
    }

    public function createSemester(array $data)
    {
        DB::beginTransaction();
        try {
            // Jika statusnya aktif, nonaktifkan semester lain di tahun pelajaran yang sama
            if (isset($data['status']) && $data['status'] === 'aktif') {
                $this->deactivateAll($data['tahun_pelajaran_id']);
            }

            $semester = $this->semesterRepository->create($data);
            $this->logActivityService->log('Create Semester', "Menambah Semester: {$semester->nama} untuk TP ID {$semester->tahun_pelajaran_id}");
            
            DB::commit();
            return $semester;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSemester(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $semester = $this->semesterRepository->getById($id);
            
            // Jika statusnya diubah menjadi aktif, nonaktifkan semester lain di tahun pelajaran yang sama
            if (isset($data['status']) && $data['status'] === 'aktif') {
                $this->deactivateAll($semester->tahun_pelajaran_id, $id);
            }

            $semester = $this->semesterRepository->update($id, $data);
            $this->logActivityService->log('Update Semester', "Memperbarui Semester: {$semester->nama}");
            
            DB::commit();
            return $semester;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteSemester(int $id)
    {
        DB::beginTransaction();
        try {
            $semester = $this->semesterRepository->getById($id);
            $nama = $semester->nama;
            $this->semesterRepository->delete($id);
            $this->logActivityService->log('Delete Semester', "Menghapus Semester: {$nama}");
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleStatus(int $id)
    {
        DB::beginTransaction();
        try {
            $semester = $this->semesterRepository->getById($id);
            $newStatus = $semester->status === 'aktif' ? 'nonaktif' : 'aktif';
            
            if ($newStatus === 'aktif') {
                $this->deactivateAll($semester->tahun_pelajaran_id, $id);
            }

            $this->semesterRepository->update($id, ['status' => $newStatus]);
            $this->logActivityService->log('Toggle Semester Status', "Mengubah status Semester {$semester->nama} menjadi {$newStatus}");
            
            DB::commit();
            return $semester;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function deactivateAll($tahunPelajaranId, $exceptId = null)
    {
        $query = \App\Models\Master\Semester::where('tahun_pelajaran_id', $tahunPelajaranId)
            ->where('status', 'aktif');
            
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        $query->update(['status' => 'nonaktif']);
    }
}
