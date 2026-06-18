<?php

namespace App\Services\Transaksi;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranFieldValueRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;
use App\Repositories\Ppdb\SyaratPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\FormulirPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\KuotaJurusanRepositoryInterface;
use App\Repositories\Ppdb\JadwalPendaftaranRepositoryInterface;

use App\Services\NotifikasiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;

use App\Models\Transaksi\Pendaftaran;
use App\Models\Ppdb\FormulirField;

/**
 * PendaftaranService
 *
 * Orkestrator alur pendaftaran PPDB dari awal hingga verifikasi admin.
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │  STATE MACHINE — Urutan transisi status yang valid (NO SKIP):       │
 * │                                                                     │
 * │   draft ──► submit ──► verifikasi ──► lulus ──► daftar_ulang       │
 * │     ▲                       │         tidak_lulus                   │
 * │     └───────────────────────┘ (reject kembali ke draft)            │
 * │                                                 daftar_ulang ──► siswa_tetap
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * Dependencies (semua di-inject via constructor):
 *  - PendaftaranRepositoryInterface
 *  - PendaftaranFieldValueRepositoryInterface
 *  - DokumenPesertaRepositoryInterface
 *  - SyaratPendaftaranRepositoryInterface
 *  - FormulirPendaftaranRepositoryInterface
 *  - KuotaJurusanRepositoryInterface
 *  - JadwalPendaftaranRepositoryInterface
 *  - NotifikasiService
 *  - LogActivityService
 *  - ResponseService
 */
class PendaftaranService
{
    /**
     * Peta transisi status yang diizinkan.
     * KEY   = status saat ini
     * VALUE = array status yang boleh dituju dari KEY
     *
     * @var array<string, string[]>
     */
    private const VALID_TRANSITIONS = [
        Pendaftaran::STATUS_DRAFT         => [Pendaftaran::STATUS_SUBMIT],
        Pendaftaran::STATUS_SUBMIT        => [Pendaftaran::STATUS_VERIFIKASI, Pendaftaran::STATUS_DRAFT],
        Pendaftaran::STATUS_VERIFIKASI    => [Pendaftaran::STATUS_LULUS, Pendaftaran::STATUS_TIDAK_LULUS, Pendaftaran::STATUS_DRAFT],
        Pendaftaran::STATUS_LULUS         => [Pendaftaran::STATUS_DAFTAR_ULANG],
        Pendaftaran::STATUS_TIDAK_LULUS   => [],
        Pendaftaran::STATUS_DAFTAR_ULANG  => [Pendaftaran::STATUS_SISWA_TETAP],
        Pendaftaran::STATUS_SISWA_TETAP   => [],
    ];

    /**
     * Ukuran maksimum upload dokumen dalam kilobytes (5 MB).
     */
    private const MAX_FILE_SIZE_KB = 5120;

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================

    public function __construct(
        protected PendaftaranRepositoryInterface            $pendaftaranRepo,
        protected PendaftaranFieldValueRepositoryInterface  $fieldValueRepo,
        protected DokumenPesertaRepositoryInterface         $dokumenRepo,
        protected SyaratPendaftaranRepositoryInterface      $syaratRepo,
        protected FormulirPendaftaranRepositoryInterface    $formulirRepo,
        protected KuotaJurusanRepositoryInterface           $kuotaRepo,
        protected JadwalPendaftaranRepositoryInterface      $jadwalRepo,
        protected NotifikasiService                         $notifikasiService,
        protected LogActivityService                        $logActivity,
        protected ResponseService                           $responseService,
    ) {
    }

    // =========================================================================
    // 1. INDEX — DataTable list semua pendaftaran
    // =========================================================================

    /**
     * Menyediakan data untuk Yajra DataTable daftar pendaftaran.
     *
     * Filter yang didukung:
     *  - status       : filter exact match pada kolom status
     *  - jalur_id     : filter jalur_pendaftaran_id
     *  - tahun_id     : filter tahun_pelajaran_id
     *  - nama_peserta : filter LIKE pada nama_lengkap peserta (join)
     *
     * Return berisi Eloquent Builder yang dipakai DataTables::of() di Controller.
     *
     * @param  array $filters  Associative array filter dari request
     * @return array           ['success', 'message', 'data' => Builder]
     */
    public function index(array $filters = []): array
    {
        try {
            $query = $this->pendaftaranRepo->datatable()
                ->with([
                    // Hanya kolom yang dipakai di DataTable (foto, nama, nisn)
                    'peserta:id,nama_lengkap,foto,nisn',
                    // Hanya nama jalur
                    'jalurPendaftaran:id,nama,kode_jalur',
                    // Nama tahun
                    'tahunPelajaran:id,nama',
                    // Untuk tampilan status verifikator
                    'verifier:id_user,name',
                    // Status pembayaran terbaru — penting untuk info lunas/belum
                    'pembayaranPpdb:id,pendaftaran_id,status,amount',
                ]);

            // Filter: status (exact match)
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Filter: jalur_id
            if (!empty($filters['jalur_id'])) {
                $query->where('jalur_pendaftaran_id', $filters['jalur_id']);
            }

            // Filter: tahun_id
            if (!empty($filters['tahun_id'])) {
                $query->where('tahun_pelajaran_id', $filters['tahun_id']);
            }

            // Filter: lembaga_id
            if (!empty($filters['lembaga_id'])) {
                $query->where('pendaftaran.lembaga_id', $filters['lembaga_id']);
            }

            // Filter: nama_peserta — join ke tabel peserta (hati-hati ambiguitas kolom)
            if (!empty($filters['nama_peserta'])) {
                $query->whereHas('peserta', function ($q) use ($filters) {
                    $q->where('nama_lengkap', 'LIKE', '%' . $filters['nama_peserta'] . '%');
                });
            }

            return [
                'success' => true,
                'message' => 'Data pendaftaran berhasil diambil.',
                'data'    => $query->latest('pendaftaran.created_at'),
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::index] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data pendaftaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 2. STORE — Buat pendaftaran baru (status = draft)
    // =========================================================================

    /**
     * Membuat pendaftaran baru untuk peserta pada jalur dan tahun pelajaran tertentu.
     *
     * Validasi bisnis yang dilakukan:
     *  1. Jadwal pendaftaran masih aktif (tipe = 'pendaftaran', now() dalam range mulai-selesai)
     *  2. Kuota jalur/jurusan belum penuh (terisi < kuota)
     *  3. Tidak double daftar — 1 peserta hanya 1 pendaftaran aktif per jalur per tahun
     *
     * Flow:
     *  - Generate no_pendaftaran unik format: PPDB{tahun}{5digit}, contoh: PPDB202600001
     *  - Simpan pendaftaran dengan status = draft
     *
     * @param  int  $pesertaId  ID peserta yang mendaftar
     * @param  int  $jalurId    ID jalur pendaftaran
     * @param  int  $tahunId    ID tahun pelajaran
     * @param  int  $userId     ID user yang melakukan aksi (untuk log)
     * @return array            ['success', 'message', 'data' => Pendaftaran|null]
     */
    public function store(int $pesertaId, int $jalurId, int $tahunId, int $userId): array
    {
        // --- Validasi 1: Jadwal pendaftaran aktif ---
        $jadwalAktif = $this->jadwalRepo->findAktifByJalurAndTipe($jalurId, 'pendaftaran');
        if (!$jadwalAktif) {
            return [
                'success' => false,
                'message' => 'Pendaftaran untuk jalur ini sedang tidak dibuka. Periksa jadwal pendaftaran.',
                'data'    => null,
            ];
        }

        // --- Validasi 2: Kuota belum penuh ---
        $kuotaList = $this->kuotaRepo->all(['jalur_pendaftaran_id' => $jalurId, 'tahun_pelajaran_id' => $tahunId]);
        $kuotaPenuh = $kuotaList->every(fn($k) => $k->terisi >= $k->kuota);

        if ($kuotaList->isNotEmpty() && $kuotaPenuh) {
            return [
                'success' => false,
                'message' => 'Kuota jalur pendaftaran ini sudah penuh.',
                'data'    => null,
            ];
        }

        // --- Validasi 3: Tidak double daftar ---
        $statusAktif = [
            Pendaftaran::STATUS_DRAFT,
            Pendaftaran::STATUS_SUBMIT,
            Pendaftaran::STATUS_VERIFIKASI,
            Pendaftaran::STATUS_LULUS,
            Pendaftaran::STATUS_DAFTAR_ULANG,
            Pendaftaran::STATUS_SISWA_TETAP,
        ];

        $existing = $this->pendaftaranRepo->datatable()
            ->where('peserta_id', $pesertaId)
            ->where('jalur_pendaftaran_id', $jalurId)
            ->where('tahun_pelajaran_id', $tahunId)
            ->whereIn('status', $statusAktif)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => "Peserta sudah memiliki pendaftaran aktif di jalur ini (#{$existing->no_pendaftaran}). Tidak dapat mendaftar ganda.",
                'data'    => null,
            ];
        }

        // --- Generate no_pendaftaran unik ---
        $noPendaftaran = $this->generateNoPendaftaranUnik($tahunId);

        // --- Buat pendaftaran ---
        try {
            $pendaftaran = $this->pendaftaranRepo->create([
                'no_pendaftaran'      => $noPendaftaran,
                'peserta_id'          => $pesertaId,
                'jalur_pendaftaran_id' => $jalurId,
                'tahun_pelajaran_id'  => $tahunId,
                'status'              => Pendaftaran::STATUS_DRAFT,
            ]);

            $this->logActivity->log(
                'Buat Pendaftaran',
                "Pendaftaran baru dibuat: #{$noPendaftaran} (Peserta ID: {$pesertaId}, Jalur ID: {$jalurId})"
            );

            return [
                'success' => true,
                'message' => "Pendaftaran #{$noPendaftaran} berhasil dibuat. Silakan lengkapi formulir dan dokumen.",
                'data'    => $pendaftaran->load(['peserta', 'jalurPendaftaran', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::store] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membuat pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 3. SAVE DRAFT FIELD VALUES — Simpan nilai field formulir dinamis
    // =========================================================================

    /**
     * Menyimpan atau memperbarui nilai-nilai field formulir dinamis pada pendaftaran.
     *
     * Hanya bisa dilakukan jika status pendaftaran = draft.
     * Setiap field di-upsert: jika sudah ada → update, belum ada → insert.
     *
     * Format $fieldValues yang diharapkan:
     * [
     *   ['formulir_field_id' => 1, 'value' => 'John'],
     *   ['formulir_field_id' => 2, 'value' => '2005-03-15'],
     *   ...
     * ]
     *
     * @param  int   $pendaftaranId  ID pendaftaran
     * @param  array $fieldValues    Array of ['formulir_field_id' => int, 'value' => mixed]
     * @param  int   $userId         ID user yang melakukan aksi
     * @return array                 ['success', 'message', 'data' => array field values tersimpan]
     */
    public function saveDraftFieldValues(int $pendaftaranId, array $fieldValues, int $userId): array
    {
        // Ambil pendaftaran dan guard status
        $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId);
        if (!$pendaftaran) {
            return $this->notFound('Pendaftaran', $pendaftaranId);
        }

        if ($pendaftaran->status !== Pendaftaran::STATUS_DRAFT) {
            return [
                'success' => false,
                'message' => "Field formulir hanya dapat diedit saat status draft. Status saat ini: {$pendaftaran->status}.",
                'data'    => null,
            ];
        }

        try {
            $saved = [];
            foreach ($fieldValues as $item) {
                $fieldId = $item['formulir_field_id'] ?? null;
                $value   = $item['value'] ?? null;

                if (!$fieldId) {
                    continue;
                }

                $record = $this->fieldValueRepo->upsertFieldValue($pendaftaranId, (int) $fieldId, $value);
                $saved[] = $record;
            }

            $this->logActivity->log(
                'Simpan Draft Field',
                "Draft field values disimpan untuk pendaftaran #{$pendaftaran->no_pendaftaran} (" . count($saved) . " field)"
            );

            return [
                'success' => true,
                'message' => count($saved) . ' field berhasil disimpan.',
                'data'    => $saved,
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::saveDraftFieldValues] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyimpan field values: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 4. UPLOAD DOKUMEN — Upload file dokumen persyaratan
    // =========================================================================

    /**
     * Mengunggah dan menyimpan file dokumen persyaratan pendaftaran.
     *
     * Aturan bisnis:
     *  - Validasi MIME type file harus sesuai konfigurasi tipe syarat
     *  - Ukuran maksimum: 5MB (5120 KB)
     *  - Path penyimpanan: storage/pendaftaran/{no_pendaftaran}/{syarat_id}_{timestamp}.{ext}
     *  - Jika sudah ada dokumen untuk syarat yang sama, file lama dihapus dan diganti (upsert)
     *  - Tidak ada restriksi status: dokumen bisa diupload kapan saja sebelum submit
     *
     * @param  int          $pendaftaranId  ID pendaftaran
     * @param  int          $syaratId       ID syarat_pendaftaran
     * @param  UploadedFile $file           File yang diunggah
     * @param  int          $userId         ID user yang melakukan aksi
     * @return array                        ['success', 'message', 'data' => DokumenPeserta|null]
     */
    public function uploadDokumen(int $pendaftaranId, int $syaratId, UploadedFile $file, int $userId): array
    {
        // Ambil pendaftaran
        $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId);
        if (!$pendaftaran) {
            return $this->notFound('Pendaftaran', $pendaftaranId);
        }

        // Guard: tidak boleh upload jika sudah verifikasi/selesai
        $statusTerkunci = [
            Pendaftaran::STATUS_VERIFIKASI,
            Pendaftaran::STATUS_LULUS,
            Pendaftaran::STATUS_TIDAK_LULUS,
            Pendaftaran::STATUS_DAFTAR_ULANG,
            Pendaftaran::STATUS_SISWA_TETAP,
        ];
        if (in_array($pendaftaran->status, $statusTerkunci)) {
            return [
                'success' => false,
                'message' => "Dokumen tidak dapat diubah karena pendaftaran sudah berstatus {$pendaftaran->status}.",
                'data'    => null,
            ];
        }

        // Ambil syarat pendaftaran
        $syarat = $this->syaratRepo->findById($syaratId);
        if (!$syarat) {
            return $this->notFound('Syarat Pendaftaran', $syaratId);
        }

        // Validasi ukuran file (max 5 MB)
        if ($file->getSize() > self::MAX_FILE_SIZE_KB * 1024) {
            return [
                'success' => false,
                'message' => 'Ukuran file melebihi batas maksimum 5MB.',
                'data'    => null,
            ];
        }

        // Validasi MIME type berdasarkan tipe syarat
        $allowedMimes = $this->getAllowedMimesByTipeSyarat($syarat->tipe);
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $allowedExt = implode(', ', $this->getExtensionsByTipe($syarat->tipe));
            return [
                'success' => false,
                'message' => "Tipe file tidak valid untuk syarat ini. Format yang diizinkan: {$allowedExt}.",
                'data'    => null,
            ];
        }

        try {
            // --- Hapus file lama jika ada ---
            $existing = $this->dokumenRepo->all(['pendaftaran_id' => $pendaftaranId, 'syarat_pendaftaran_id' => $syaratId])->first();
            if ($existing && $existing->path_file) {
                Storage::disk('public')->delete($existing->path_file);
            }

            // --- Tentukan path penyimpanan ---
            $noPendaftaran = preg_replace('/[^a-zA-Z0-9]/', '_', $pendaftaran->no_pendaftaran);
            $timestamp     = now()->format('YmdHis');
            $extension     = $file->getClientOriginalExtension();
            $namaFile      = "{$syaratId}_{$timestamp}.{$extension}";
            $directory     = "pendaftaran/{$noPendaftaran}";
            $path          = $file->storeAs($directory, $namaFile, 'public');

            if (!$path) {
                throw new Exception('Gagal menyimpan file ke storage.');
            }

            // --- Upsert record dokumen_peserta ---
            $data = [
                'pendaftaran_id'        => $pendaftaranId,
                'syarat_pendaftaran_id' => $syaratId,
                'nama_file'             => $file->getClientOriginalName(),
                'path_file'             => $path,
                'mime_type'             => $file->getMimeType(),
                'ukuran_file'           => $file->getSize(),
                'status_verifikasi'     => 'pending',
                'keterangan_verifikasi' => null,
                'verified_by'           => null,
                'verified_at'           => null,
            ];

            if ($existing) {
                $dokumen = $this->dokumenRepo->update($existing->id, $data);
            } else {
                $dokumen = $this->dokumenRepo->create($data);
            }

            $this->logActivity->log(
                'Upload Dokumen',
                "Dokumen '{$syarat->nama}' diupload untuk pendaftaran #{$pendaftaran->no_pendaftaran}"
            );

            return [
                'success' => true,
                'message' => "Dokumen '{$syarat->nama}' berhasil diupload.",
                'data'    => $dokumen->load('syaratPendaftaran'),
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::uploadDokumen] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengupload dokumen: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 5. SUBMIT — Ajukan pendaftaran (draft → submit)
    // =========================================================================

    /**
     * Mengajukan pendaftaran dari status draft ke submit.
     *
     * Validasi kelengkapan yang dilakukan:
     *  1. Semua field wajib (is_required = true) harus terisi
     *  2. Semua dokumen wajib (wajib = true) harus sudah diupload
     *
     * Jika tidak lengkap, method mengembalikan detail errors per field/dokumen.
     *
     * Jika lengkap (atomic dalam DB::transaction):
     *  - Update status → submit
     *  - Set tanggal_daftar = now()
     *  - Kirim notifikasi ke admin: "Pendaftaran baru #{no} dari {nama_peserta}"
     *
     * @param  int  $pendaftaranId  ID pendaftaran
     * @param  int  $userId         ID user yang melakukan aksi
     * @return array               ['success', 'message', 'data', 'errors' => [...] jika gagal validasi]
     */
    public function submit(int $pendaftaranId, int $userId): array
    {
        // Ambil pendaftaran dengan relasi yang dibutuhkan
        $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId, [
            'peserta',
            'jalurPendaftaran',
            'tahunPelajaran',
        ]);

        if (!$pendaftaran) {
            return $this->notFound('Pendaftaran', $pendaftaranId);
        }

        // Guard: hanya bisa submit dari status draft
        if (!$this->canTransition($pendaftaran->status, Pendaftaran::STATUS_SUBMIT)) {
            return [
                'success' => false,
                'message' => "Tidak dapat submit dari status '{$pendaftaran->status}'. Submit hanya diperbolehkan dari status draft.",
                'data'    => null,
            ];
        }

        // --- Validasi kelengkapan ---
        $errors = $this->getValidationErrors($pendaftaran);

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Pendaftaran belum dapat disubmit karena ada kelengkapan yang belum terpenuhi.',
                'data'    => null,
                'errors'  => $errors,
            ];
        }

        // --- Semua valid: gunakan transaksi atomik ---
        try {
            DB::transaction(function () use ($pendaftaran) {
                // Update status dan tanggal daftar
                $this->pendaftaranRepo->updateStatus(
                    $pendaftaran->id,
                    Pendaftaran::STATUS_SUBMIT,
                    ['tanggal_daftar' => now()]
                );

                // Kirim notifikasi ke semua admin
                $namaPeserta = $pendaftaran->peserta?->nama_lengkap ?? 'Peserta';
                $noPendaftaran = $pendaftaran->no_pendaftaran;

                $this->notifikasiService->kirimKeAdmin(
                    'admin_pendaftaran_baru',
                    [
                        'nama_peserta'   => $namaPeserta,
                        'no_pendaftaran' => $noPendaftaran,
                        'jalur'          => $pendaftaran->jalurPendaftaran?->nama ?? '-',
                        'tanggal'        => now()->isoFormat('D MMMM YYYY HH:mm')
                    ]
                );

                // Kirim notifikasi ke peserta
                if ($pendaftaran->peserta?->user_id) {
                    $this->notifikasiService->kirim(
                        $pendaftaran->peserta->user_id,
                        'pendaftaran_submit',
                        [
                            'nama_peserta'   => $namaPeserta,
                            'no_pendaftaran' => $noPendaftaran,
                            'jalur'          => $pendaftaran->jalurPendaftaran?->nama ?? '-'
                        ]
                    );
                }
            });

            $this->logActivity->log(
                'Submit Pendaftaran',
                "Pendaftaran #{$pendaftaran->no_pendaftaran} disubmit oleh user ID: {$userId}"
            );

            return [
                'success' => true,
                'message' => "Pendaftaran #{$pendaftaran->no_pendaftaran} berhasil disubmit. Menunggu verifikasi admin.",
                'data'    => $this->pendaftaranRepo->findById($pendaftaran->id, ['peserta', 'jalurPendaftaran', 'tahunPelajaran']),
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::submit] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal submit pendaftaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 6. VERIFIKASI — Approve atau reject pendaftaran oleh admin
    // =========================================================================

    /**
     * Verifikasi pendaftaran oleh admin (dari status submit).
     *
     * Action yang valid:
     *  - 'approve' : status → verifikasi, catat verified_by + verified_at
     *  - 'reject'  : status → draft, simpan catatan_verifikasi, notif peserta untuk perbaikan
     *
     * State machine validasi:
     *  - Approve: submit → verifikasi (VALID)
     *  - Reject : submit → draft (VALID — diizinkan secara khusus untuk koreksi)
     *
     * @param  int         $pendaftaranId  ID pendaftaran
     * @param  string      $action         'approve' atau 'reject'
     * @param  string|null $catatan        Catatan verifikasi (wajib jika reject)
     * @param  int         $userId         ID user admin yang melakukan verifikasi
     * @return array                       ['success', 'message', 'data' => Pendaftaran|null]
     */
    public function verifikasi(int $pendaftaranId, string $action, ?string $catatan, int $userId): array
    {
        // Validasi action
        if (!in_array($action, ['approve', 'reject'])) {
            return [
                'success' => false,
                'message' => "Action tidak valid. Gunakan 'approve' atau 'reject'.",
                'data'    => null,
            ];
        }

        // Ambil pendaftaran
        $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId, ['peserta', 'peserta.kontak']);
        if (!$pendaftaran) {
            return $this->notFound('Pendaftaran', $pendaftaranId);
        }

        // Guard: hanya bisa verifikasi dari status submit
        if ($pendaftaran->status !== Pendaftaran::STATUS_SUBMIT) {
            return [
                'success' => false,
                'message' => "Verifikasi hanya dapat dilakukan saat status 'submit'. Status saat ini: {$pendaftaran->status}.",
                'data'    => null,
            ];
        }

        // Reject wajib menyertakan catatan
        if ($action === 'reject' && empty(trim($catatan ?? ''))) {
            return [
                'success' => false,
                'message' => "Catatan verifikasi wajib diisi saat melakukan reject.",
                'data'    => null,
            ];
        }

        try {
            if ($action === 'approve') {
                // submit → verifikasi
                $this->pendaftaranRepo->updateStatus(
                    $pendaftaranId,
                    Pendaftaran::STATUS_VERIFIKASI,
                    [
                        'verified_by'          => $userId,
                        'verified_at'          => now(),
                        'catatan_verifikasi'   => $catatan,
                    ]
                );

                $statusMsg = 'diverifikasi (approve)';

                $this->logActivity->log(
                    'Verifikasi Approve',
                    "Pendaftaran #{$pendaftaran->no_pendaftaran} di-approve oleh user ID: {$userId}"
                );

                // Notifikasi ke peserta
                if ($pendaftaran->peserta?->user_id) {
                    $this->notifikasiService->kirim(
                        $pendaftaran->peserta->user_id,
                        'verifikasi_approve',
                        [
                            'nama_peserta'   => $pendaftaran->peserta->nama_lengkap ?? 'Peserta',
                            'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                            'jalur'          => $pendaftaran->jalurPendaftaran?->nama ?? '-'
                        ]
                    );
                }
            } else {
                // submit → draft (reject dengan catatan)
                $this->pendaftaranRepo->updateStatus(
                    $pendaftaranId,
                    Pendaftaran::STATUS_DRAFT,
                    ['catatan_verifikasi' => $catatan]
                );

                // Notifikasi peserta untuk perbaikan
                if ($pendaftaran->peserta?->user_id) {
                    $this->notifikasiService->kirim(
                        $pendaftaran->peserta->user_id,
                        'verifikasi_reject',
                        [
                            'nama_peserta'   => $pendaftaran->peserta->nama_lengkap ?? 'Peserta',
                            'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                            'catatan'        => $catatan
                        ]
                    );
                }

                $statusMsg = 'dikembalikan (reject)';

                $this->logActivity->log(
                    'Verifikasi Reject',
                    "Pendaftaran #{$pendaftaran->no_pendaftaran} di-reject oleh user ID: {$userId}. Catatan: {$catatan}"
                );
            }

            return [
                'success' => true,
                'message' => "Pendaftaran #{$pendaftaran->no_pendaftaran} berhasil {$statusMsg}.",
                'data'    => $this->pendaftaranRepo->findById($pendaftaranId, ['peserta', 'jalurPendaftaran', 'verifier']),
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::verifikasi] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal melakukan verifikasi: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 7. SHOW — Detail lengkap pendaftaran
    // =========================================================================

    /**
     * Mengambil detail lengkap pendaftaran beserta seluruh entitas terkait.
     *
     * Data yang dikembalikan:
     *  - pendaftaran     : data utama pendaftaran
     *  - peserta         : data identitas peserta
     *  - jalur           : data jalur pendaftaran
     *  - tahun           : data tahun pelajaran
     *  - field_values    : semua nilai field formulir yang sudah diisi
     *  - dokumen         : semua dokumen yang sudah diupload beserta URL-nya
     *  - verifier        : data user yang melakukan verifikasi (jika ada)
     *
     * @param  int   $id  ID pendaftaran
     * @return array      ['success', 'message', 'data' => array detail|null]
     */
    public function show(int $id): array
    {
        try {
            $pendaftaran = $this->pendaftaranRepo->findById($id, [
                'peserta',
                'peserta.alamat',
                'peserta.kontak',
                'jalurPendaftaran',
                'tahunPelajaran',
                'verifier',
            ]);

            if (!$pendaftaran) {
                return $this->notFound('Pendaftaran', $id);
            }

            // Field values dengan detail field
            $fieldValues = $this->fieldValueRepo->findByPendaftaranId($id);

            // Dokumen dengan relasi syarat
            $dokumen = $this->dokumenRepo->findByPendaftaranId($id);

            return [
                'success' => true,
                'message' => 'Detail pendaftaran berhasil diambil.',
                'data'    => [
                    'pendaftaran'  => $pendaftaran,
                    'peserta'      => $pendaftaran->peserta,
                    'jalur'        => $pendaftaran->jalurPendaftaran,
                    'tahun'        => $pendaftaran->tahunPelajaran,
                    'field_values' => $fieldValues,
                    'dokumen'      => $dokumen->map(fn($d) => [
                        'id'               => $d->id,
                        'syarat_id'        => $d->syarat_pendaftaran_id,
                        'nama_syarat'      => $d->syaratPendaftaran?->nama,
                        'nama_file'        => $d->nama_file,
                        'path_file'        => $d->path_file,
                        'url_file'         => $d->url_file,
                        'mime_type'        => $d->mime_type,
                        'ukuran_file'      => $d->ukuran_file,
                        'status_verifikasi' => $d->status_verifikasi,
                        'keterangan'       => $d->keterangan_verifikasi,
                        'uploaded_at'      => $d->created_at?->format('d/m/Y H:i'),
                    ]),
                    'verifier' => $pendaftaran->verifier ? [
                        'id'         => $pendaftaran->verifier->id_user,
                        'name'       => $pendaftaran->verifier->name,
                        'verified_at' => $pendaftaran->verified_at?->format('d/m/Y H:i'),
                    ] : null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::show] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil detail pendaftaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 8. GET PROGRESS LENGKAPAN — Persentase kelengkapan pendaftaran
    // =========================================================================

    /**
     * Menghitung persentase kelengkapan pendaftaran (field wajib + dokumen wajib).
     *
     * TIDAK BOLEH throw exception — selalu kembalikan struktur data meski 0.
     * Aman dipanggil kapan saja, termasuk dari blade/view.
     *
     * Return format:
     * [
     *   'field_wajib'   => ['total' => int, 'terisi' => int, 'percent' => float],
     *   'dokumen_wajib' => ['total' => int, 'uploaded' => int, 'percent' => float],
     *   'siap_submit'   => bool,
     * ]
     *
     * @param  int   $pendaftaranId  ID pendaftaran
     * @return array                 Selalu return array, tidak pernah throw
     */
    public function getProgressLengkapan(int $pendaftaranId): array
    {
        // Default fallback — aman jika terjadi exception apapun
        $defaultResult = [
            'field_wajib' => [
                'total'   => 0,
                'terisi'  => 0,
                'percent' => 0.0,
            ],
            'dokumen_wajib' => [
                'total'    => 0,
                'uploaded' => 0,
                'percent'  => 0.0,
            ],
            'siap_submit' => false,
        ];

        try {
            $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId, ['jalurPendaftaran']);

            if (!$pendaftaran) {
                return $defaultResult;
            }

            $jalurId = $pendaftaran->jalur_pendaftaran_id;

            // --- Hitung field wajib ---
            $formulir = $this->formulirRepo->all(['jalur_pendaftaran_id' => $jalurId])->first();
            $totalFieldWajib = 0;
            $terisiFieldWajib = 0;

            if ($formulir) {
                // Ambil field wajib dari formulir
                $formulirDenganField = $this->formulirRepo->findWithFields($formulir->id);
                $fieldWajib = collect($formulirDenganField?->formulirField ?? [])
                    ->filter(fn($f) => $f->is_required);

                $totalFieldWajib = $fieldWajib->count();

                if ($totalFieldWajib > 0) {
                    // Ambil field values yang sudah terisi
                    $fieldValues = $this->fieldValueRepo->findByPendaftaranId($pendaftaranId)
                        ->keyBy('formulir_field_id');

                    foreach ($fieldWajib as $field) {
                        $fv = $fieldValues->get($field->id);
                        if ($fv && !is_null($fv->value) && trim((string) $fv->value) !== '') {
                            $terisiFieldWajib++;
                        }
                    }
                }
            }

            $percentField = $totalFieldWajib > 0
                ? round(($terisiFieldWajib / $totalFieldWajib) * 100, 1)
                : 100.0; // Jika tidak ada field wajib, anggap 100%

            // --- Hitung dokumen wajib ---
            $syaratWajib = $this->syaratRepo->all([
                'jalur_pendaftaran_id' => $jalurId,
                'wajib'                => true,
            ]);

            $totalDokumenWajib = $syaratWajib->count();
            $uploadedDokumenWajib = 0;

            if ($totalDokumenWajib > 0) {
                $uploadedDokumen = $this->dokumenRepo->findByPendaftaranId($pendaftaranId)
                    ->pluck('syarat_pendaftaran_id')
                    ->toArray();

                foreach ($syaratWajib as $syarat) {
                    if (in_array($syarat->id, $uploadedDokumen)) {
                        $uploadedDokumenWajib++;
                    }
                }
            }

            $percentDokumen = $totalDokumenWajib > 0
                ? round(($uploadedDokumenWajib / $totalDokumenWajib) * 100, 1)
                : 100.0; // Jika tidak ada dokumen wajib, anggap 100%

            // Siap submit jika semua field wajib terisi DAN semua dokumen wajib ada
            $siapSubmit = ($terisiFieldWajib >= $totalFieldWajib)
                && ($uploadedDokumenWajib >= $totalDokumenWajib);

            return [
                'field_wajib' => [
                    'total'   => $totalFieldWajib,
                    'terisi'  => $terisiFieldWajib,
                    'percent' => $percentField,
                ],
                'dokumen_wajib' => [
                    'total'    => $totalDokumenWajib,
                    'uploaded' => $uploadedDokumenWajib,
                    'percent'  => $percentDokumen,
                ],
                'siap_submit' => $siapSubmit,
            ];
        } catch (\Throwable $e) {
            // Method ini TIDAK BOLEH throw — catat error ke log dan return default
            Log::warning('[PendaftaranService::getProgressLengkapan] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
            ]);

            return $defaultResult;
        }
    }

    // =========================================================================
    // 9. GET ADMIN VERIFIKASI VIEW — Data terformat untuk halaman verifikasi admin
    // =========================================================================

    /**
     * Mengambil dan memformat seluruh data pendaftaran untuk halaman verifikasi admin.
     *
     * Return berisi data yang siap di-render di Blade, terorganisir per section:
     *  - section_biodata     : data identitas peserta lengkap
     *  - section_field_values: field formulir dengan label + nilai ter-render
     *  - section_dokumen     : dokumen dengan link preview langsung bisa dibuka
     *  - section_metadata    : info pendaftaran (no, status, jalur, tanggal, dll)
     *  - progress            : progress kelengkapan (sama dengan getProgressLengkapan)
     *
     * @param  int   $id  ID pendaftaran
     * @return array      ['success', 'message', 'data' => array terformat|null]
     */
    public function getAdminVerifikasiView(int $id): array
    {
        try {
            $pendaftaran = $this->pendaftaranRepo->findById($id, [
                'peserta',
                'peserta.alamat',
                'peserta.orangTua',
                'peserta.kontak',
                'peserta.periodik',
                'peserta.dokumenPribadi',
                'jalurPendaftaran',
                'tahunPelajaran',
                'verifier',
            ]);

            if (!$pendaftaran) {
                return $this->notFound('Pendaftaran', $id);
            }

            $peserta = $pendaftaran->peserta;

            // --- Section: Field Values yang dirender dengan label ---
            $fieldValues       = $this->fieldValueRepo->findByPendaftaranId($id);
            $fieldValuesFormatted = $fieldValues->map(fn($fv) => [
                'formulir_field_id' => $fv->formulir_field_id,
                'kode_field'        => $fv->formulirField?->kode_field,
                'label'             => $fv->formulirField?->label ?? 'Field #' . $fv->formulir_field_id,
                'tipe_field'        => $fv->formulirField?->tipe_field,
                'is_required'       => $fv->formulirField?->is_required ?? false,
                'value'             => $fv->value,
                'value_display'     => $this->renderFieldValue($fv->formulirField, $fv->value),
            ])->values();

            // --- Section: Dokumen dengan preview link ---
            $dokumen          = $this->dokumenRepo->findByPendaftaranId($id);
            $dokumenFormatted = $dokumen->map(fn($d) => [
                'id'                  => $d->id,
                'syarat_id'           => $d->syarat_pendaftaran_id,
                'nama_syarat'         => $d->syaratPendaftaran?->nama,
                'wajib'               => $d->syaratPendaftaran?->wajib ?? false,
                'tipe_syarat'         => $d->syaratPendaftaran?->tipe,
                'nama_file'           => $d->nama_file,
                'url_preview'         => $d->url_file,
                'mime_type'           => $d->mime_type,
                'ukuran_file_kb'      => number_format($d->ukuran_file / 1024, 1),
                'status_verifikasi'   => $d->status_verifikasi,
                'keterangan'          => $d->keterangan_verifikasi,
                'uploaded_at'         => $d->created_at?->format('d/m/Y H:i'),
                'is_image'            => str_starts_with($d->mime_type ?? '', 'image/'),
                'is_pdf'              => $d->mime_type === 'application/pdf',
            ])->values();

            // --- Section: Biodata Peserta ---
            $biodata = [
                'nama_lengkap'    => $peserta?->nama_lengkap,
                'nisn'            => $peserta?->nisn,
                'nik'             => $peserta?->nik,
                'jenis_kelamin'   => $peserta?->jenis_kelamin,
                'tempat_lahir'    => $peserta?->tempat_lahir,
                'tanggal_lahir'   => $peserta?->tanggal_lahir?->format('d/m/Y'),
                'agama'           => $peserta?->agama,
                'no_kk'           => $peserta?->no_kk,
                'foto_url'        => $peserta?->foto ? Storage::url($peserta->foto) : null,
                // Alamat
                'alamat'          => $peserta?->alamat?->alamat,
                'rt_rw'           => ($peserta?->alamat?->rt ?? '-') . '/' . ($peserta?->alamat?->rw ?? '-'),
                'desa_kelurahan'  => $peserta?->alamat?->desa_kelurahan,
                'kecamatan'       => $peserta?->alamat?->kecamatan,
                'kabupaten_kota'  => $peserta?->alamat?->kabupaten_kota,
                'provinsi'        => $peserta?->alamat?->provinsi,
                // Orang tua
                'orang_tua'       => [
                    'ayah' => $peserta?->orangTua?->firstWhere('tipe', 'ayah'),
                    'ibu'  => $peserta?->orangTua?->firstWhere('tipe', 'ibu'),
                    'wali' => $peserta?->orangTua?->firstWhere('tipe', 'wali'),
                ],
                // Kontak
                'no_hp'           => $peserta?->kontak?->no_hp,
                'email'           => $peserta?->kontak?->email,
            ];

            // --- Section: Metadata Pendaftaran ---
            $metadata = [
                'id'               => $pendaftaran->id,
                'no_pendaftaran'   => $pendaftaran->no_pendaftaran,
                'status'           => $pendaftaran->status,
                'tanggal_daftar'   => $pendaftaran->tanggal_daftar?->format('d/m/Y H:i'),
                'jalur'            => $pendaftaran->jalurPendaftaran?->nama,
                'tahun_pelajaran'  => $pendaftaran->tahunPelajaran?->nama,
                'catatan_verifikasi' => $pendaftaran->catatan_verifikasi,
                'verifier'         => $pendaftaran->verifier ? [
                    'nama'        => $pendaftaran->verifier->name,
                    'verified_at' => $pendaftaran->verified_at?->format('d/m/Y H:i'),
                ] : null,
                'transisi_tersedia' => $this->getAvailableTransitions($pendaftaran->status),
            ];

            // Progress
            $progress = $this->getProgressLengkapan($id);

            return [
                'success' => true,
                'message' => 'Data verifikasi berhasil diambil.',
                'data'    => [
                    'section_metadata'     => $metadata,
                    'section_biodata'      => $biodata,
                    'section_field_values' => $fieldValuesFormatted,
                    'section_dokumen'      => $dokumenFormatted,
                    'progress'             => $progress,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[PendaftaranService::getAdminVerifikasiView] ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data verifikasi.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Validasi transisi status menggunakan state machine.
     *
     * @param  string $statusSaatIni  Status pendaftaran saat ini
     * @param  string $statusTujuan   Status yang ingin dituju
     * @return bool
     */
    private function canTransition(string $statusSaatIni, string $statusTujuan): bool
    {
        $allowedNext = self::VALID_TRANSITIONS[$statusSaatIni] ?? [];
        return in_array($statusTujuan, $allowedNext);
    }

    /**
     * Ambil daftar transisi status yang tersedia dari status saat ini.
     *
     * @param  string $statusSaatIni
     * @return string[]
     */
    private function getAvailableTransitions(string $statusSaatIni): array
    {
        return self::VALID_TRANSITIONS[$statusSaatIni] ?? [];
    }

    /**
     * Kumpulkan error kelengkapan form sebelum submit.
     * Return array kosong jika semua lengkap.
     *
     * @param  Pendaftaran $pendaftaran  Model pendaftaran dengan relasi jalurPendaftaran
     * @return array                    Array of ['type', 'id', 'label', 'message']
     */
    private function getValidationErrors(Pendaftaran $pendaftaran): array
    {
        $errors = [];
        $jalurId = $pendaftaran->jalur_pendaftaran_id;

        // --- Cek field wajib ---
        $formulir = $this->formulirRepo->all(['jalur_pendaftaran_id' => $jalurId])->first();
        if ($formulir) {
            $formulirDenganField = $this->formulirRepo->findWithFields($formulir->id);
            $fieldWajib = collect($formulirDenganField?->formulirField ?? [])
                ->filter(fn($f) => $f->is_required);

            $fieldValues = $this->fieldValueRepo->findByPendaftaranId($pendaftaran->id)
                ->keyBy('formulir_field_id');

            foreach ($fieldWajib as $field) {
                $fv = $fieldValues->get($field->id);
                if (!$fv || is_null($fv->value) || trim((string) $fv->value) === '') {
                    $errors[] = [
                        'type'    => 'field',
                        'id'      => $field->id,
                        'label'   => $field->label,
                        'message' => "Field '{$field->label}' wajib diisi.",
                    ];
                }
            }
        }

        // --- Cek dokumen wajib ---
        $syaratWajib = $this->syaratRepo->all([
            'jalur_pendaftaran_id' => $jalurId,
            'wajib'                => true,
        ]);

        $uploadedDokumenIds = $this->dokumenRepo->findByPendaftaranId($pendaftaran->id)
            ->pluck('syarat_pendaftaran_id')
            ->toArray();

        foreach ($syaratWajib as $syarat) {
            if (!in_array($syarat->id, $uploadedDokumenIds)) {
                $errors[] = [
                    'type'    => 'dokumen',
                    'id'      => $syarat->id,
                    'label'   => $syarat->nama,
                    'message' => "Dokumen '{$syarat->nama}' wajib diupload.",
                ];
            }
        }

        return $errors;
    }

    /**
     * Generate nomor pendaftaran unik dengan format: PPDB{tahun}{5digit}
     * Contoh: PPDB202600001
     *
     * Menggunakan SELECT FOR UPDATE (via lockForUpdate) untuk menghindari race condition
     * pada concurrent requests.
     *
     * @param  int    $tahunPelajaranId  ID tahun pelajaran
     * @return string                   Nomor pendaftaran unik
     */
    private function generateNoPendaftaranUnik(int $tahunPelajaranId): string
    {
        // Gunakan loop + cek unik untuk menghindari race condition
        $maxAttempts = 10;
        $attempt     = 0;

        do {
            $no = $this->pendaftaranRepo->generateNoPendaftaran($tahunPelajaranId);
            $existing = $this->pendaftaranRepo->findByNoPendaftaran($no);
            $attempt++;
        } while ($existing && $attempt < $maxAttempts);

        if ($existing) {
            // Fallback: tambahkan suffix timestamp jika masih collision
            $no .= now()->format('His');
        }

        return $no;
    }

    /**
     * Mendapatkan daftar MIME type yang diizinkan berdasarkan tipe syarat.
     *
     * Tipe syarat yang dikenali:
     *  - 'pdf'   : hanya PDF
     *  - 'image' : JPG, JPEG, PNG, WebP
     *  - 'foto'  : JPG, JPEG, PNG (tanpa WebP untuk konsistensi foto formal)
     *  - 'all'   / lainnya: semua tipe di atas
     *
     * @param  string|null $tipeSyarat
     * @return string[]
     */
    private function getAllowedMimesByTipeSyarat(?string $tipeSyarat): array
    {
        return match ($tipeSyarat) {
            'pdf'   => ['application/pdf'],
            'image' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
            'foto'  => ['image/jpeg', 'image/jpg', 'image/png'],
            default => ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
        };
    }

    /**
     * Mendapatkan daftar ekstensi file berdasarkan tipe syarat (untuk pesan error user-friendly).
     *
     * @param  string|null $tipeSyarat
     * @return string[]
     */
    private function getExtensionsByTipe(?string $tipeSyarat): array
    {
        return match ($tipeSyarat) {
            'pdf'   => ['pdf'],
            'image' => ['jpg', 'jpeg', 'png', 'webp'],
            'foto'  => ['jpg', 'jpeg', 'png'],
            default => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
        };
    }

    /**
     * Render nilai field formulir menjadi format yang user-friendly untuk tampilan.
     *
     * @param  FormulirField|null $field  Model field (bisa null)
     * @param  mixed              $value  Nilai raw dari database
     * @return string                     Nilai yang sudah diformat
     */
    private function renderFieldValue(?FormulirField $field, mixed $value): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        if (!$field) {
            return (string) $value;
        }

        return match ($field->tipe_field) {
            'date'     => \Carbon\Carbon::parse($value)->format('d/m/Y'),
            'select'   => $this->resolveSelectLabel($field, $value),
            'checkbox' => $value ? 'Ya' : 'Tidak',
            'file'     => $value ? '[File Terupload]' : '-',
            default    => (string) $value,
        };
    }

    /**
     * Resolve label display untuk field bertipe select dari opsi yang terdaftar.
     *
     * @param  FormulirField $field  Model field dengan kolom opsi (JSON)
     * @param  mixed         $value  Value yang dipilih
     * @return string                Label opsi atau value asli jika tidak ditemukan
     */
    private function resolveSelectLabel(FormulirField $field, mixed $value): string
    {
        $opsi = $field->opsi ?? []; // opsi sudah di-cast sebagai array di model
        foreach ($opsi as $opt) {
            // Format opsi bisa berupa array ['value' => 'x', 'label' => 'y']
            // atau string sederhana
            if (is_array($opt) && ($opt['value'] ?? null) == $value) {
                return $opt['label'] ?? (string) $value;
            }
            if ($opt === $value) {
                return (string) $value;
            }
        }
        return (string) $value;
    }

    /**
     * Helper untuk return standar "record tidak ditemukan".
     *
     * @param  string $entity  Nama entitas (misal: 'Pendaftaran')
     * @param  int    $id      ID yang dicari
     * @return array
     */
    private function notFound(string $entity, int $id): array
    {
        return [
            'success' => false,
            'message' => "{$entity} dengan ID {$id} tidak ditemukan.",
            'data'    => null,
        ];
    }
}
