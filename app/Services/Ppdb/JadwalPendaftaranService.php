<?php

namespace App\Services\Ppdb;

use App\Models\Ppdb\JadwalPendaftaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalPendaftaranService
{
    public function __construct(
        protected LogActivityService $logActivity,
        protected ResponseService $response
    ) {}

    public function index(int $jalurId, array $filters = []): array
    {
        try {
            $query = JadwalPendaftaran::where('jalur_pendaftaran_id', $jalurId);

            foreach ($filters as $key => $val) {
                if ($val !== null && $val !== '') {
                    $query->where($key, $val);
                }
            }

            return [
                'success' => true,
                'message' => 'Data jadwal pendaftaran berhasil diambil.',
                'data'    => $query,
            ];
        } catch (Exception $e) {
            Log::error('[JadwalPendaftaranService::index] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data jadwal pendaftaran.',
                'data'    => null,
            ];
        }
    }

    public function store(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            // Validasi overlap jadwal dengan tipe yang sama di jalur yang sama
            $overlap = JadwalPendaftaran::where('jalur_pendaftaran_id', $data['jalur_pendaftaran_id'])
                ->where('tipe', $data['tipe'])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('mulai', [$data['mulai'], $data['selesai']])
                          ->orWhereBetween('selesai', [$data['mulai'], $data['selesai']])
                          ->orWhere(function ($q) use ($data) {
                              $q->where('mulai', '<=', $data['mulai'])
                                ->where('selesai', '>=', $data['selesai']);
                          });
                })->exists();

            if ($overlap) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Terdapat jadwal pendaftaran lain dengan tipe '{$data['tipe']}' pada rentang waktu yang beririsan.",
                    'data'    => null,
                ];
            }

            $jadwal = JadwalPendaftaran::create($data);

            $this->logActivity->log(
                'Tambah Jadwal Pendaftaran',
                "Menambahkan Jadwal Pendaftaran: {$jadwal->nama} pada Jalur ID: {$jadwal->jalur_pendaftaran_id}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jadwal pendaftaran berhasil dibuat.',
                'data'    => $jadwal,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JadwalPendaftaranService::store] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat jadwal pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function show(int $id): array
    {
        try {
            $jadwal = JadwalPendaftaran::find($id);

            if (! $jadwal) {
                return [
                    'success' => false,
                    'message' => "Jadwal pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Detail jadwal pendaftaran berhasil diambil.',
                'data'    => $jadwal,
            ];
        } catch (Exception $e) {
            Log::error('[JadwalPendaftaranService::show] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil detail jadwal pendaftaran.',
                'data'    => null,
            ];
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $jadwal = JadwalPendaftaran::find($id);

            if (! $jadwal) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jadwal pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Validasi overlap (kecualikan diri sendiri)
            $overlap = JadwalPendaftaran::where('jalur_pendaftaran_id', $jadwal->jalur_pendaftaran_id)
                ->where('tipe', $data['tipe'] ?? $jadwal->tipe)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($data, $jadwal) {
                    $mulai = $data['mulai'] ?? $jadwal->mulai;
                    $selesai = $data['selesai'] ?? $jadwal->selesai;

                    $query->whereBetween('mulai', [$mulai, $selesai])
                          ->orWhereBetween('selesai', [$mulai, $selesai])
                          ->orWhere(function ($q) use ($mulai, $selesai) {
                              $q->where('mulai', '<=', $mulai)
                                ->where('selesai', '>=', $selesai);
                          });
                })->exists();

            if ($overlap) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Terdapat jadwal pendaftaran lain dengan tipe yang sama pada rentang waktu yang beririsan.",
                    'data'    => null,
                ];
            }

            $jadwal->update($data);

            $this->logActivity->log(
                'Update Jadwal Pendaftaran',
                "Memperbarui Jadwal Pendaftaran: {$jadwal->nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jadwal pendaftaran berhasil diperbarui.',
                'data'    => $jadwal,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JadwalPendaftaranService::update] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memperbarui jadwal pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    public function destroy(int $id, int $userId): array
    {
        DB::beginTransaction();
        try {
            $jadwal = JadwalPendaftaran::find($id);

            if (! $jadwal) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => "Jadwal pendaftaran dengan ID {$id} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            $nama = $jadwal->nama;
            $jadwal->delete();

            $this->logActivity->log(
                'Hapus Jadwal Pendaftaran',
                "Menghapus Jadwal Pendaftaran: {$nama}"
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "Jadwal pendaftaran \"{$nama}\" berhasil dihapus.",
                'data'    => null,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[JadwalPendaftaranService::destroy] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghapus jadwal pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }
}
