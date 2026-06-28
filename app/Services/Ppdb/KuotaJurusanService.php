<?php

namespace App\Services\Ppdb;

use App\Models\Master\Jurusan;
use App\Models\Ppdb\JalurPendaftaran;
use App\Models\Ppdb\KuotaJurusan;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KuotaJurusanService
{
    public function __construct(
        protected LogActivityService $logActivity,
        protected ResponseService    $response
    ) {}

    // =========================================================================
    // INDEX — Ambil semua kuota jurusan untuk kombinasi [jalurId, tahunPelajaranId]
    // =========================================================================

    /**
     * Mengembalikan kuota semua jurusan aktif beserta data sisa kuota.
     * Jurusan yang belum memiliki record kuota akan ditampilkan dengan kuota = 0.
     *
     * @param  int $jalurId
     * @param  int $tahunPelajaranId
     * @return array
     */
    public function index(int $jalurId, int $tahunPelajaranId): array
    {
        try {
            // Derive lembaga dari jalur agar jurusan yang ditampilkan hanya milik lembaga ini
            $jalur     = JalurPendaftaran::with('pembukaanPpdb')->find($jalurId);
            $lembagaId = $jalur?->pembukaanPpdb?->lembaga_id;

            // Ambil semua jurusan aktif milik lembaga ini saja
            $allJurusan = Jurusan::aktif()->byLembaga($lembagaId)->orderBy('nama')->get();

            // Ambil kuota existing untuk kombinasi ini
            $existingKuota = KuotaJurusan::with('jurusan')
                ->where('jalur_pendaftaran_id', $jalurId)
                ->where('tahun_pelajaran_id', $tahunPelajaranId)
                ->get()
                ->keyBy('jurusan_id');

            // Gabungkan: setiap jurusan aktif + data kuotanya (jika ada)
            $result = $allJurusan->map(function (Jurusan $jurusan) use ($existingKuota, $jalurId, $tahunPelajaranId) {
                $kuotaRecord = $existingKuota->get($jurusan->id);
                return [
                    'kuota_id'            => $kuotaRecord?->id,
                    'jurusan_id'          => $jurusan->id,
                    'jurusan_nama'        => $jurusan->nama,
                    'jurusan_kode'        => $jurusan->kode,
                    'jalur_pendaftaran_id' => $jalurId,
                    'tahun_pelajaran_id'  => $tahunPelajaranId,
                    'kuota'              => $kuotaRecord?->kuota ?? 0,
                    'terisi'             => $kuotaRecord?->terisi ?? 0,
                    'sisa'               => $kuotaRecord ? max(0, $kuotaRecord->kuota - $kuotaRecord->terisi) : 0,
                    'has_record'         => $kuotaRecord !== null,
                ];
            });

            return [
                'success' => true,
                'message' => 'Data kuota jurusan berhasil diambil.',
                'data'    => $result,
            ];
        } catch (Exception $e) {
            Log::error('[KuotaJurusanService::index] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data kuota jurusan.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // UPSERT — Insert atau update semua kuota sekaligus
    // =========================================================================

    /**
     * Upsert kuota jurusan untuk satu jalur + tahun pelajaran.
     * Format $kuotaData: [['jurusan_id' => int, 'kuota' => int], ...]
     *
     * Aturan bisnis:
     * - Kuota baru HARUS >= terisi existing (tidak boleh dikurangi di bawah yang sudah terisi)
     * - Kolom 'terisi' TIDAK boleh diubah dari sini
     *
     * @param  int   $jalurId
     * @param  int   $tahunPelajaranId
     * @param  array $kuotaData
     * @param  int   $userId
     * @return array
     */
    public function upsert(int $jalurId, int $tahunPelajaranId, array $kuotaData, int $userId): array
    {
        DB::beginTransaction();
        try {
            // Validasi: jalur harus ada
            $jalur = JalurPendaftaran::with('pembukaanPpdb')->find($jalurId);
            if (!$jalur) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Jalur pendaftaran tidak ditemukan.',
                    'data'    => null,
                ];
            }

            $lembagaId  = $jalur->pembukaanPpdb?->lembaga_id;
            $errors     = [];
            $processed  = 0;

            foreach ($kuotaData as $item) {
                $jurusanId  = (int) $item['jurusan_id'];
                $kuotaBaru  = (int) $item['kuota'];

                // Jurusan harus valid/aktif dan milik lembaga yang sama
                $jurusan = Jurusan::aktif()->byLembaga($lembagaId)->find($jurusanId);
                if (!$jurusan) {
                    $errors[] = "Jurusan ID {$jurusanId} tidak ditemukan atau tidak aktif.";
                    continue;
                }

                // Cek record existing
                $existing = KuotaJurusan::where('jalur_pendaftaran_id', $jalurId)
                    ->where('tahun_pelajaran_id', $tahunPelajaranId)
                    ->where('jurusan_id', $jurusanId)
                    ->first();

                // Aturan bisnis: kuota baru tidak boleh < terisi
                if ($existing && $kuotaBaru < $existing->terisi) {
                    $errors[] = "Kuota jurusan \"{$jurusan->nama}\" tidak bisa dikurangi dari {$existing->kuota} menjadi {$kuotaBaru} karena sudah ada {$existing->terisi} pendaftar terisi.";
                    continue;
                }

                if ($existing) {
                    // UPDATE — jangan sentuh kolom 'terisi'
                    $existing->update(['kuota' => $kuotaBaru]);
                } else {
                    // INSERT baru
                    KuotaJurusan::create([
                        'jalur_pendaftaran_id' => $jalurId,
                        'tahun_pelajaran_id'   => $tahunPelajaranId,
                        'jurusan_id'           => $jurusanId,
                        'kuota'                => $kuotaBaru,
                        'terisi'               => 0,
                    ]);
                }

                $processed++;
            }

            // Jika ada error, rollback semua
            if (!empty($errors)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => implode(' | ', $errors),
                    'data'    => null,
                ];
            }

            $this->logActivity->log(
                'Upsert Kuota Jurusan',
                "Menyimpan kuota jurusan untuk Jalur: \"{$jalur->nama}\" — {$processed} jurusan diproses."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => "{$processed} kuota jurusan berhasil disimpan.",
                'data'    => $processed,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[KuotaJurusanService::upsert] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan kuota jurusan: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // GET KUOTA STATUS — Status per jurusan: penuh / tersedia + persentase
    // =========================================================================

    /**
     * Mengembalikan status detail per jurusan untuk kombinasi jalur + tahun pelajaran.
     *
     * @param  int $jalurId
     * @param  int $tahunPelajaranId
     * @return array
     */
    public function getKuotaStatus(int $jalurId, int $tahunPelajaranId): array
    {
        try {
            $kuotaList = KuotaJurusan::with('jurusan')
                ->where('jalur_pendaftaran_id', $jalurId)
                ->where('tahun_pelajaran_id', $tahunPelajaranId)
                ->get();

            $result = $kuotaList->map(function (KuotaJurusan $kq) {
                $persen  = ($kq->kuota > 0) ? round(($kq->terisi / $kq->kuota) * 100, 1) : 0;
                $status  = ($kq->terisi >= $kq->kuota && $kq->kuota > 0) ? 'penuh' : 'tersedia';
                return [
                    'jurusan_id'   => $kq->jurusan_id,
                    'jurusan_nama' => $kq->jurusan?->nama,
                    'kuota'        => $kq->kuota,
                    'terisi'       => $kq->terisi,
                    'sisa'         => max(0, $kq->kuota - $kq->terisi),
                    'persentase'   => $persen,
                    'status'       => $status,
                ];
            });

            return [
                'success' => true,
                'message' => 'Status kuota jurusan berhasil diambil.',
                'data'    => $result,
            ];
        } catch (Exception $e) {
            Log::error('[KuotaJurusanService::getKuotaStatus] ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil status kuota.',
                'data'    => null,
            ];
        }
    }
}
