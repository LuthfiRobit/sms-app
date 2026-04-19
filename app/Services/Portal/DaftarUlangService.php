<?php

namespace App\Services\Portal;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

use App\Models\Transaksi\Pendaftaran;

use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Transaksi\HasilSeleksiRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;
use App\Repositories\Ppdb\JadwalPendaftaranRepositoryInterface;

use App\Services\NotifikasiService;
use App\Services\LogActivityService;

/**
 * DaftarUlangService
 *
 * Layanan untuk mengelola alur daftar ulang peserta yang dinyatakan lulus seleksi PPDB.
 *
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  STATUS FLOW:                                                           │
 * │                                                                         │
 * │  lulus → (daftar_ulang jika konfirmasi) → siswa_tetap (admin konfirm)  │
 * │                                                                         │
 * │  PRINSIP KEAMANAN:                                                      │
 * │  1. Identifikasi user via $userId (auth()->user()->id_user)             │
 * │  2. Ownership check: Pendaftaran->peserta->user_id === $userId          │
 * │  3. Hanya status 'lulus' yang bisa melakukan daftar ulang               │
 * │  4. Deadline dari JadwalPendaftaran tipe = 'daftar_ulang'               │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Dependencies (di-inject via constructor):
 *  - PendaftaranRepositoryInterface
 *  - HasilSeleksiRepositoryInterface
 *  - JadwalPendaftaranRepositoryInterface
 *  - DokumenPesertaRepositoryInterface
 *  - NotifikasiService
 *  - LogActivityService
 */
class DaftarUlangService
{
    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================

    public function __construct(
        protected PendaftaranRepositoryInterface     $pendaftaranRepo,
        protected HasilSeleksiRepositoryInterface    $hasilSeleksiRepo,
        protected JadwalPendaftaranRepositoryInterface $jadwalRepo,
        protected DokumenPesertaRepositoryInterface  $dokumenRepo,
        protected NotifikasiService                  $notifikasiService,
        protected LogActivityService                 $logActivity,
    ) {
    }

    // =========================================================================
    // 1. GET INFO DAFTAR ULANG
    // =========================================================================

    /**
     * Mengambil informasi lengkap untuk halaman daftar ulang satu pendaftaran.
     *
     * Alur:
     *  1. Ownership check (throw AuthorizationException jika gagal)
     *  2. Validasi status 'lulus' dari hasil_seleksi.status_kelulusan
     *  3. Ambil jadwal daftar_ulang (tipe = 'daftar_ulang') dari jalur pendaftaran
     *  4. Hitung sisa hari & flag terlambat
     *
     * Return:
     * [
     *   'pendaftaran'       => Pendaftaran,
     *   'peserta'           => Peserta,
     *   'hasil_seleksi'     => HasilSeleksi|null,
     *   'jalur'             => JalurPendaftaran,
     *   'deadline'          => Carbon|null,
     *   'sisa_hari'         => int,
     *   'sisa_jam'          => int,
     *   'sisa_menit'        => int,
     *   'sudah_daftar_ulang' => bool,
     *   'terlambat'         => bool,
     *   'status_kelulusan'  => string|null,
     * ]
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @return array
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function getInfoDaftarUlang(int $pendaftaranId, int $userId): array
    {
        // Ownership check — throw jika bukan milik user ini
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        try {
            // Muat relasi yang diperlukan
            $pendaftaran->loadMissing([
                'peserta.user',
                'jalurPendaftaran',
                'tahunPelajaran',
                'hasilSeleksi',
            ]);

            $hasilSeleksi = $pendaftaran->hasilSeleksi;
            $jalur        = $pendaftaran->jalurPendaftaran;
            $peserta      = $pendaftaran->peserta;

            // Ambil jadwal daftar_ulang untuk jalur ini
            $jadwalDaftarUlang = null;
            $deadline          = null;

            if ($jalur) {
                $jadwalDaftarUlang = $this->jadwalRepo->findAktifByJalurAndTipe(
                    $jalur->id,
                    'daftar_ulang'
                );

                // Jika tidak ada jadwal aktif, coba ambil jadwal yang mana pun (expired juga)
                if (!$jadwalDaftarUlang) {
                    $jadwalDaftarUlang = $this->jadwalRepo
                        ->datatable()
                        ->where('jalur_pendaftaran_id', $jalur->id)
                        ->where('tipe', 'daftar_ulang')
                        ->first();
                }

                $deadline = $jadwalDaftarUlang?->selesai;
            }

            // Hitung sisa waktu
            $sisaHari   = 0;
            $sisaJam    = 0;
            $sisaMenit  = 0;
            $terlambat  = true;

            if ($deadline) {
                $now = now();
                if ($now->lessThan($deadline)) {
                    $terlambat = false;
                    $diff      = $now->diff($deadline);
                    $sisaHari  = (int) $diff->days;
                    $sisaJam   = (int) $diff->h;
                    $sisaMenit = (int) $diff->i;
                }
            }

            // Flag sudah daftar ulang
            $sudahDaftarUlang = in_array($pendaftaran->status, [
                Pendaftaran::STATUS_DAFTAR_ULANG,
                Pendaftaran::STATUS_SISWA_TETAP,
            ]);

            return [
                'success'            => true,
                'pendaftaran'        => $pendaftaran,
                'peserta'            => $peserta,
                'hasil_seleksi'      => $hasilSeleksi,
                'jalur'              => $jalur,
                'tahun_pelajaran'    => $pendaftaran->tahunPelajaran,
                'jadwal_daftar_ulang'=> $jadwalDaftarUlang,
                'deadline'           => $deadline,
                'sisa_hari'          => $sisaHari,
                'sisa_jam'           => $sisaJam,
                'sisa_menit'         => $sisaMenit,
                'sudah_daftar_ulang' => $sudahDaftarUlang,
                'terlambat'          => $terlambat,
                'status_kelulusan'   => $hasilSeleksi?->status_kelulusan,
                'status_pendaftaran' => $pendaftaran->status,
            ];
        } catch (AuthorizationException $e) {
            throw $e; // Re-throw karena perlu di-catch Controller
        } catch (Exception $e) {
            Log::error('[DaftarUlangService::getInfoDaftarUlang] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil informasi daftar ulang: ' . $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // 2. KONFIRMASI DAFTAR ULANG
    // =========================================================================

    /**
     * Memproses konfirmasi daftar ulang oleh peserta.
     *
     * Validasi:
     *  1. Ownership check
     *  2. Status kelulusan harus 'lulus'
     *  3. Status pendaftaran harus 'lulus' (belum daftar_ulang/siswa_tetap)
     *  4. Tidak melewati deadline
     *
     * Aksi:
     *  - Update status pendaftaran ke 'daftar_ulang'
     *  - Kirim notifikasi ke admin
     *  - Log activity
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @return array                ['success', 'message', 'data' => null]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function konfirmasiDaftarUlang(int $pendaftaranId, int $userId): array
    {
        // Ownership check
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        try {
            $pendaftaran->loadMissing([
                'peserta.user',
                'jalurPendaftaran',
                'hasilSeleksi',
            ]);

            $hasilSeleksi = $pendaftaran->hasilSeleksi;
            $peserta      = $pendaftaran->peserta;
            $jalur        = $pendaftaran->jalurPendaftaran;

            // Validasi: harus lulus seleksi
            if (!$hasilSeleksi || $hasilSeleksi->status_kelulusan !== 'lulus') {
                return [
                    'success' => false,
                    'message' => 'Hanya peserta yang dinyatakan lulus yang dapat melakukan daftar ulang.',
                ];
            }

            // Validasi: status pendaftaran harus 'lulus' (belum daftar_ulang atau siswa_tetap)
            if ($pendaftaran->status !== Pendaftaran::STATUS_LULUS) {
                if ($pendaftaran->status === Pendaftaran::STATUS_DAFTAR_ULANG) {
                    return [
                        'success' => false,
                        'message' => 'Anda sudah melakukan daftar ulang. Menunggu konfirmasi dari admin.',
                    ];
                }
                if ($pendaftaran->status === Pendaftaran::STATUS_SISWA_TETAP) {
                    return [
                        'success' => false,
                        'message' => 'Status Anda sudah ditetapkan sebagai Siswa Tetap.',
                    ];
                }
                return [
                    'success' => false,
                    'message' => "Status pendaftaran tidak valid untuk daftar ulang. Status saat ini: {$pendaftaran->status}.",
                ];
            }

            // Validasi: cek deadline daftar_ulang
            $jadwalDaftarUlang = null;
            if ($jalur) {
                $jadwalDaftarUlang = $this->jadwalRepo
                    ->datatable()
                    ->where('jalur_pendaftaran_id', $jalur->id)
                    ->where('tipe', 'daftar_ulang')
                    ->first();
            }

            if ($jadwalDaftarUlang && now()->greaterThan($jadwalDaftarUlang->selesai)) {
                return [
                    'success' => false,
                    'message' => 'Batas waktu daftar ulang telah lewat. Hubungi admin sekolah untuk penanganan lebih lanjut.',
                ];
            }

            // Update status ke 'daftar_ulang'
            $this->pendaftaranRepo->updateStatus($pendaftaranId, Pendaftaran::STATUS_DAFTAR_ULANG);

            // Ambil data untuk notifikasi
            $namaPeserta    = $peserta?->user?->name ?? 'Peserta';
            $noPendaftaran  = $pendaftaran->no_pendaftaran ?? ('#' . $pendaftaran->id);
            $namaJalur      = $jalur?->nama ?? 'Reguler';

            // Kirim notifikasi ke admin
            $this->notifikasiService->kirimKeAdmin('admin_pendaftaran_baru', [
                'nama_peserta'   => $namaPeserta,
                'no_pendaftaran' => $noPendaftaran,
                'jalur'          => $namaJalur,
                'tanggal'        => now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm'),
            ], ['inapp']);

            // Kirim notifikasi ke peserta
            $pesertaUserId = $peserta?->user_id;
            if ($pesertaUserId) {
                $this->notifikasiService->kirim($pesertaUserId, 'daftar_ulang_reminder', [
                    'nama_peserta'   => $namaPeserta,
                    'no_pendaftaran' => $noPendaftaran,
                    'jalur'          => $namaJalur,
                ], ['inapp']);
            }

            // Log activity
            $this->logActivity->log(
                'Daftar Ulang',
                "Peserta {$namaPeserta} melakukan daftar ulang untuk pendaftaran #{$noPendaftaran}."
            );

            return [
                'success' => true,
                'message' => 'Daftar ulang berhasil dikonfirmasi! Menunggu verifikasi dari admin.',
                'data'    => null,
            ];
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('[DaftarUlangService::konfirmasiDaftarUlang] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses daftar ulang: ' . $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // 3. GET STATUS DAFTAR ULANG
    // =========================================================================

    /**
     * Mengambil status ringkas daftar ulang untuk satu pendaftaran.
     *
     * Return:
     * [
     *   'success'           => true,
     *   'sudah_konfirmasi'  => bool,
     *   'status_pendaftaran'=> string,
     *   'info_selanjutnya'  => string,
     * ]
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @return array
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function getStatusDaftarUlang(int $pendaftaranId, int $userId): array
    {
        // Ownership check
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        try {
            $status           = $pendaftaran->status;
            $sudahKonfirmasi  = in_array($status, [
                Pendaftaran::STATUS_DAFTAR_ULANG,
                Pendaftaran::STATUS_SISWA_TETAP,
            ]);

            $infoSelanjutnya = match ($status) {
                Pendaftaran::STATUS_LULUS        => 'Segera lakukan konfirmasi daftar ulang sebelum batas waktu.',
                Pendaftaran::STATUS_DAFTAR_ULANG => 'Menunggu konfirmasi dari admin. Notifikasi akan dikirim via email dan portal.',
                Pendaftaran::STATUS_SISWA_TETAP  => 'Selamat! Anda telah resmi menjadi siswa baru.',
                default                           => 'Status tidak dikenali. Hubungi admin untuk informasi lebih lanjut.',
            };

            return [
                'success'            => true,
                'sudah_konfirmasi'   => $sudahKonfirmasi,
                'status_pendaftaran' => $status,
                'info_selanjutnya'   => $infoSelanjutnya,
            ];
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('[DaftarUlangService::getStatusDaftarUlang] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil status daftar ulang.',
            ];
        }
    }

    // =========================================================================
    // PRIVATE — OWNERSHIP CHECK
    // =========================================================================

    /**
     * Memvalidasi bahwa pendaftaran dengan $pendaftaranId benar-benar milik $userId.
     *
     * Mekanisme:
     *  - Load Pendaftaran dengan relasi 'peserta' (1 query eager load)
     *  - Bandingkan: Pendaftaran->peserta->user_id === $userId
     *
     * @param  int         $pendaftaranId  ID pendaftaran yang akan dicek
     * @param  int         $userId         ID user yang sedang login (id_user)
     * @return Pendaftaran                 Instance Pendaftaran jika ownership valid
     *
     * @throws AuthorizationException Jika pendaftaran tidak ditemukan atau bukan milik $userId
     */
    private function authorizeOwnership(int $pendaftaranId, int $userId): Pendaftaran
    {
        $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId, ['peserta']);

        if (!$pendaftaran) {
            throw new AuthorizationException(
                "Pendaftaran dengan ID {$pendaftaranId} tidak ditemukan."
            );
        }

        // Strict comparison untuk mencegah type juggling
        if ((int) ($pendaftaran->peserta?->user_id) !== $userId) {
            Log::warning('[DaftarUlangService] Unauthorized access attempt', [
                'pendaftaran_id'  => $pendaftaranId,
                'requesting_user' => $userId,
                'owner_user_id'   => $pendaftaran->peserta?->user_id,
            ]);

            throw new AuthorizationException(
                'Anda tidak memiliki akses ke pendaftaran ini.'
            );
        }

        return $pendaftaran;
    }
}
