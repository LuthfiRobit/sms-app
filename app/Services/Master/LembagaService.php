<?php

namespace App\Services\Master;

use App\Repositories\Master\LembagaRepositoryInterface;
use App\Services\LogActivityService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LembagaService
{
    public function __construct(
        protected LembagaRepositoryInterface $lembagaRepository,
        protected LogActivityService $logActivityService
    ) {
    }

    public function index(array $filters = [])
    {
        return $this->lembagaRepository->datatable($filters);
    }

    public function all(array $filters = [], array $with = [])
    {
        return $this->lembagaRepository->all($filters, $with);
    }

    public function findById(int $id, array $with = [])
    {
        return $this->lembagaRepository->findById($id, $with);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                $data['logo'] = $data['logo']->store('lembaga/logo', 'public');
            }

            $lembaga = $this->lembagaRepository->create($data);
            $this->logActivityService->log('Create Lembaga', "Menambah Lembaga: {$lembaga->nama}");

            DB::commit();
            return $lembaga;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $lembaga = $this->lembagaRepository->findById($id);

            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                if ($lembaga->logo) {
                    Storage::disk('public')->delete($lembaga->logo);
                }
                $data['logo'] = $data['logo']->store('lembaga/logo', 'public');
            }

            $lembaga = $this->lembagaRepository->update($id, $data);
            $this->logActivityService->log('Update Lembaga', "Memperbarui Lembaga: {$lembaga->nama}");

            DB::commit();
            return $lembaga;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $id)
    {
        DB::beginTransaction();
        try {
            $lembaga = $this->lembagaRepository->findById($id, ['pembukaanPpdb']);

            if ($lembaga->pembukaanPpdb()->whereIn('status', ['buka', 'draft'])->exists()) {
                throw new Exception('Lembaga masih memiliki PPDB aktif dan tidak dapat dihapus.');
            }

            $nama = $lembaga->nama;
            $this->lembagaRepository->delete($id);
            $this->logActivityService->log('Delete Lembaga', "Menghapus Lembaga: {$nama}");

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function setAdmins(int $lembagaId, array $userIds)
    {
        DB::beginTransaction();
        try {
            $this->lembagaRepository->syncAdmins($lembagaId, $userIds);
            $lembaga = $this->lembagaRepository->findById($lembagaId);
            $this->logActivityService->log('Set Admin Lembaga', "Mengatur admin Lembaga: {$lembaga->nama}");

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
            $lembaga = $this->lembagaRepository->findById($id);
            $newStatus = $lembaga->status === 'aktif' ? 'nonaktif' : 'aktif';
            $lembaga = $this->lembagaRepository->update($id, ['status' => $newStatus]);
            $this->logActivityService->log('Toggle Status Lembaga', "Mengubah status {$lembaga->nama} menjadi {$newStatus}");

            DB::commit();
            return $lembaga;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
