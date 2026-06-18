<?php

namespace App\Services\Portal;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\Ppdb\PembukaanPpdb;
use App\Models\Transaksi\Pendaftaran;

use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Ppdb\JalurPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\FormulirPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\SyaratPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\KuotaJurusanRepositoryInterface;
use App\Repositories\Ppdb\BiayaRegistrasiRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;

use App\Services\Peserta\PesertaProfileService;
use App\Services\Transaksi\PendaftaranService;

/**
 * PortalPendaftaranService
 *
 * Wrapper dengan ownership check ketat di atas PendaftaranService,
 * dirancang khusus untuk konteks portal peserta (bukan admin).
 *
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  PRINSIP KEAMANAN:                                                      │
 * │                                                                         │
 * │  1. Semua operasi diidentifikasi via $userId (auth()->user()->id_user)  │
 * │  2. Ownership check WAJIB sebelum setiap akses ke data pendaftaran:     │
 * │       Pendaftaran->peserta->user_id === $userId                          │
 * │  3. Jika ownership gagal → throw AuthorizationException (bukan return   │
 * │     error) agar Controller dapat catch + abort(403).                    │
 * │  4. PendaftaranService TIDAK ditulis ulang — hanya dipanggil setelah   │
 * │     validasi ownership dan konteks peserta selesai.                     │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Dependencies (semua di-inject via constructor):
 *  - PendaftaranService            (orchestrator admin dari M6)
 *  - PesertaProfileService         (validasi kelengkapan profil)
 *  - PesertaRepositoryInterface    (findByUserId)
 *  - JalurPendaftaranRepositoryInterface
 *  - FormulirPendaftaranRepositoryInterface
 *  - SyaratPendaftaranRepositoryInterface
 *  - KuotaJurusanRepositoryInterface
 *  - BiayaRegistrasiRepositoryInterface
 *  - PendaftaranRepositoryInterface
 *  - DokumenPesertaRepositoryInterface
 */
class PortalPendaftaranService
{
    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================

    public function __construct(
        protected PendaftaranService                    $pendaftaranService,
        protected PesertaProfileService                 $pesertaProfileService,
        protected PesertaRepositoryInterface            $pesertaRepo,
        protected JalurPendaftaranRepositoryInterface   $jalurRepo,
        protected FormulirPendaftaranRepositoryInterface $formulirRepo,
        protected SyaratPendaftaranRepositoryInterface  $syaratRepo,
        protected KuotaJurusanRepositoryInterface       $kuotaRepo,
        protected BiayaRegistrasiRepositoryInterface    $biayaRepo,
        protected PendaftaranRepositoryInterface        $pendaftaranRepo,
        protected DokumenPesertaRepositoryInterface     $dokumenRepo,
    ) {
    }

    // =========================================================================
    // 1. GET JALUR TERSEDIA — Daftar jalur yang bisa diikuti peserta
    // =========================================================================

    /**
     * Mengambil semua jalur pendaftaran yang sedang aktif berikut status
     * pendaftaran peserta di masing-masing jalur.
     *
     * Query strategy (max 5 query dengan eager loading):
     *  Q1: findByUserId($userId)                        — ambil peserta
     *  Q2: PembukaanPpdb::aktif with jalurPendaftaran  — jalur dari pembukaan aktif
     *  Q3: kuota_jurusan per jalur (via eager load)    — kalkulasi kuota
     *  Q4: pendaftaran aktif peserta                   — cek sudah_daftar per jalur
     *  Q5: syarat + biaya_registrasi (via eager load)  — detail per jalur
     *
     * Return structure per jalur:
     * [
     *   'jalur'           => JalurPendaftaran,
     *   'pembukaan'       => PembukaanPpdb,
     *   'jadwal'          => Collection<JadwalPendaftaran>,
     *   'syarat'          => Collection<SyaratPendaftaran>,
     *   'kuota_jurusan'   => Collection<KuotaJurusan>,
     *   'biaya'           => Collection<BiayaRegistrasi>,
     *   'kuota_tersedia'  => int,
     *   'sudah_daftar'    => bool,
     *   'bisa_daftar'     => bool,
     * ]
     *
     * @param  int  $userId  auth()->user()->id_user
     * @return array         ['success', 'message', 'data' => array jalur terformat]
     */
    public function getJalurTersedia(int $userId): array
    {
        try {
            // Q1: Ambil peserta
            $peserta = $this->pesertaRepo->findByUserId($userId);

            // Q2+Q3+Q4+Q5: Ambil semua pembukaan aktif beserta jalur dan eager loads
            $pembukaanAktif = PembukaanPpdb::aktif()
                ->with([
                    'lembaga',
                    'jalurPendaftaran' => function ($q) {
                        $q->where('status', 'aktif')
                          ->with([
                              'jadwalPendaftaran',
                              'syaratPendaftaran',
                              'kuotaJurusan.jurusan',
                              'biayaRegistrasi',
                          ]);
                    },
                    'tahunPelajaran',
                ])
                ->get();

            if ($pembukaanAktif->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Tidak ada pendaftaran yang sedang dibuka saat ini.',
                    'data'    => [],
                ];
            }

            // Ambil semua pendaftaran aktif peserta (jika peserta sudah ada) — 1 query
            $pendaftaranAktifPeserta = collect();
            if ($peserta) {
                $statusAktif = [
                    Pendaftaran::STATUS_DRAFT,
                    Pendaftaran::STATUS_SUBMIT,
                    Pendaftaran::STATUS_VERIFIKASI,
                    Pendaftaran::STATUS_LULUS,
                    Pendaftaran::STATUS_DAFTAR_ULANG,
                    Pendaftaran::STATUS_SISWA_TETAP,
                ];

                $pendaftaranAktifPeserta = $this->pendaftaranRepo
                    ->datatable()
                    ->where('peserta_id', $peserta->id)
                    ->whereIn('status', $statusAktif)
                    ->get()
                    ->keyBy('jalur_pendaftaran_id');
            }

            // Format per jalur
            $result = [];
            foreach ($pembukaanAktif as $pembukaan) {
                foreach ($pembukaan->jalurPendaftaran as $jalur) {
                    // Cek apakah jadwal pendaftaran masih aktif
                    $jadwalAktif = $jalur->jadwalPendaftaran
                        ->filter(fn($j) =>
                            $j->tipe === 'pendaftaran'
                            && now()->between($j->mulai, $j->selesai)
                        )
                        ->first();

                    // Hitung kuota tersedia
                    $totalKuota  = $jalur->kuotaJurusan->sum('kuota');
                    $totalTerisi = $jalur->kuotaJurusan->sum('terisi');
                    $kuotaTersedia = max(0, $totalKuota - $totalTerisi);

                    // Flag sudah_daftar
                    $sudahDaftar = $peserta
                        ? $pendaftaranAktifPeserta->has($jalur->id)
                        : false;

                    // Flag bisa_daftar: kuota ada AND jadwal aktif AND belum daftar
                    $bisaDaftar = $kuotaTersedia > 0
                        && $jadwalAktif !== null
                        && !$sudahDaftar;

                    $result[] = [
                        'jalur'           => $jalur,
                        'pembukaan'       => $pembukaan,
                        'lembaga'         => $pembukaan->lembaga,
                        'tahun_pelajaran' => $pembukaan->tahunPelajaran,
                        'jadwal'          => $jalur->jadwalPendaftaran,
                        'jadwal_aktif'    => $jadwalAktif,
                        'syarat'          => $jalur->syaratPendaftaran,
                        'kuota_jurusan'   => $jalur->kuotaJurusan,
                        'biaya'           => $jalur->biayaRegistrasi,
                        'kuota_tersedia'  => $kuotaTersedia,
                        'sudah_daftar'    => $sudahDaftar,
                        'bisa_daftar'     => $bisaDaftar,
                        'pendaftaran_aktif' => $sudahDaftar
                            ? $pendaftaranAktifPeserta->get($jalur->id)
                            : null,
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Daftar jalur pendaftaran berhasil diambil.',
                'data'    => $result,
            ];
        } catch (Exception $e) {
            Log::error('[PortalPendaftaranService::getJalurTersedia] ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil daftar jalur pendaftaran.',
                'data'    => [],
            ];
        }
    }

    // =========================================================================
    // 2. MULAI PENDAFTARAN — Buat pendaftaran baru via portal peserta
    // =========================================================================

    /**
     * Memulai pendaftaran baru untuk peserta pada jalur tertentu.
     *
     * Validasi yang dilakukan (berurutan, berhenti di error pertama):
     *  1. Peserta harus ada (findByUserId)
     *  2. bisa_daftar === true (dari getJalurTersedia — kuota > 0, jadwal aktif, belum daftar)
     *  3. Profil cukup untuk mendaftar (isProfilCukupUntukDaftar dari PesertaProfileService)
     *  4. Ambil tahun_pelajaran_id dari pembukaan_ppdb yang aktif di jalur tersebut
     *
     * Jika semua valid → delegasi ke PendaftaranService->store().
     *
     * @param  int  $userId  auth()->user()->id_user
     * @param  int  $jalurId ID jalur_pendaftaran yang dipilih
     * @return array         ['success', 'message', 'data' => Pendaftaran|null]
     */
    public function mulaiPendaftaran(int $userId, int $jalurId): array
    {
        try {
            // 1. Ambil peserta
            $peserta = $this->pesertaRepo->findByUserId($userId);
            if (!$peserta) {
                return [
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan. Silakan lengkapi profil Anda terlebih dahulu.',
                    'data'    => null,
                ];
            }

            // 2. Validasi bisa_daftar dari getJalurTersedia
            $jalurResult = $this->getJalurTersedia($userId);
            if (!$jalurResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Gagal memvalidasi status jalur pendaftaran.',
                    'data'    => null,
                ];
            }

            $jalurData = collect($jalurResult['data'])->firstWhere(
                fn($item) => $item['jalur']->id === $jalurId
            );

            if (!$jalurData) {
                return [
                    'success' => false,
                    'message' => 'Jalur pendaftaran tidak ditemukan atau tidak aktif.',
                    'data'    => null,
                ];
            }

            if (!$jalurData['bisa_daftar']) {
                // Berikan pesan yang lebih spesifik
                if ($jalurData['sudah_daftar']) {
                    return [
                        'success' => false,
                        'message' => 'Anda sudah memiliki pendaftaran aktif di jalur ini. Tidak dapat mendaftar ganda.',
                        'data'    => null,
                    ];
                }

                if ($jalurData['kuota_tersedia'] <= 0) {
                    return [
                        'success' => false,
                        'message' => 'Kuota jalur pendaftaran ini sudah penuh.',
                        'data'    => null,
                    ];
                }

                if (!$jalurData['jadwal_aktif']) {
                    return [
                        'success' => false,
                        'message' => 'Pendaftaran untuk jalur ini sedang tidak dibuka. Periksa jadwal pendaftaran.',
                        'data'    => null,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Pendaftaran tidak dapat dilakukan saat ini.',
                    'data'    => null,
                ];
            }

            // 3. Validasi kelengkapan profil
            $profilCheck = $this->pesertaProfileService->isProfilCukupUntukDaftar($userId);
            if (!$profilCheck['cukup']) {
                return [
                    'success' => false,
                    'message' => 'Profil Anda belum lengkap untuk mendaftar. Silakan lengkapi data berikut: '
                        . implode(', ', $profilCheck['kekurangan']) . '.',
                    'data'    => null,
                ];
            }

            // 4. Ambil tahun_pelajaran_id dari pembukaan aktif di jalur ini
            $tahunId = $jalurData['tahun_pelajaran']?->id;
            if (!$tahunId) {
                return [
                    'success' => false,
                    'message' => 'Tahun pelajaran untuk jalur ini tidak ditemukan. Hubungi administrator.',
                    'data'    => null,
                ];
            }

            // 5. Delegasi ke PendaftaranService->store()
            return $this->pendaftaranService->store(
                $peserta->id,
                $jalurId,
                $tahunId,
                $userId
            );
        } catch (Exception $e) {
            Log::error('[PortalPendaftaranService::mulaiPendaftaran] ' . $e->getMessage(), [
                'user_id'  => $userId,
                'jalur_id' => $jalurId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memulai pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 3. GET PENDAFTARAN SAYA — List semua pendaftaran milik peserta ini
    // =========================================================================

    /**
     * Mengambil seluruh riwayat pendaftaran milik peserta yang sedang login.
     *
     * Tidak memerlukan ownership check per-item karena query sudah di-scope
     * ke peserta_id yang berasal dari userId yang terautentikasi.
     *
     * @param  int  $userId  auth()->user()->id_user
     * @return array         ['success', 'message', 'data' => Collection<Pendaftaran>]
     */
    public function getPendaftaranSaya(int $userId): array
    {
        try {
            $peserta = $this->pesertaRepo->findByUserId($userId);
            if (!$peserta) {
                return [
                    'success' => true,
                    'message' => 'Belum ada pendaftaran.',
                    'data'    => [],
                ];
            }

            $pendaftaran = $this->pendaftaranRepo
                ->datatable()
                ->where('peserta_id', $peserta->id)
                ->with([
                    'lembaga',
                    'jalurPendaftaran.pembukaanPpdb.lembaga',
                    'tahunPelajaran',
                ])
                ->latest('created_at')
                ->get();

            return [
                'success' => true,
                'message' => 'Daftar pendaftaran berhasil diambil.',
                'data'    => $pendaftaran,
            ];
        } catch (Exception $e) {
            Log::error('[PortalPendaftaranService::getPendaftaranSaya] ' . $e->getMessage(), [
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil daftar pendaftaran.',
                'data'    => [],
            ];
        }
    }

    // =========================================================================
    // 4. GET DETAIL PENDAFTARAN — Detail lengkap + ownership check
    // =========================================================================

    /**
     * Mengambil detail lengkap satu pendaftaran beserta semua relasi.
     *
     * OWNERSHIP CHECK: Memastikan Pendaftaran->peserta->user_id === $userId.
     * Jika bukan milik peserta yang sedang login → throw AuthorizationException.
     *
     * Return data mencakup: pendaftaran + jalur + field_values + dokumen + progress.
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @return array                ['success', 'message', 'data' => array detail]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function getDetailPendaftaran(int $pendaftaranId, int $userId): array
    {
        // Ownership check — akan throw jika bukan milik user ini
        $this->authorizeOwnership($pendaftaranId, $userId);

        // Delegasi ke PendaftaranService->show() yang sudah comprehensive
        return $this->pendaftaranService->show($pendaftaranId);
    }

    // =========================================================================
    // 5. SAVE FIELD VALUES — Simpan draft field formulir
    // =========================================================================

    /**
     * Menyimpan atau memperbarui nilai field formulir pada pendaftaran.
     *
     * Guard:
     *  - Ownership check: pendaftaran harus milik $userId
     *  - Status harus 'draft'
     *
     * Format $fieldValues:
     * [
     *   ['formulir_field_id' => int, 'value' => mixed],
     *   ...
     * ]
     *
     * @param  int   $pendaftaranId  ID pendaftaran
     * @param  int   $userId         auth()->user()->id_user
     * @param  array $fieldValues    Array of field values
     * @return array                 ['success', 'message', 'data' => array saved]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function saveFieldValues(int $pendaftaranId, int $userId, array $fieldValues): array
    {
        // Ownership check
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        // Guard status harus draft
        if ($pendaftaran->status !== Pendaftaran::STATUS_DRAFT) {
            return [
                'success' => false,
                'message' => "Field formulir hanya dapat diedit saat status draft. Status saat ini: {$pendaftaran->status}.",
                'data'    => null,
            ];
        }

        // Delegasi ke PendaftaranService
        return $this->pendaftaranService->saveDraftFieldValues(
            $pendaftaranId,
            $fieldValues,
            $userId
        );
    }

    // =========================================================================
    // 6. UPLOAD DOKUMEN — Upload file syarat pendaftaran
    // =========================================================================

    /**
     * Mengunggah file dokumen persyaratan untuk satu pendaftaran.
     *
     * Guard:
     *  - Ownership check: pendaftaran harus milik $userId
     *  - Status harus 'draft'
     *
     * Validasi file (ukuran, MIME type) dilakukan di dalam PendaftaranService.
     *
     * @param  int          $pendaftaranId  ID pendaftaran
     * @param  int          $userId         auth()->user()->id_user
     * @param  int          $syaratId       ID syarat_pendaftaran
     * @param  UploadedFile $file           File yang diunggah
     * @return array                        ['success', 'message', 'data' => DokumenPeserta]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function uploadDokumen(
        int $pendaftaranId,
        int $userId,
        int $syaratId,
        UploadedFile $file
    ): array {
        // Ownership check
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        // Guard status harus draft
        if ($pendaftaran->status !== Pendaftaran::STATUS_DRAFT) {
            return [
                'success' => false,
                'message' => "Dokumen hanya dapat diupload saat status draft. Status saat ini: {$pendaftaran->status}.",
                'data'    => null,
            ];
        }

        // Delegasi ke PendaftaranService
        return $this->pendaftaranService->uploadDokumen(
            $pendaftaranId,
            $syaratId,
            $file,
            $userId
        );
    }

    // =========================================================================
    // 7. HAPUS DOKUMEN — Hapus file + record DokumenPeserta
    // =========================================================================

    /**
     * Menghapus dokumen yang sudah diupload dari storage dan database.
     *
     * Guard:
     *  - Ownership check pada pendaftaran ($pendaftaranId harus milik $userId)
     *  - Status pendaftaran harus 'draft'
     *  - dokumen_id harus benar-benar milik pendaftaran tersebut (cegah IDOR)
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @param  int  $dokumenId      ID dokumen_peserta yang akan dihapus
     * @return array                ['success', 'message', 'data' => null]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function hapusDokumen(int $pendaftaranId, int $userId, int $dokumenId): array
    {
        // Ownership check pada pendaftaran
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        // Guard status harus draft
        if ($pendaftaran->status !== Pendaftaran::STATUS_DRAFT) {
            return [
                'success' => false,
                'message' => "Dokumen hanya dapat dihapus saat status draft. Status saat ini: {$pendaftaran->status}.",
                'data'    => null,
            ];
        }

        try {
            // Ambil record dokumen
            $dokumen = $this->dokumenRepo->findById($dokumenId);
            if (!$dokumen) {
                return [
                    'success' => false,
                    'message' => "Dokumen dengan ID {$dokumenId} tidak ditemukan.",
                    'data'    => null,
                ];
            }

            // Double-check: dokumen harus benar-benar milik pendaftaran ini (cegah IDOR)
            if ($dokumen->pendaftaran_id !== $pendaftaranId) {
                throw new AuthorizationException(
                    'Dokumen ini bukan bagian dari pendaftaran Anda.'
                );
            }

            DB::transaction(function () use ($dokumen) {
                // Hapus file dari storage
                if ($dokumen->path_file && Storage::disk('public')->exists($dokumen->path_file)) {
                    Storage::disk('public')->delete($dokumen->path_file);
                }

                // Hapus record database
                $this->dokumenRepo->delete($dokumen->id);
            });

            return [
                'success' => true,
                'message' => 'Dokumen berhasil dihapus.',
                'data'    => null,
            ];
        } catch (AuthorizationException $e) {
            throw $e; // Re-throw agar Controller bisa catch + abort(403)
        } catch (Exception $e) {
            Log::error('[PortalPendaftaranService::hapusDokumen] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $userId,
                'dokumen_id'     => $dokumenId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 8. SUBMIT PENDAFTARAN — Ajukan pendaftaran (draft → submit)
    // =========================================================================

    /**
     * Mengajukan pendaftaran dari status draft ke submit.
     *
     * Guard:
     *  - Ownership check: pendaftaran harus milik $userId
     *  - Status harus 'draft'
     *
     * Validasi kelengkapan (field wajib + dokumen wajib) dilakukan
     * di dalam PendaftaranService->submit().
     *
     * Return format diperluas dengan errors_detail jika validasi gagal:
     * ['success' => bool, 'message' => string, 'data' => mixed, 'errors_detail' => array|null]
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @return array                ['success', 'message', 'data', 'errors_detail' => array|null]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function submitPendaftaran(int $pendaftaranId, int $userId): array
    {
        // Ownership check
        $pendaftaran = $this->authorizeOwnership($pendaftaranId, $userId);

        // Guard status harus draft
        if ($pendaftaran->status !== Pendaftaran::STATUS_DRAFT) {
            return [
                'success'       => false,
                'message'       => "Hanya pendaftaran berstatus draft yang dapat disubmit. Status saat ini: {$pendaftaran->status}.",
                'data'          => null,
                'errors_detail' => null,
            ];
        }

        // Delegasi ke PendaftaranService->submit()
        $result = $this->pendaftaranService->submit($pendaftaranId, $userId);

        // Normalisasi response — tambahkan errors_detail dari 'errors' PendaftaranService
        return [
            'success'       => $result['success'],
            'message'       => $result['message'],
            'data'          => $result['data'] ?? null,
            'errors_detail' => $result['errors'] ?? null,
        ];
    }

    // =========================================================================
    // 9. GET PROGRESS DETAIL — Progress kelengkapan terformat untuk UI
    // =========================================================================

    /**
     * Mengambil dan memformat progress kelengkapan pendaftaran untuk tampilan UI peserta.
     *
     * Guard: Ownership check.
     *
     * Format return yang diformat untuk UI:
     * [
     *   'formulir' => [
     *     'persen'   => float    (0-100),
     *     'terisi'   => int,
     *     'total'    => int,
     *     'kurang'   => string[] (label field yang belum terisi),
     *   ],
     *   'dokumen' => [
     *     'persen'   => float    (0-100),
     *     'uploaded' => int,
     *     'total'    => int,
     *     'kurang'   => string[] (nama syarat yang belum diupload),
     *   ],
     *   'siap_submit' => bool,
     * ]
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         auth()->user()->id_user
     * @return array                ['success', 'message', 'data' => array progress terformat]
     *
     * @throws AuthorizationException Jika pendaftaran bukan milik $userId
     */
    public function getProgressDetail(int $pendaftaranId, int $userId): array
    {
        // Ownership check
        $this->authorizeOwnership($pendaftaranId, $userId);

        try {
            // Ambil raw progress dari PendaftaranService
            $rawProgress = $this->pendaftaranService->getProgressLengkapan($pendaftaranId);

            // Ambil daftar field wajib yang kurang (detail label)
            $fieldKurang   = $this->getFieldKurangDetail($pendaftaranId);
            $dokumenKurang = $this->getDokumenKurangDetail($pendaftaranId);

            // Format untuk UI peserta
            $formatted = [
                'formulir' => [
                    'persen'  => $rawProgress['field_wajib']['percent'],
                    'terisi'  => $rawProgress['field_wajib']['terisi'],
                    'total'   => $rawProgress['field_wajib']['total'],
                    'kurang'  => $fieldKurang,
                ],
                'dokumen' => [
                    'persen'   => $rawProgress['dokumen_wajib']['percent'],
                    'uploaded' => $rawProgress['dokumen_wajib']['uploaded'],
                    'total'    => $rawProgress['dokumen_wajib']['total'],
                    'kurang'   => $dokumenKurang,
                ],
                'siap_submit' => $rawProgress['siap_submit'],
            ];

            return [
                'success' => true,
                'message' => 'Progress pendaftaran berhasil diambil.',
                'data'    => $formatted,
            ];
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('[PortalPendaftaranService::getProgressDetail] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil progress pendaftaran.',
                'data'    => null,
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
     *  - Load Pendaftaran dengan relasi 'peserta' (1 query dengan eager load)
     *  - Bandingkan: Pendaftaran->peserta->user_id === $userId
     *
     * Throw AuthorizationException (bukan return error) agar:
     *  - Controller dapat catch + abort(403) secara konsisten
     *  - Exception tidak ter-catch di catch(Exception) biasa — harus di-catch secara eksplisit
     *
     * @param  int         $pendaftaranId  ID pendaftaran yang akan dicek
     * @param  int         $userId         ID user yang sedang login
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

        // Strict comparison (===) untuk mencegah type juggling
        if ((int) ($pendaftaran->peserta?->user_id) !== $userId) {
            Log::warning('[PortalPendaftaranService] Unauthorized access attempt', [
                'pendaftaran_id'    => $pendaftaranId,
                'requesting_user'   => $userId,
                'owner_user_id'     => $pendaftaran->peserta?->user_id,
            ]);

            throw new AuthorizationException(
                'Anda tidak memiliki akses ke pendaftaran ini.'
            );
        }

        return $pendaftaran;
    }

    // =========================================================================
    // PRIVATE — DETAIL HELPERS UNTUK getProgressDetail
    // =========================================================================

    /**
     * Mengambil label field formulir wajib yang BELUM terisi.
     *
     * Digunakan eksklusif oleh getProgressDetail untuk mengisi 'kurang' pada section formulir.
     *
     * @param  int      $pendaftaranId  ID pendaftaran
     * @return string[]                 Array label field yang kurang
     */
    private function getFieldKurangDetail(int $pendaftaranId): array
    {
        try {
            $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId);
            if (!$pendaftaran) {
                return [];
            }

            $jalurId = $pendaftaran->jalur_pendaftaran_id;

            $formulir = $this->formulirRepo->all(['jalur_pendaftaran_id' => $jalurId])->first();
            if (!$formulir) {
                return [];
            }

            // Eager load fields via formulirRepo
            $formulirDenganField = $this->formulirRepo->findWithFields($formulir->id);
            $fieldWajib = collect($formulirDenganField?->formulirField ?? [])
                ->filter(fn($f) => $f->is_required);

            if ($fieldWajib->isEmpty()) {
                return [];
            }

            // Ambil field values yang sudah terisi
            $pendaftaranLoaded = $this->pendaftaranRepo->findById($pendaftaranId, [
                'pendaftaranFieldValue',
            ]);

            $filledFieldIds = collect($pendaftaranLoaded?->pendaftaranFieldValue ?? [])
                ->filter(fn($fv) => !is_null($fv->value) && trim((string) $fv->value) !== '')
                ->pluck('formulir_field_id')
                ->toArray();

            $kurang = [];
            foreach ($fieldWajib as $field) {
                if (!in_array($field->id, $filledFieldIds)) {
                    $kurang[] = $field->label;
                }
            }

            return $kurang;
        } catch (\Throwable $e) {
            Log::warning('[PortalPendaftaranService::getFieldKurangDetail] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mengambil nama syarat wajib yang BELUM diupload dokumennya.
     *
     * Digunakan eksklusif oleh getProgressDetail untuk mengisi 'kurang' pada section dokumen.
     *
     * @param  int      $pendaftaranId  ID pendaftaran
     * @return string[]                 Array nama syarat yang kurang
     */
    private function getDokumenKurangDetail(int $pendaftaranId): array
    {
        try {
            $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId);
            if (!$pendaftaran) {
                return [];
            }

            $jalurId = $pendaftaran->jalur_pendaftaran_id;

            // Ambil semua syarat wajib
            $syaratWajib = $this->syaratRepo->all([
                'jalur_pendaftaran_id' => $jalurId,
                'wajib'                => true,
            ]);

            if ($syaratWajib->isEmpty()) {
                return [];
            }

            // Ambil syarat_id yang sudah diupload
            $uploadedSyaratIds = $this->dokumenRepo
                ->findByPendaftaranId($pendaftaranId)
                ->pluck('syarat_pendaftaran_id')
                ->toArray();

            $kurang = [];
            foreach ($syaratWajib as $syarat) {
                if (!in_array($syarat->id, $uploadedSyaratIds)) {
                    $kurang[] = $syarat->nama;
                }
            }

            return $kurang;
        } catch (\Throwable $e) {
            Log::warning('[PortalPendaftaranService::getDokumenKurangDetail] ' . $e->getMessage());
            return [];
        }
    }
}
