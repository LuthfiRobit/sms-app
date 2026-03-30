<?php

namespace App\Services\Master;

use App\Repositories\Master\TahunPelajaranRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;

class TahunPelajaranService
{
    protected $tahunPelajaranRepository;
    protected $logActivityService;

    public function __construct(
        TahunPelajaranRepositoryInterface $tahunPelajaranRepository,
        LogActivityService $logActivityService
    ) {
        $this->tahunPelajaranRepository = $tahunPelajaranRepository;
        $this->logActivityService = $logActivityService;
    }

    public function createTahunPelajaran(array $data)
    {
        DB::beginTransaction();
        try {
            // Jika statusnya aktif, nonaktifkan tahun pelajaran lain
            if (isset($data['status']) && $data['status'] === 'aktif') {
                $this->deactivateAll();
            }

            $tahun = $this->tahunPelajaranRepository->create($data);
            $this->logActivityService->log('Create Tahun Pelajaran', "Menambah Tahun Pelajaran: {$tahun->nama}");
            
            DB::commit();
            return $tahun;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTahunPelajaran(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            // Jika statusnya diubah menjadi aktif, nonaktifkan tahun pelajaran lain
            if (isset($data['status']) && $data['status'] === 'aktif') {
                $this->deactivateAll($id);
            }

            $tahun = $this->tahunPelajaranRepository->update($id, $data);
            $this->logActivityService->log('Update Tahun Pelajaran', "Memperbarui Tahun Pelajaran: {$tahun->nama}");
            
            DB::commit();
            return $tahun;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTahunPelajaran(int $id)
    {
        DB::beginTransaction();
        try {
            $tahun = $this->tahunPelajaranRepository->getById($id);
            $nama = $tahun->nama;
            $this->tahunPelajaranRepository->delete($id);
            $this->logActivityService->log('Delete Tahun Pelajaran', "Menghapus Tahun Pelajaran: {$nama}");
            
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
            $tahun = $this->tahunPelajaranRepository->getById($id);
            $newStatus = $tahun->status === 'aktif' ? 'nonaktif' : 'aktif';
            
            if ($newStatus === 'aktif') {
                $this->deactivateAll($id);
            }

            $this->tahunPelajaranRepository->update($id, ['status' => $newStatus]);
            $this->logActivityService->log('Toggle Tahun Pelajaran Status', "Mengubah status Tahun Pelajaran {$tahun->nama} menjadi {$newStatus}");
            
            DB::commit();
            return $tahun;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function deactivateAll($exceptId = null)
    {
        $query = \App\Models\Master\TahunPelajaran::where('status', 'aktif');
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        $query->update(['status' => 'nonaktif']);
    }
}
