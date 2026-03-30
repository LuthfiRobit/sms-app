<?php

namespace App\Services\Ppdb;

use App\Models\Ppdb\BiayaRegistrasi;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiayaRegistrasiService
{
    public function __construct(
        protected LogActivityService $logActivity,
        protected ResponseService $response
    ) {}

    public function index(int $jalurId, array $filters = []): array
    {
        try {
            $query = BiayaRegistrasi::where('jalur_pendaftaran_id', $jalurId);

            foreach ($filters as $key => $val) {
                if ($val !== null && $val !== '') {
                    $query->where($key, $val);
                }
            }

            return [
                'success' => true,
                'message' => 'Data biaya registrasi berhasil diambil.',
                'data'    => $query,
            ];
        } catch (Exception $e) {
            Log::error('[BiayaRegistrasiService::index] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data biaya registrasi.',
                'data'    => null,
            ];
        }
    }

    public function store(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            if ($data['nominal'] <= 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Nominal biaya harus lebih besar dari 0.",
                    'data'    => null,
                ];
            }

            $jalur = JalurPendaftaran::find($data['jalur_pendaftaran_id']);
            if($jalur) {
                $data['tahun_pelajaran_id'] = $jalur->pembukaanPpdb->tahun_pelajaran_id;
            }

            $data['is_aktif'] = $data['is_aktif'] ?? 1;

            $biaya = BiayaRegistrasi::create($data);

            $this->logActivity->log(
                'Tambah Biaya Registrasi',
                "Menambahkan Biaya Registrasi: {$biaya->nama} pada Jalur ID: {$biaya->jalur_pendaftaran_id}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Biaya registrasi berhasil dibuat.',
                'data'    => $biaya,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[BiayaRegistrasiService::store] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat biaya registrasi: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function show(int $id): array
    {
        try {
            $biaya = BiayaRegistrasi::find($id);

            if (! $biaya) {
                return [
                    'success' => false,
                    'message' => "Biaya registrasi dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail biaya registrasi berhasil diambil.',
                'data'    => $biaya,
            ];
        } catch (Exception $e) {
            Log::error('[BiayaRegistrasiService::show] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil detail biaya registrasi.',
                'data'    => null,
            ];
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            if (isset($data['nominal']) && $data['nominal'] <= 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Nominal biaya harus lebih besar dari 0.",
                    'data'    => null,
                ];
            }

            $biaya = BiayaRegistrasi::find($id);

            if (! $biaya) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Biaya registrasi dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $biaya->update($data);

            $this->logActivity->log(
                'Update Biaya Registrasi',
                "Memperbarui Biaya Registrasi: {$biaya->nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Biaya registrasi berhasil diperbarui.',
                'data'    => $biaya,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[BiayaRegistrasiService::update] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memperbarui biaya registrasi: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $biaya = BiayaRegistrasi::find($id);

            if (! $biaya) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Biaya registrasi dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $nama = $biaya->nama;
            $biaya->delete();

            $this->logActivity->log(
                'Hapus Biaya Registrasi',
                "Menghapus Biaya Registrasi: {$nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Biaya registrasi \"{$nama}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[BiayaRegistrasiService::destroy] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghapus biaya registrasi: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
}
