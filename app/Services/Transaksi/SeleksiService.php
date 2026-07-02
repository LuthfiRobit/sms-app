<?php

namespace App\Services\Transaksi;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Transaksi\HasilSeleksiRepositoryInterface;

use App\Models\Transaksi\Pendaftaran;
use App\Models\Transaksi\Seleksi;
use App\Models\Transaksi\HasilSeleksi;
use App\Models\Ppdb\JalurPendaftaran;
use App\Models\Ppdb\TemplateDokumen;

use App\Services\NotifikasiService;
use App\Services\LogActivityService;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * SeleksiService
 *
 * Orkestrator kalkulasi nilai, ranking otomatis, dan pengumuman hasil seleksi PPDB.
 *
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  SCORING PIPELINE                                                       │
 * │                                                                         │
 * │  1. inputNilai()   — Reviewer input nilai per model penilaian → upsert  │
 * │  2. hitungRanking() — Kalkulasi total_nilai & peringkat per jalur        │
 * │  3. pengumuman()   — Set waktu_pengumuman, update status, kirim notif   │
 * │  4. generatePdf*() — Generate dokumen PDF pengumuman & kartu peserta    │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Formula ranking:
 *   total_nilai = SUM(nilai[i] × bobot[i])   untuk tiap model_penilaian
 *   Urutan      = total_nilai DESC, tanggal_daftar ASC  (tie-breaking)
 *
 * Status kelulusan berdasarkan kuota jalur:
 *   peringkat <= kuota                          → 'lulus'
 *   peringkat > kuota + 5                       → 'tidak_lulus'
 *   kuota < peringkat <= kuota + 5              → 'cadangan'
 *
 * Idempotency:
 *   hitungRanking() dapat dijalankan berkali-kali tanpa side-effect berbahaya.
 *   Setiap pemanggilan akan me-recalculate dan upsert hasil secara atomik.
 *
 * Dependencies (di-inject via constructor):
 *  - PendaftaranRepositoryInterface
 *  - HasilSeleksiRepositoryInterface
 *  - NotifikasiService
 *  - LogActivityService
 */
class SeleksiService
{
    /**
     * Toleransi validasi total bobot (0.01 = ±1%).
     */
    private const BOBOT_TOLERANCE = 0.01;

    /**
     * Jumlah slot cadangan setelah kuota habis.
     */
    private const SLOT_CADANGAN = 5;

    /**
     * Direktori root untuk menyimpan file PDF generated.
     */
    private const PDF_DISK = 'public';
    private const PDF_DIR_PENGUMUMAN = 'seleksi/pengumuman';
    private const PDF_DIR_KARTU = 'seleksi/kartu';

    // =========================================================================
    // CONSTRUCTOR
    // =========================================================================

    public function __construct(
        protected PendaftaranRepositoryInterface $pendaftaranRepo,
        protected HasilSeleksiRepositoryInterface $hasilSeleksiRepo,
        protected NotifikasiService $notifikasiService,
        protected LogActivityService $logActivity,
    ) {
    }

    // =========================================================================
    // 1. INDEX — List semua pendaftaran di jalur dengan nilai & peringkat
    // =========================================================================

    /**
     * Mengambil daftar semua pendaftaran di jalur tertentu lengkap dengan
     * nilai per model penilaian dan peringkat terkini.
     *
     * Return format per item:
     * [
     *   'pendaftaran_id'  => int,
     *   'no_pendaftaran'  => string,
     *   'nama_peserta'    => string,
     *   'nisn'            => string,
     *   'tanggal_daftar'  => string,
     *   'status'          => string,
     *   'nilai'           => [ ['model_penilaian', 'nilai', 'bobot', 'reviewer_name'] ],
     *   'total_nilai'     => float|null,
     *   'peringkat'       => int|null,
     *   'status_kelulusan'=> string|null,
     * ]
     *
     * @param  int   $jalurId  ID jalur_pendaftaran
     * @return array           ['success', 'message', 'data' => array]
     */
    public function index(int $jalurId): array
    {
        try {
            $jalur = JalurPendaftaran::find($jalurId);
            if (!$jalur) {
                return $this->notFound('Jalur Pendaftaran', $jalurId);
            }

            // Ambil semua pendaftaran yang sudah diverifikasi di jalur ini
            $pendaftaranList = Pendaftaran::with([
                'peserta',
                'seleksi.reviewer',
                'hasilSeleksi',
            ])
                ->where('jalur_pendaftaran_id', $jalurId)
                ->whereIn('status', [
                    Pendaftaran::STATUS_VERIFIKASI,
                    Pendaftaran::STATUS_LULUS,
                    Pendaftaran::STATUS_TIDAK_LULUS,
                    Pendaftaran::STATUS_DAFTAR_ULANG,
                    Pendaftaran::STATUS_SISWA_TETAP,
                ])
                ->orderBy('tanggal_daftar')
                ->get();

            $data = $pendaftaranList->map(function (Pendaftaran $p) {
                $nilaiList = $p->seleksi->map(fn(Seleksi $s) => [
                    'model_penilaian' => $s->model_penilaian,
                    'nilai' => (float) $s->nilai,
                    'bobot' => (float) $s->bobot,
                    'reviewer_name' => $s->reviewer?->name ?? '-',
                    'waktu_nilai' => $s->waktu_nilai?->format('d/m/Y H:i'),
                ])->values()->toArray();

                return [
                    'pendaftaran_id' => $p->id,
                    'no_pendaftaran' => $p->no_pendaftaran,
                    'nama_peserta' => $p->peserta?->nama_lengkap ?? '-',
                    'nisn' => $p->peserta?->nisn ?? '-',
                    'tanggal_daftar' => $p->tanggal_daftar?->format('d/m/Y H:i'),
                    'status' => $p->status,
                    'nilai' => $nilaiList,
                    'total_nilai' => $p->hasilSeleksi ? (float) $p->hasilSeleksi->total_nilai : null,
                    'peringkat' => $p->hasilSeleksi?->peringkat,
                    'status_kelulusan' => $p->hasilSeleksi?->status_kelulusan,
                ];
            })->sortBy('peringkat')->values()->toArray();

            return [
                'success' => true,
                'message' => "Berhasil mengambil data seleksi jalur '{$jalur->nama}'.",
                'data' => [
                    'jalur' => $jalur,
                    'kuota' => $jalur->kuota,
                    'total_peserta' => count($data),
                    'pendaftaran' => $data,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[SeleksiService::index] ' . $e->getMessage(), [
                'jalur_id' => $jalurId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil data seleksi: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    // =========================================================================
    // 2. INPUT NILAI — Reviewer menginput nilai per model penilaian
    // =========================================================================

    /**
     * Menyimpan atau memperbarui nilai seleksi per model penilaian untuk
     * satu pendaftaran. Setelah upsert, total_nilai di hasil_seleksi
     * dihitung ulang secara otomatis.
     *
     * Format $nilaiData yang diharapkan:
     * [
     *   ['model_penilaian' => 'tes_tulis',   'nilai' => 85.5, 'bobot' => 0.6],
     *   ['model_penilaian' => 'wawancara',   'nilai' => 90.0, 'bobot' => 0.3],
     *   ['model_penilaian' => 'portofolio',  'nilai' => 75.0, 'bobot' => 0.1],
     * ]
     *
     * Validasi:
     *  - Setiap nilai harus 0–100
     *  - Setiap bobot harus 0.0–1.0
     *  - SUM(bobot) harus = 1.0 (toleransi ±0.01)
     *  - Pendaftaran harus berstatus 'verifikasi' (sudah diverifikasi admin)
     *
     * @param  int   $pendaftaranId  ID pendaftaran
     * @param  array $nilaiData      Array data nilai per model
     * @param  int   $reviewerId     ID user reviewer
     * @return array                 ['success', 'message', 'data' => array]
     */
    public function inputNilai(int $pendaftaranId, array $nilaiData, int $reviewerId): array
    {
        // --- Ambil pendaftaran ---
        $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId, [
            'peserta',
            'jalurPendaftaran',
        ]);

        if (!$pendaftaran) {
            return $this->notFound('Pendaftaran', $pendaftaranId);
        }

        // Guard: hanya bisa input nilai saat status verifikasi
        if ($pendaftaran->status !== Pendaftaran::STATUS_VERIFIKASI) {
            return [
                'success' => false,
                'message' => "Input nilai hanya dapat dilakukan saat pendaftaran berstatus 'verifikasi'. " .
                    "Status saat ini: '{$pendaftaran->status}'.",
                'data' => null,
            ];
        }

        // --- Validasi nilaiData tidak boleh kosong ---
        if (empty($nilaiData)) {
            return [
                'success' => false,
                'message' => 'Data nilai tidak boleh kosong.',
                'data' => null,
            ];
        }

        // --- Validasi tiap item ---
        $errors = [];
        $totalBobot = 0.0;

        foreach ($nilaiData as $index => $item) {
            $model = $item['model_penilaian'] ?? null;
            $nilai = isset($item['nilai']) ? (float) $item['nilai'] : null;
            $bobot = isset($item['bobot']) ? (float) $item['bobot'] : null;

            if (empty($model)) {
                $errors[] = "Item ke-{$index}: 'model_penilaian' wajib diisi.";
            }

            if ($nilai === null || $nilai < 0 || $nilai > 100) {
                $errors[] = "Item ke-{$index} ({$model}): 'nilai' harus antara 0–100.";
            }

            if ($bobot === null || $bobot < 0.0 || $bobot > 1.0) {
                $errors[] = "Item ke-{$index} ({$model}): 'bobot' harus antara 0.0–1.0.";
            } else {
                $totalBobot += $bobot;
            }
        }

        // Validasi total bobot = 1.0 (toleransi 0.01)
        if (empty($errors) && abs($totalBobot - 1.0) > self::BOBOT_TOLERANCE) {
            $errors[] = sprintf(
                'Total bobot semua model penilaian harus = 1.0 (toleransi ±%.2f). Saat ini = %.4f.',
                self::BOBOT_TOLERANCE,
                $totalBobot
            );
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validasi gagal.',
                'data' => null,
                'errors' => $errors,
            ];
        }

        // --- Upsert ke tabel seleksi & hitung ulang total_nilai ---
        try {
            DB::transaction(function () use ($pendaftaranId, $nilaiData, $reviewerId, $pendaftaran) {
                foreach ($nilaiData as $item) {
                    Seleksi::updateOrCreate(
                        [
                            'pendaftaran_id' => $pendaftaranId,
                            'model_penilaian' => $item['model_penilaian'],
                        ],
                        [
                            'reviewer_id' => $reviewerId,
                            'nilai' => (float) $item['nilai'],
                            'bobot' => (float) $item['bobot'],
                            'keterangan' => $item['keterangan'] ?? null,
                            'waktu_nilai' => now(),
                        ]
                    );
                }

                // Hitung ulang total_nilai setelah semua nilai masuk
                $this->recalculateTotalNilai($pendaftaranId, $reviewerId);
            });

            $this->logActivity->log(
                'Input Nilai Seleksi',
                "Nilai seleksi diinput untuk pendaftaran #{$pendaftaran->no_pendaftaran} " .
                "oleh reviewer ID: {$reviewerId}. Model: " .
                implode(', ', array_column($nilaiData, 'model_penilaian'))
            );

            // Ambil data hasil setelah update
            $hasilSeleksi = HasilSeleksi::where('pendaftaran_id', $pendaftaranId)->first();
            $seleksiList = Seleksi::where('pendaftaran_id', $pendaftaranId)
                ->with('reviewer')
                ->get()
                ->map(fn(Seleksi $s) => [
                    'model_penilaian' => $s->model_penilaian,
                    'nilai' => (float) $s->nilai,
                    'bobot' => (float) $s->bobot,
                    'kontribusi' => round((float) $s->nilai * (float) $s->bobot, 4),
                    'reviewer_name' => $s->reviewer?->name ?? '-',
                    'waktu_nilai' => $s->waktu_nilai?->format('d/m/Y H:i'),
                ]);

            return [
                'success' => true,
                'message' => "Nilai seleksi untuk pendaftaran #{$pendaftaran->no_pendaftaran} berhasil disimpan.",
                'data' => [
                    'pendaftaran_id' => $pendaftaranId,
                    'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                    'nama_peserta' => $pendaftaran->peserta?->nama_lengkap,
                    'nilai_detail' => $seleksiList,
                    'total_nilai' => $hasilSeleksi ? (float) $hasilSeleksi->total_nilai : null,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[SeleksiService::inputNilai] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'reviewer_id' => $reviewerId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menyimpan nilai seleksi: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    // =========================================================================
    // 3. HITUNG RANKING — Kalkulasi peringkat seluruh peserta di jalur
    // =========================================================================

    /**
     * Menghitung dan memperbarui peringkat semua peserta di jalur tertentu.
     *
     * Algoritma:
     *  1. Ambil semua pendaftaran di jalur yang sudah diverifikasi + punya nilai
     *  2. Hitung total_nilai = SUM(nilai[i] × bobot[i]) per pendaftaran
     *  3. Sort: total_nilai DESC, tanggal_daftar ASC (tie-breaking FIFO)
     *  4. Tentukan peringkat (1-based)
     *  5. Tentukan status_kelulusan berdasarkan kuota:
     *       peringkat <= kuota         → 'lulus'
     *       kuota < peringkat <= kuota+5 → 'cadangan'
     *       peringkat > kuota + 5      → 'tidak_lulus'
     *  6. Upsert semua hasil_seleksi dalam satu DB transaction
     *
     * Method ini IDEMPOTENT — aman dipanggil berkali-kali.
     *
     * @param  int   $jalurId  ID jalur_pendaftaran
     * @param  int   $userId   ID user yang menjalankan perhitungan
     * @return array           ['success', 'message', 'data' => ranking list]
     */
    public function hitungRanking(int $jalurId, int $userId): array
    {
        try {
            $jalur = JalurPendaftaran::find($jalurId);
            if (!$jalur) {
                return $this->notFound('Jalur Pendaftaran', $jalurId);
            }

            $kuotaPersen = (int) $jalur->kuota; // nilai di DB adalah persen (0-100)

            // Ambil semua pendaftaran yang sudah diverifikasi di jalur ini
            $pendaftaranList = Pendaftaran::with(['seleksi', 'hasilSeleksi', 'peserta'])
                ->where('jalur_pendaftaran_id', $jalurId)
                ->whereIn('status', [
                    Pendaftaran::STATUS_VERIFIKASI,
                    Pendaftaran::STATUS_LULUS,
                    Pendaftaran::STATUS_TIDAK_LULUS,
                ])
                ->orderBy('tanggal_daftar') // tie-breaking awal: FIFO
                ->get();

            if ($pendaftaranList->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Tidak ada pendaftaran terverifikasi di jalur ini untuk diperingkat.',
                    'data' => [],
                ];
            }

            // Hitung kuota absolut dari persen
            // Jika kuota <= 100 kita asumsikan nilai tsb adalah persen
            // Kuota absolut = ceil(persen/100 * total_peserta), minimum 1
            $totalPeserta = $pendaftaranList->count();
            $kuota = $kuotaPersen > 100
                ? $kuotaPersen  // Sudah berupa angka absolut
                : (int) max(1, ceil($kuotaPersen / 100 * $totalPeserta));

            Log::info('[SeleksiService::hitungRanking] Kalkulasi kuota', [
                'jalur_id' => $jalurId,
                'kuota_persen' => $kuotaPersen,
                'total_peserta' => $totalPeserta,
                'kuota_absolut' => $kuota,
            ]);

            // Filter hanya yang sudah punya nilai seleksi
            $denganNilai = $pendaftaranList->filter(
                fn(Pendaftaran $p) => $p->seleksi->isNotEmpty()
            );

            if ($denganNilai->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Belum ada pendaftaran yang memiliki nilai seleksi di jalur ini.',
                    'data' => null,
                ];
            }

            // Hitung total_nilai untuk setiap pendaftaran
            $denganTotalNilai = $denganNilai->map(function (Pendaftaran $p) {
                $totalNilai = $p->seleksi->sum(
                    fn(Seleksi $s) => (float) $s->nilai * (float) $s->bobot
                );

                return [
                    'pendaftaran' => $p,
                    'total_nilai' => round($totalNilai, 4),
                    'tanggal_daftar' => $p->tanggal_daftar,
                ];
            });

            // Sort: total_nilai DESC, tanggal_daftar ASC (tie-breaking FIFO)
            $sorted = $denganTotalNilai
                ->sort(function ($a, $b) {
                    $nilaiCmp = $b['total_nilai'] <=> $a['total_nilai']; // DESC
                    if ($nilaiCmp !== 0) {
                        return $nilaiCmp;
                    }
                    return $a['tanggal_daftar'] <=> $b['tanggal_daftar']; // ASC
                })
                ->values();

            // Upsert hasil_seleksi dalam satu transaksi
            $rankingResult = [];

            DB::transaction(function () use ($sorted, $kuota, $userId, &$rankingResult) {
                foreach ($sorted as $rank => $item) {
                    /** @var Pendaftaran $pendaftaran */
                    $pendaftaran = $item['pendaftaran'];
                    $peringkat = $rank + 1; // 1-based
                    $totalNilai = $item['total_nilai'];

                    // Tentukan status_kelulusan
                    $statusKelulusan = $this->tentikanStatusKelulusan($peringkat, $kuota);

                    // Upsert hasil_seleksi
                    HasilSeleksi::updateOrCreate(
                        ['pendaftaran_id' => $pendaftaran->id],
                        [
                            'total_nilai' => $totalNilai,
                            'peringkat' => $peringkat,
                            'status_kelulusan' => $statusKelulusan,
                            'reviewer_id' => $userId,
                        ]
                    );

                    $rankingResult[] = [
                        'peringkat' => $peringkat,
                        'pendaftaran_id' => $pendaftaran->id,
                        'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                        'nama_peserta' => $pendaftaran->peserta?->nama_lengkap ?? '-',
                        'nisn' => $pendaftaran->peserta?->nisn ?? '-',
                        'tanggal_daftar' => $pendaftaran->tanggal_daftar?->format('d/m/Y H:i'),
                        'total_nilai' => $totalNilai,
                        'status_kelulusan' => $statusKelulusan,
                    ];
                }
            });

            $this->logActivity->log(
                'Hitung Ranking Seleksi',
                "Ranking dihitung untuk jalur ID: {$jalurId} oleh user ID: {$userId}. " .
                "Total peserta diperingkat: " . count($rankingResult)
            );

            return [
                'success' => true,
                'message' => 'Ranking berhasil dihitung. Total ' . count($rankingResult) . ' peserta diperingkat.',
                'data' => [
                    'jalur_id' => $jalurId,
                    'nama_jalur' => $jalur->nama,
                    'kuota' => $kuota,
                    'total_diperingkat' => count($rankingResult),
                    'ranking' => $rankingResult,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[SeleksiService::hitungRanking] ' . $e->getMessage(), [
                'jalur_id' => $jalurId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghitung ranking: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    // =========================================================================
    // 4. PENGUMUMAN — Publikasi hasil seleksi & kirim notifikasi
    // =========================================================================

    /**
     * Mempublikasikan hasil seleksi jalur tertentu.
     *
     * Prasyarat:
     *  - Semua pendaftaran terverifikasi harus sudah memiliki nilai seleksi
     *  - Peringkat harus sudah dihitung (hasil_seleksi tidak boleh kosong)
     *
     * Yang dilakukan:
     *  1. Validasi prasyarat di atas
     *  2. Set waktu_pengumuman = now() untuk semua hasil_seleksi di jalur
     *  3. Update status pendaftaran: lulus → 'lulus', lainnya → 'tidak_lulus'
     *     (cadangan tetap pada status 'verifikasi' sampai keputusan final)
     *  4. Kirim notifikasi ke setiap peserta (in-app + log untuk email)
     *  5. Log aktivitas
     *
     * @param  int   $jalurId  ID jalur_pendaftaran
     * @param  int   $userId   ID user admin yang melakukan pengumuman
     * @return array           ['success', 'message', 'data' => summary]
     */
    public function pengumuman(int $jalurId, int $userId): array
    {
        try {
            $jalur = JalurPendaftaran::find($jalurId);
            if (!$jalur) {
                return $this->notFound('Jalur Pendaftaran', $jalurId);
            }

            // Ambil pendaftaran terverifikasi
            $pendaftaranList = Pendaftaran::with([
                'peserta',
                'peserta.kontak',
                'seleksi',
                'hasilSeleksi',
            ])
                ->where('jalur_pendaftaran_id', $jalurId)
                ->whereIn('status', [
                    Pendaftaran::STATUS_VERIFIKASI,
                    Pendaftaran::STATUS_LULUS,
                    Pendaftaran::STATUS_TIDAK_LULUS,
                ])
                ->get();

            if ($pendaftaranList->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada peserta terverifikasi di jalur ini untuk diumumkan.',
                    'data' => null,
                ];
            }

            // Validasi 1: semua peserta harus sudah punya nilai
            $belumNilai = $pendaftaranList->filter(
                fn(Pendaftaran $p) => $p->seleksi->isEmpty()
            );

            if ($belumNilai->isNotEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Pengumuman belum dapat dilakukan. Ada ' . $belumNilai->count() .
                        ' peserta yang belum dinilai.',
                    'data' => [
                        'belum_dinilai' => $belumNilai->map(fn($p) => [
                            'pendaftaran_id' => $p->id,
                            'no_pendaftaran' => $p->no_pendaftaran,
                            'nama_peserta' => $p->peserta?->nama_lengkap,
                        ])->values(),
                    ],
                ];
            }

            // Validasi 2: semua peserta harus sudah punya hasil_seleksi (ranking sudah dihitung)
            $belumRanking = $pendaftaranList->filter(
                fn(Pendaftaran $p) => !$p->hasilSeleksi
            );

            if ($belumRanking->isNotEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Ranking belum dihitung untuk semua peserta. Jalankan hitungRanking() terlebih dahulu.',
                    'data' => null,
                ];
            }

            // --- Publikasi dalam satu transaksi atomik ---
            $summary       = [];
            $notifications = []; // Dikumpulkan dalam transaksi, dikirim setelah commit

            DB::transaction(function () use ($pendaftaranList, $jalur, $userId, &$summary, &$notifications) {
                $waktuPengumuman = now();

                foreach ($pendaftaranList as $pendaftaran) {
                    $hasil = $pendaftaran->hasilSeleksi;
                    $statusKelulusan = $hasil->status_kelulusan;

                    // Update waktu_pengumuman di hasil_seleksi
                    $hasil->update(['waktu_pengumuman' => $waktuPengumuman]);

                    // Update status pendaftaran
                    $statusBaru = match ($statusKelulusan) {
                        HasilSeleksi::STATUS_LULUS => Pendaftaran::STATUS_LULUS,
                        HasilSeleksi::STATUS_TIDAK_LULUS => Pendaftaran::STATUS_TIDAK_LULUS,
                        default => $pendaftaran->status, // cadangan: tetap verifikasi
                    };

                    if ($statusBaru !== $pendaftaran->status) {
                        $pendaftaran->update(['status' => $statusBaru]);
                    }

                    // Kumpulkan data notifikasi untuk dikirim setelah transaction commit
                    $userId_peserta = $pendaftaran->peserta?->user_id;
                    if ($userId_peserta) {
                        $event = match ($statusKelulusan) {
                            HasilSeleksi::STATUS_LULUS => 'pengumuman_lulus',
                            default                    => 'pengumuman_tidak_lulus',
                        };

                        $notifications[] = [
                            'user_id' => $userId_peserta,
                            'event'   => $event,
                            'data'    => [
                                'nama_peserta'   => $pendaftaran->peserta?->nama_lengkap ?? '-',
                                'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                                'jalur'          => $jalur->nama,
                            ],
                        ];
                    }

                    $summary[] = [
                        'pendaftaran_id' => $pendaftaran->id,
                        'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                        'nama_peserta' => $pendaftaran->peserta?->nama_lengkap,
                        'peringkat' => $hasil->peringkat,
                        'total_nilai' => (float) $hasil->total_nilai,
                        'status_kelulusan' => $statusKelulusan,
                        'status_baru' => $statusBaru,
                        'waktu_pengumuman' => $waktuPengumuman->format('d/m/Y H:i:s'),
                    ];
                }
            });

            // Kirim notifikasi ke setiap peserta setelah transaction berhasil commit
            foreach ($notifications as $notif) {
                $this->notifikasiService->kirim($notif['user_id'], $notif['event'], $notif['data']);
            }

            $this->logActivity->log(
                'Pengumuman Seleksi',
                "Pengumuman hasil seleksi jalur ID: {$jalurId} dipublikasikan oleh user ID: {$userId}. " .
                'Total peserta diumumkan: ' . count($summary)
            );

            $totalLulus = collect($summary)->where('status_kelulusan', HasilSeleksi::STATUS_LULUS)->count();
            $totalCadangan = collect($summary)->where('status_kelulusan', HasilSeleksi::STATUS_CADANGAN)->count();
            $totalTidakLulus = collect($summary)->where('status_kelulusan', HasilSeleksi::STATUS_TIDAK_LULUS)->count();

            return [
                'success' => true,
                'message' => "Pengumuman hasil seleksi jalur '{$jalur->nama}' berhasil dipublikasikan.",
                'data' => [
                    'jalur_id' => $jalurId,
                    'nama_jalur' => $jalur->nama,
                    'total_peserta' => count($summary),
                    'total_lulus' => $totalLulus,
                    'total_cadangan' => $totalCadangan,
                    'total_tidak_lulus' => $totalTidakLulus,
                    'detail' => $summary,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[SeleksiService::pengumuman] ' . $e->getMessage(), [
                'jalur_id' => $jalurId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mempublikasikan pengumuman: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    // =========================================================================
    // 5. GENERATE PDF PENGUMUMAN — PDF daftar peserta lulus
    // =========================================================================

    /**
     * Membuat file PDF pengumuman kelulusan untuk jalur tertentu.
     *
     * Konten PDF:
     *  - Header: nama jalur, kuota, tanggal pengumuman
     *  - Tabel: No | Nama Peserta | NISN | Peringkat | Total Nilai | Status
     *  - Hanya peserta LULUS & CADANGAN yang ditampilkan
     *
     * Template: menggunakan TemplateDokumen (tipe = 'pengumuman') jika ada.
     * Fallback: blade template default (resources/views/pdf/pengumuman.blade.php)
     *
     * @param  int    $jalurId  ID jalur_pendaftaran
     * @return string           Path file PDF (relatif ke disk 'public')
     *
     * @throws Exception jika generate PDF gagal
     */
    public function generatePdfPengumuman(int $jalurId): string
    {
        $jalur = JalurPendaftaran::with(['pembukaanPpdb'])->find($jalurId);
        if (!$jalur) {
            throw new Exception("Jalur Pendaftaran ID: {$jalurId} tidak ditemukan.");
        }

        // Ambil peserta yang lulus dan cadangan, urut peringkat
        $hasilList = HasilSeleksi::with([
            'pendaftaran.peserta',
            'pendaftaran.peserta.kontak',
        ])
            ->whereHas('pendaftaran', fn($q) => $q->where('jalur_pendaftaran_id', $jalurId))
            ->whereIn('status_kelulusan', [
                HasilSeleksi::STATUS_LULUS,
                HasilSeleksi::STATUS_CADANGAN,
            ])
            ->orderBy('peringkat')
            ->get();

        // Data untuk blade
        $data = [
            'jalur' => $jalur,
            'hasil_list' => $hasilList,
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'total_lulus' => $hasilList->where('status_kelulusan', HasilSeleksi::STATUS_LULUS)->count(),
            'total_cadangan' => $hasilList->where('status_kelulusan', HasilSeleksi::STATUS_CADANGAN)->count(),
        ];

        // Generate PDF menggunakan DomPDF
        $pdf = Pdf::loadView('pdf.seleksi.pengumuman', $data)
            ->setPaper('A4', 'portrait')
            ->setOption('defaultFont', 'sans-serif')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', false);

        // Tentukan path output
        $fileName = 'pengumuman_' . $jalurId . '_' . now()->format('Ymd_His') . '.pdf';
        $directory = self::PDF_DIR_PENGUMUMAN . '/' . $jalurId;
        $filePath = $directory . '/' . $fileName;

        // Simpan ke storage
        Storage::disk(self::PDF_DISK)->makeDirectory($directory);
        Storage::disk(self::PDF_DISK)->put($filePath, $pdf->output());

        $this->logActivity->log(
            'Generate PDF Pengumuman',
            "PDF pengumuman jalur ID: {$jalurId} berhasil digenerate → {$filePath}"
        );

        return $filePath;
    }

    // =========================================================================
    // 6. GENERATE KARTU PESERTA — PDF kartu per individual
    // =========================================================================

    /**
     * Membuat kartu peserta individual dalam format PDF.
     *
     * Konten kartu:
     *  - Foto peserta (jika ada)
     *  - Data identitas: nama, NISN, tempat/tanggal lahir
     *  - Nomor pendaftaran
     *  - Jalur & tahun pelajaran
     *  - Status kelulusan & peringkat
     *  - QR Code berisi no_pendaftaran untuk verifikasi
     *
     * QR Code: menggunakan package Simple QR (Bacon/BaconQrCode atau
     *          endroid/qr-code). PDF dirender via DomPDF.
     *
     * @param  int    $pendaftaranId  ID pendaftaran
     * @return string                 Path file PDF (relatif ke disk 'public')
     *
     * @throws Exception jika generate gagal
     */
    public function generateKartuPeserta(int $pendaftaranId): string
    {
        $pendaftaran = Pendaftaran::with([
            'peserta',
            'peserta.kontak',
            'peserta.alamat',
            'jalurPendaftaran',
            'tahunPelajaran',
            'hasilSeleksi',
        ])->find($pendaftaranId);

        if (!$pendaftaran) {
            throw new Exception("Pendaftaran ID: {$pendaftaranId} tidak ditemukan.");
        }

        // Generate QR Code sebagai base64 image
        $qrBase64 = $this->generateQrCodeBase64($pendaftaran->no_pendaftaran);

        // URL foto peserta (fallback ke placeholder jika tidak ada)
        $fotoPath = $pendaftaran->peserta?->foto
            ? Storage::disk(self::PDF_DISK)->path($pendaftaran->peserta->foto)
            : null;

        $fotoBase64 = null;
        if ($fotoPath && file_exists($fotoPath)) {
            $mime = mime_content_type($fotoPath);
            $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
        }

        $data = [
            'pendaftaran' => $pendaftaran,
            'peserta' => $pendaftaran->peserta,
            'jalur' => $pendaftaran->jalurPendaftaran,
            'tahun' => $pendaftaran->tahunPelajaran,
            'hasil' => $pendaftaran->hasilSeleksi,
            'qr_base64' => $qrBase64,
            'foto_base64' => $fotoBase64,
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
        ];

        // Generate PDF kartu (ukuran A5 portrait)
        $pdf = Pdf::loadView('pdf.seleksi.kartu_peserta', $data)
            ->setPaper('a5', 'portrait')
            ->setOption('defaultFont', 'Helvetica')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        // Path output
        $noBersih = preg_replace('/[^a-zA-Z0-9]/', '_', $pendaftaran->no_pendaftaran);
        $fileName = "kartu_{$noBersih}.pdf";
        $directory = self::PDF_DIR_KARTU . '/' . $pendaftaran->jalur_pendaftaran_id;
        $filePath = $directory . '/' . $fileName;

        Storage::disk(self::PDF_DISK)->makeDirectory($directory);
        Storage::disk(self::PDF_DISK)->put($filePath, $pdf->output());

        $this->logActivity->log(
            'Generate Kartu Peserta',
            "Kartu peserta #{$pendaftaran->no_pendaftaran} berhasil digenerate → {$filePath}"
        );

        return $filePath;
    }

    // =========================================================================
    // 7. GET HASIL SELEKSI — Data publik / admin hasil seleksi
    // =========================================================================

    /**
     * Mengambil data hasil seleksi untuk ditampilkan di halaman publik maupun
     * halaman admin. Digroup menjadi: lulus, cadangan, tidak_lulus.
     *
     * @param  int   $jalurId  ID jalur_pendaftaran
     * @return array           ['success', 'message', 'data' => array berisi 3 grup]
     */
    public function getHasilSeleksi(int $jalurId): array
    {
        try {
            $jalur = JalurPendaftaran::with(['pembukaanPpdb'])->find($jalurId);
            if (!$jalur) {
                return $this->notFound('Jalur Pendaftaran', $jalurId);
            }

            $hasilList = HasilSeleksi::with([
                'pendaftaran.peserta',
            ])
                ->whereHas('pendaftaran', fn($q) => $q->where('jalur_pendaftaran_id', $jalurId))
                ->orderBy('peringkat')
                ->get();

            $mapper = fn(HasilSeleksi $h) => [
                'pendaftaran_id' => $h->pendaftaran_id,
                'no_pendaftaran' => $h->pendaftaran?->no_pendaftaran,
                'nama_peserta' => $h->pendaftaran?->peserta?->nama_lengkap ?? '-',
                'nisn' => $h->pendaftaran?->peserta?->nisn ?? '-',
                'peringkat' => $h->peringkat,
                'total_nilai' => (float) $h->total_nilai,
                'status_kelulusan' => $h->status_kelulusan,
                'waktu_pengumuman' => $h->waktu_pengumuman?->format('d/m/Y H:i'),
            ];

            $lulus = $hasilList->where('status_kelulusan', HasilSeleksi::STATUS_LULUS)->map($mapper)->values();
            $cadangan = $hasilList->where('status_kelulusan', HasilSeleksi::STATUS_CADANGAN)->map($mapper)->values();
            $tidakLulus = $hasilList->where('status_kelulusan', HasilSeleksi::STATUS_TIDAK_LULUS)->map($mapper)->values();

            // Cek apakah sudah diumumkan (ada waktu_pengumuman)
            $sudahDiumumkan = $hasilList->whereNotNull('waktu_pengumuman')->isNotEmpty();

            return [
                'success' => true,
                'message' => 'Data hasil seleksi berhasil diambil.',
                'data' => [
                    'jalur' => [
                        'id' => $jalur->id,
                        'nama' => $jalur->nama,
                        'kuota' => $jalur->kuota,
                    ],
                    'sudah_diumumkan' => $sudahDiumumkan,
                    'waktu_pengumuman' => $hasilList->whereNotNull('waktu_pengumuman')
                        ->first()?->waktu_pengumuman?->format('d/m/Y H:i'),
                    'lulus' => $lulus,
                    'cadangan' => $cadangan,
                    'tidak_lulus' => $tidakLulus,
                    'statistik' => [
                        'total_peserta' => $hasilList->count(),
                        'total_lulus' => $lulus->count(),
                        'total_cadangan' => $cadangan->count(),
                        'total_tidak_lulus' => $tidakLulus->count(),
                    ],
                ],
            ];
        } catch (Exception $e) {
            Log::error('[SeleksiService::getHasilSeleksi] ' . $e->getMessage(), [
                'jalur_id' => $jalurId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil hasil seleksi: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Menghitung ulang total_nilai di hasil_seleksi berdasarkan data di tabel seleksi.
     * Dipanggil setelah inputNilai(). Membuat record hasil_seleksi jika belum ada.
     *
     * @param  int  $pendaftaranId
     * @param  int  $reviewerId
     */
    private function recalculateTotalNilai(int $pendaftaranId, int $reviewerId): void
    {
        $seleksiList = Seleksi::where('pendaftaran_id', $pendaftaranId)->get();

        $totalNilai = $seleksiList->sum(
            fn(Seleksi $s) => (float) $s->nilai * (float) $s->bobot
        );

        HasilSeleksi::updateOrCreate(
            ['pendaftaran_id' => $pendaftaranId],
            [
                'total_nilai' => round($totalNilai, 4),
                'reviewer_id' => $reviewerId,
                // peringkat & status_kelulusan diisi saat hitungRanking()
            ]
        );
    }

    /**
     * Menentukan status kelulusan berdasarkan peringkat dan kuota.
     *
     * Aturan:
     *  peringkat <= kuota                    → 'lulus'
     *  kuota < peringkat <= kuota + 5       → 'cadangan'
     *  peringkat > kuota + 5                → 'tidak_lulus'
     */
    private function tentikanStatusKelulusan(int $peringkat, int $kuota): string
    {
        if ($peringkat <= $kuota) {
            return HasilSeleksi::STATUS_LULUS;
        }

        if ($peringkat <= ($kuota + self::SLOT_CADANGAN)) {
            return HasilSeleksi::STATUS_CADANGAN;
        }

        return HasilSeleksi::STATUS_TIDAK_LULUS;
    }

    /**
     * Membangun judul, isi, dan tipe notifikasi berdasarkan hasil seleksi.
     *
     * @return array [judul, isi, tipe]
     */
    private function buildNotifikasiPengumuman(
        Pendaftaran $pendaftaran,
        HasilSeleksi $hasil
    ): array {
        $nama = $pendaftaran->peserta?->nama_lengkap ?? 'Ananda';
        $jalur = $pendaftaran->jalurPendaftaran?->nama ?? 'PPDB';
        $no = $pendaftaran->no_pendaftaran;

        return match ($hasil->status_kelulusan) {
            HasilSeleksi::STATUS_LULUS => [
                "Selamat! Anda LULUS Seleksi {$jalur}",
                "Yth. {$nama}, dengan nomor pendaftaran #{$no}, Anda dinyatakan **LULUS** " .
                "seleksi {$jalur} dan mendapatkan peringkat ke-{$hasil->peringkat}. " .
                "Silakan lakukan daftar ulang sesuai jadwal yang telah ditetapkan.",
                'success',
            ],
            HasilSeleksi::STATUS_CADANGAN => [
                "Pengumuman Seleksi {$jalur} — Status Cadangan",
                "Yth. {$nama}, dengan nomor pendaftaran #{$no}, Anda saat ini berada di posisi " .
                "**CADANGAN** (peringkat ke-{$hasil->peringkat}). " .
                "Tetap pantau informasi lanjutan dari sekolah mengenai keputusan final.",
                'warning',
            ],
            default => [
                "Pengumuman Seleksi {$jalur}",
                "Yth. {$nama}, dengan nomor pendaftaran #{$no}, setelah proses seleksi " .
                "Anda dinyatakan **TIDAK LULUS** seleksi {$jalur}. " .
                "Terima kasih atas partisipasi Anda dalam PPDB ini.",
                'error',
            ],
        };
    }

    /**
     * Generate QR Code.
     * Mencoba PNG via SimpleSoftwareIO (butuh Imagick), lalu fallback ke inline SVG via BaconQrCode.
     * Return value: data URL PNG, inline SVG markup, atau null.
     *
     * @param  string $content  Konten yang di-encode dalam QR
     * @return string|null
     */
    private function generateQrCodeBase64(string $content): ?string
    {
        // Coba PNG via SimpleSoftwareIO (butuh Imagick)
        if (
            class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)
            || class_exists(\SimpleSoftwareIO\QrCode\QrCode::class)
        ) {
            try {
                $qrPng = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                    ->size(200)
                    ->margin(0)
                    ->errorCorrection('M')
                    ->generate($content);

                return 'data:image/png;base64,' . base64_encode($qrPng);
            } catch (Exception $e) {
                Log::info('[SeleksiService::generateQrCodeBase64] PNG gagal (Imagick?), fallback ke SVG: ' . $e->getMessage());
            }
        }

        // Fallback: BaconQrCode SVG (tersedia tanpa Imagick, DomPDF mendukung inline SVG)
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 1),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            return $writer->writeString($content);
        } catch (Exception $e) {
            Log::warning('[SeleksiService::generateQrCodeBase64] SVG juga gagal: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Helper: return array not found standar.
     */
    private function notFound(string $entity, int $id): array
    {
        return [
            'success' => false,
            'message' => "{$entity} dengan ID {$id} tidak ditemukan.",
            'data' => null,
        ];
    }
}
