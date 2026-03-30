<?php

namespace App\Services\Ppdb;

use App\Models\Ppdb\SyaratPendaftaran;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyaratPendaftaranService
{
    public function __construct(
        protected LogActivityService $logActivity,
        protected ResponseService $response
    ) {}

    public function index(int $jalurId, array $filters = []): array
    {
        try {
            $query = SyaratPendaftaran::where('jalur_pendaftaran_id', $jalurId)->orderBy('urutan', 'asc');

            foreach ($filters as $key => $val) {
                if ($val !== null && $val !== '') {
                    $query->where($key, $val);
                }
            }

            return [
                'success' => true,
                'message' => 'Data syarat pendaftaran berhasil diambil.',
                'data'    => $query,
            ];
        } catch (Exception $e) {
            Log::error('[SyaratPendaftaranService::index] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data syarat pendaftaran.',
                'data'    => null,
            ];
        }
    }

    public function store(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $jalur = JalurPendaftaran::find($data['jalur_pendaftaran_id']);
            if($jalur) {
                $data['tahun_pelajaran_id'] = $jalur->pembukaanPpdb->tahun_pelajaran_id;
            }

            if (!isset($data['urutan'])) {
                $maxUrutan = SyaratPendaftaran::where('jalur_pendaftaran_id', $data['jalur_pendaftaran_id'])->max('urutan');
                $data['urutan'] = $maxUrutan ? $maxUrutan + 1 : 1;
            }

            $syarat = SyaratPendaftaran::create($data);

            $this->logActivity->log(
                'Tambah Syarat Pendaftaran',
                "Menambahkan Syarat Pendaftaran: {$syarat->nama} pada Jalur ID: {$syarat->jalur_pendaftaran_id}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Syarat pendaftaran berhasil dibuat.',
                'data'    => $syarat,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SyaratPendaftaranService::store] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat syarat pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function show(int $id): array
    {
        try {
            $syarat = SyaratPendaftaran::find($id);

            if (! $syarat) {
                return [
                    'success' => false,
                    'message' => "Syarat pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail syarat pendaftaran berhasil diambil.',
                'data'    => $syarat,
            ];
        } catch (Exception $e) {
            Log::error('[SyaratPendaftaranService::show] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil detail syarat pendaftaran.',
                'data'    => null,
            ];
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $syarat = SyaratPendaftaran::find($id);

            if (! $syarat) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Syarat pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Jika ubah is_wajib dari checkbox (gk nge-pass di request kalo false)
            if(!isset($data['wajib']) && isset($data['nama'])) {
                // If it's a full update and checkbox is unchecked, we need to set to false. Controller level check is better, but doing it here as fallback if needed.
            }

            $syarat->update($data);

            $this->logActivity->log(
                'Update Syarat Pendaftaran',
                "Memperbarui Syarat Pendaftaran: {$syarat->nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Syarat pendaftaran berhasil diperbarui.',
                'data'    => $syarat,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SyaratPendaftaranService::update] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memperbarui syarat pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $syarat = SyaratPendaftaran::find($id);

            if (! $syarat) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Syarat pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $nama = $syarat->nama;
            $syarat->delete();

            $this->logActivity->log(
                'Hapus Syarat Pendaftaran',
                "Menghapus Syarat Pendaftaran: {$nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Syarat pendaftaran \"{$nama}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SyaratPendaftaranService::destroy] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghapus syarat pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function reorder(array $urutanData, int $userId): array
    {
        DB::beginTransaction();
        try {
            foreach ($urutanData as $data) {
                SyaratPendaftaran::where('id', $data['id'])->update(['urutan' => $data['urutan']]);
            }

            $this->logActivity->log(
                'Reorder Syarat Pendaftaran',
                "Mengubah urutan syarat pendaftaran"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Urutan syarat pendaftaran berhasil diperbarui.',
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[SyaratPendaftaranService::reorder] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengubah urutan: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
}
