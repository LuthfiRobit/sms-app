<?php

namespace App\Services\Transaksi;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Transaksi\PembayaranPpdbRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Ppdb\BiayaRegistrasiRepositoryInterface;

use App\Services\NotifikasiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;

use App\Models\Transaksi\PembayaranPpdb;
use App\Models\Transaksi\Pendaftaran;

/**
 * PembayaranService
 *
 * Service pembayaran PPDB yang aman, idempotent, dan production-ready.
 * Menangani integrasi Midtrans Snap API dengan penekanan pada:
 *
 *  ┌─────────────────────────────────────────────────────────────────────────┐
 *  │  SECURITY CHECKLIST:                                                    │
 *  │  ✓ Signature validation pada SETIAP webhook callback                   │
 *  │  ✓ Amount re-validated dari database (TIDAK trust dari callback)       │
 *  │  ✓ DB::transaction() untuk update atomik (pembayaran + pendaftaran)    │
 *  │  ✓ Idempotent: duplikat callback ditangani tanpa double-update         │
 *  │  ✓ order_id unik per transaksi: PPDB-{no_pendaftaran}-{timestamp_ms}  │
 *  │  ✓ Satu pendaftaran hanya punya SATU pembayaran active (pending/paid) │
 *  │  ✓ Raw callback JSON disimpan ke midtrans_response (audit trail)       │
 *  └─────────────────────────────────────────────────────────────────────────┘
 *
 * Dependencies (semua di-inject via constructor):
 *  - PembayaranPpdbRepositoryInterface
 *  - PendaftaranRepositoryInterface
 *  - BiayaRegistrasiRepositoryInterface
 *  - NotifikasiService
 *  - LogActivityService
 *  - ResponseService
 */
class PembayaranService
{
    /**
     * Status pembayaran yang dianggap "active" (tidak boleh buat transaksi baru).
     */
    private const ACTIVE_PAYMENT_STATUSES = [
        PembayaranPpdb::STATUS_PENDING,
        PembayaranPpdb::STATUS_PAID,
    ];

    /**
     * Status pendaftaran minimum yang diperlukan untuk membayar.
     * Pendaftaran masih DRAFT tidak boleh bayar.
     */
    private const PAYABLE_STATUSES = [
        Pendaftaran::STATUS_SUBMIT,
        Pendaftaran::STATUS_VERIFIKASI,
        Pendaftaran::STATUS_LULUS,
        Pendaftaran::STATUS_DAFTAR_ULANG,
    ];

    /**
     * Ukuran maksimum bukti transfer manual (5 MB).
     */
    private const MAX_BUKTI_SIZE_KB = 5120;

    // =========================================================================
    // CONSTRUCTOR — Setup Midtrans Config
    // =========================================================================

    public function __construct(
        protected PembayaranPpdbRepositoryInterface $pembayaranRepo,
        protected PendaftaranRepositoryInterface     $pendaftaranRepo,
        protected BiayaRegistrasiRepositoryInterface $biayaRepo,
        protected NotifikasiService                  $notifikasiService,
        protected LogActivityService                 $logActivity,
        protected ResponseService                    $responseService,
    ) {
        // Setup Midtrans configuration dari config/midtrans.php
        \Midtrans\Config::$serverKey   = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = config('midtrans.is_3ds');
    }

    // =========================================================================
    // 1. CREATE SNAP TOKEN — Generate Midtrans payment popup
    // =========================================================================

    /**
     * Membuat Snap Token untuk pembayaran pendaftaran PPDB via Midtrans.
     *
     * Flow idempotent:
     *  1. Validasi pendaftaran sudah di-submit (bukan draft)
     *  2. Jika sudah ada pembayaran PAID → return error (sudah dibayar)
     *  3. Jika sudah ada pembayaran PENDING → return existing snap_token (idempotent)
     *  4. Ambil biaya_registrasi aktif untuk jalur ini
     *  5. Generate order_id unik: PPDB-{no_pendaftaran}-{timestamp_ms}
     *  6. Call Midtrans Snap::createTransaction()
     *  7. Simpan record pembayaran status=pending
     *  8. Return snap_token + metadata
     *
     * @param  int   $pendaftaranId  ID pendaftaran
     * @param  int   $userId         ID user yang melakukan pembayaran
     * @return array ['success', 'message', 'data']
     */
    public function createSnapToken(int $pendaftaranId, int $userId): array
    {
        try {
            // --- 1. Ambil dan validasi pendaftaran ---
            $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId, [
                'peserta',
                'peserta.kontak',
                'jalurPendaftaran',
            ]);

            if (!$pendaftaran) {
                return $this->notFound('Pendaftaran', $pendaftaranId);
            }

            // Status check: pendaftaran harus sudah di-submit (bukan draft)
            if (!in_array($pendaftaran->status, self::PAYABLE_STATUSES)) {
                return [
                    'success' => false,
                    'message' => "Pembayaran hanya bisa dilakukan setelah pendaftaran di-submit. Status saat ini: {$pendaftaran->status}.",
                    'data'    => null,
                ];
            }

            // --- 2. Cek pembayaran yang sudah ada (idempotency) ---
            $existingPayments = $this->pembayaranRepo->all([
                'pendaftaran_id' => $pendaftaranId,
            ]);

            // Cek apakah sudah ada yang PAID
            $paidPayment = $existingPayments->firstWhere('status', PembayaranPpdb::STATUS_PAID);
            if ($paidPayment) {
                return [
                    'success' => false,
                    'message' => "Pembayaran untuk pendaftaran #{$pendaftaran->no_pendaftaran} sudah lunas. Tidak perlu membayar ulang.",
                    'data'    => [
                        'status'     => 'paid',
                        'order_id'   => $paidPayment->order_id,
                        'amount'     => (int) $paidPayment->amount,
                        'waktu_bayar' => $paidPayment->waktu_bayar?->format('d/m/Y H:i:s'),
                    ],
                ];
            }

            // Cek apakah ada yang PENDING → return existing snap_token (idempotent)
            $pendingPayment = $existingPayments->firstWhere('status', PembayaranPpdb::STATUS_PENDING);
            if ($pendingPayment && $pendingPayment->snap_token) {
                return [
                    'success' => true,
                    'message' => 'Snap token pembayaran sudah tersedia. Silakan lanjutkan pembayaran.',
                    'data'    => [
                        'snap_token' => $pendingPayment->snap_token,
                        'client_key' => config('midtrans.client_key'),
                        'amount'     => (int) $pendingPayment->amount,
                        'order_id'   => $pendingPayment->order_id,
                    ],
                ];
            }

            // --- 3. Ambil biaya registrasi aktif untuk jalur pendaftaran ini ---
            $biaya = $this->getActiveBiaya($pendaftaran->jalur_pendaftaran_id, $pendaftaran->tahun_pelajaran_id);

            if (!$biaya) {
                return [
                    'success' => false,
                    'message' => 'Biaya registrasi belum dikonfigurasi untuk jalur pendaftaran ini. Hubungi admin.',
                    'data'    => null,
                ];
            }

            // Amount HARUS integer (Midtrans tidak terima desimal)
            $amount = (int) $biaya->nominal;

            if ($amount <= 0) {
                return [
                    'success' => false,
                    'message' => 'Nominal biaya registrasi tidak valid (harus > 0). Hubungi admin.',
                    'data'    => null,
                ];
            }

            // --- 4. Generate order_id unik ---
            $orderId = 'PPDB-' . $pendaftaran->no_pendaftaran . '-' . round(microtime(true) * 1000);

            // --- 5. Siapkan parameter Midtrans Snap ---
            $peserta = $pendaftaran->peserta;

            // Gunakan email user yang login sebagai fallback — Midtrans menolak empty string
            $authUser      = \App\Models\User::find($userId);
            $customerEmail = $peserta?->kontak?->email ?: ($authUser?->email ?? null);
            $customerPhone = $peserta?->kontak?->no_hp ?: null;

            $customerDetails = ['first_name' => $peserta?->nama_lengkap ?? 'Peserta'];
            if ($customerEmail) {
                $customerDetails['email'] = $customerEmail;
            }
            if ($customerPhone) {
                $customerDetails['phone'] = $customerPhone;
            }

            $params = [
                'transaction_details' => [
                    'order_id'     => $orderId,
                    'gross_amount' => $amount,
                ],
                'item_details' => [
                    [
                        'id'       => 'BIAYA-' . $biaya->id,
                        'price'    => $amount,
                        'quantity' => 1,
                        'name'     => substr($biaya->nama ?? 'Biaya Registrasi PPDB', 0, 50),
                    ],
                ],
                'customer_details' => $customerDetails,
                'callbacks' => [
                    'finish' => url('/portal/pembayaran/selesai'),
                ],
            ];

            // --- 6. Call Midtrans Snap API ---
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // --- 7. Simpan record pembayaran ---
            $pembayaran = $this->pembayaranRepo->create([
                'pendaftaran_id'     => $pendaftaranId,
                'biaya_registrasi_id' => $biaya->id,
                'metode'             => 'midtrans',
                'status'             => PembayaranPpdb::STATUS_PENDING,
                'snap_token'         => $snapToken,
                'order_id'           => $orderId,
                'amount'             => $amount,
                'midtrans_response'  => ['info' => 'Snap token generated', 'request_time' => now()->toIso8601String()],
            ]);

            // --- Log activity ---
            $this->logActivity->log(
                'Buat Pembayaran',
                "Snap token dibuat untuk pendaftaran #{$pendaftaran->no_pendaftaran}. Order: {$orderId}, Amount: Rp " . number_format($amount, 0, ',', '.')
            );

            // --- 8. Return snap_token + metadata ---
            return [
                'success' => true,
                'message' => 'Snap token berhasil dibuat. Silakan lanjutkan pembayaran.',
                'data'    => [
                    'snap_token' => $snapToken,
                    'client_key' => config('midtrans.client_key'),
                    'amount'     => $amount,
                    'order_id'   => $orderId,
                ],
            ];
        } catch (Exception $e) {
            Log::error('[PembayaranService::createSnapToken] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $userId,
                'trace'          => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat token pembayaran. Silakan coba lagi atau hubungi admin.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 2. HANDLE CALLBACK — Webhook handler untuk notifikasi Midtrans
    // =========================================================================

    /**
     * Memproses callback/notification dari Midtrans webhook.
     *
     * KEAMANAN KRITIS yang diimplementasi:
     *  1. Signature validation: SHA512(order_id + status_code + gross_amount + ServerKey)
     *  2. Amount re-validated dari database (TIDAK trust callback)
     *  3. Idempotency: jika status terakhir sudah paid/failed, skip processing
     *  4. Atomic update: DB::transaction() untuk update pembayaran + pendaftaran
     *  5. Raw callback JSON disimpan ke midtrans_response (audit)
     *
     * Mapping transaction_status Midtrans ke status internal:
     *  - capture + fraud_status=accept → paid
     *  - settlement                    → paid
     *  - pending                       → pending (tetap)
     *  - deny                          → failed
     *  - cancel                        → failed
     *  - expire                        → expired
     *
     * @param  array $callbackData  Raw data dari Midtrans notification
     * @return array ['success', 'message']
     */
    public function handleCallback(array $callbackData): array
    {
        $orderId          = $callbackData['order_id'] ?? null;
        $transactionStatus = $callbackData['transaction_status'] ?? null;
        $fraudStatus      = $callbackData['fraud_status'] ?? null;
        $statusCode       = $callbackData['status_code'] ?? null;
        $grossAmount      = $callbackData['gross_amount'] ?? null;
        $signatureKey     = $callbackData['signature_key'] ?? null;

        try {
            // --- 1. VALIDASI SIGNATURE (MANDATORY — TIDAK BOLEH DISKIP) ---
            if (!$this->validateSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
                Log::warning('[PembayaranService::handleCallback] SIGNATURE INVALID', [
                    'order_id'  => $orderId,
                    'ip'        => request()->ip(),
                    'payload'   => $callbackData,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid signature. Callback rejected.',
                ];
            }

            // --- 2. Cari pembayaran by order_id ---
            $pembayaran = $this->pembayaranRepo->findByOrderId($orderId);

            if (!$pembayaran) {
                Log::warning('[PembayaranService::handleCallback] Order ID not found', [
                    'order_id' => $orderId,
                ]);

                return [
                    'success' => false,
                    'message' => "Pembayaran dengan order_id '{$orderId}' tidak ditemukan.",
                ];
            }

            // --- 3. IDEMPOTENCY CHECK ---
            // Jika status sudah final (paid/failed/expired/refund), skip processing
            $finalStatuses = [
                PembayaranPpdb::STATUS_PAID,
                PembayaranPpdb::STATUS_FAILED,
                PembayaranPpdb::STATUS_EXPIRED,
                PembayaranPpdb::STATUS_REFUND,
            ];

            if (in_array($pembayaran->status, $finalStatuses)) {
                Log::info('[PembayaranService::handleCallback] Duplicate callback (already processed)', [
                    'order_id'       => $orderId,
                    'current_status' => $pembayaran->status,
                    'callback_status' => $transactionStatus,
                ]);

                return [
                    'success' => true,
                    'message' => "Callback sudah diproses sebelumnya. Status: {$pembayaran->status}.",
                ];
            }

            // --- 4. MAP transaction_status Midtrans → status internal ---
            $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

            // --- 5. PROCESS UPDATE ---
            $result = $this->processPaymentUpdate($pembayaran, $newStatus, $callbackData, 'webhook');

            if ($result['success'] && str_contains($result['message'] ?? '', 'berhasil diperbarui')) {
                $this->logActivity->log(
                    'Callback Pembayaran',
                    "Webhook Midtrans diproses: Order {$orderId}, Status: {$transactionStatus} → {$newStatus}"
                );

                Log::info('[PembayaranService::handleCallback] Success', [
                    'order_id'    => $orderId,
                    'midtrans_status' => $transactionStatus,
                    'internal_status' => $newStatus,
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('[PembayaranService::handleCallback] ' . $e->getMessage(), [
                'order_id' => $orderId,
                'payload'  => $callbackData,
                'trace'    => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses callback: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi status pembayaran langsung dari API Midtrans.
     * Berguna jika webhook terlambat atau tidak sampai (terutama di environment local).
     *
     * @param  string $orderId
     * @return array
     */
    public function syncStatus(string $orderId): array
    {
        try {
            // Find payment record
            $pembayaran = $this->pembayaranRepo->findByOrderId($orderId);
            if (!$pembayaran) {
                return [
                    'success' => false,
                    'message' => "Pembayaran dengan Order ID {$orderId} tidak ditemukan.",
                ];
            }

            // Get status from Midtrans API
            $statusResponse = \Midtrans\Transaction::status($orderId);
            
            // Convert Midtrans object to array for easier handling
            $data = json_decode(json_encode($statusResponse), true);

            $transactionStatus = $data['transaction_status'] ?? null;
            $fraudStatus      = $data['fraud_status'] ?? null;
            $signatureKey     = $data['signature_key'] ?? null;
            $statusCode       = $data['status_code'] ?? null;
            $grossAmount      = $data['gross_amount'] ?? null;

            // Validasi Signature Key jika disediakan oleh API (untuk perlindungan ekstra MITM)
            if ($signatureKey && !$this->validateSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
                Log::warning('[PembayaranService::syncStatus] SIGNATURE INVALID', [
                    'order_id' => $orderId,
                    'payload'  => $data,
                ]);
                throw new Exception("Signature key tidak valid pada respons Midtrans API.");
            }

            // Map status
            $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

            // Check if status changed or update is needed
            // (We update anyway to refresh the midtrans_response data)
            $result = $this->processPaymentUpdate($pembayaran, $newStatus, $data, 'sync');

            if ($result['success'] && str_contains($result['message'] ?? '', 'berhasil diperbarui')) {
                $this->logActivity->log(
                    'Sinkronisasi Pembayaran',
                    "Status pembayaran #{$orderId} disinkronkan manual: {$newStatus}"
                );
            }

            return $result;

        } catch (Exception $e) {
            Log::error('[PembayaranService::syncStatus] ' . $e->getMessage(), [
                'order_id' => $orderId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal sinkronisasi status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Logika internal untuk update status pembayaran, update pendaftaran, dan kirim notifikasi.
     * Diekstrak agar bisa dipakai oleh handleCallback (webhook) dan syncStatus (pull).
     *
     * @param  PembayaranPpdb $pembayaran
     * @param  string         $newStatus
     * @param  array          $responseData
     * @param  string         $source       Asal trigger (webhook/sync)
     * @return array
     */
    private function processPaymentUpdate(PembayaranPpdb $pembayaran, string $newStatus, array $responseData, string $source = 'sync'): array
    {
        try {
            return DB::transaction(function () use ($pembayaran, $newStatus, $responseData, $source) {
                // Lock row pembayaran untuk mencegah race condition antara webhook dan sync
                $lockedPembayaran = PembayaranPpdb::where('id', $pembayaran->id)->lockForUpdate()->first();
                
                if (!$lockedPembayaran) {
                    throw new Exception("Data pembayaran tidak ditemukan saat memproses update.");
                }

                // Re-check status final di dalam lock (Idempotency)
                $finalStatuses = [
                    PembayaranPpdb::STATUS_PAID,
                    PembayaranPpdb::STATUS_FAILED,
                    PembayaranPpdb::STATUS_EXPIRED,
                    PembayaranPpdb::STATUS_REFUND,
                ];

                if (in_array($lockedPembayaran->status, $finalStatuses)) {
                    return [
                        'success' => true,
                        'message' => "Abaikan update via {$source}. Status sudah final: {$lockedPembayaran->status}",
                    ];
                }

                $updateData = [
                    'status'            => $newStatus,
                    'midtrans_response' => $responseData,
                ];

                // Jika status = paid, set waktu_bayar (jika belum ada)
                if ($newStatus === PembayaranPpdb::STATUS_PAID) {
                    $updateData['waktu_bayar'] = $lockedPembayaran->waktu_bayar ?? now();
                }

                // Update pembayaran (langsung pakai method model karena sudah di-load)
                $lockedPembayaran->update($updateData);

                // --- Jika PAID: update status pendaftaran ke 'verifikasi' ---
                if ($newStatus === PembayaranPpdb::STATUS_PAID) {
                    $pendaftaran = $this->pendaftaranRepo->findById($lockedPembayaran->pendaftaran_id, ['peserta']);

                    if ($pendaftaran) {
                        // Hanya update ke verifikasi jika status saat ini adalah 'submit'
                        if ($pendaftaran->status === Pendaftaran::STATUS_SUBMIT) {
                            $this->pendaftaranRepo->updateStatus(
                                $pendaftaran->id,
                                Pendaftaran::STATUS_VERIFIKASI
                            );
                        }

                        // Kirim notifikasi
                        if ($pendaftaran->peserta?->user_id) {
                            $this->notifikasiService->kirim(
                                $pendaftaran->peserta->user_id,
                                'pembayaran_success',
                                [
                                    'nama_peserta'   => $pendaftaran->peserta->nama_lengkap,
                                    'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                                    'nominal'        => "Rp " . number_format((int) $lockedPembayaran->amount, 0, ',', '.'),
                                    'tanggal'        => now()->isoFormat('D MMMM YYYY')
                                ]
                            );

                            $this->notifikasiService->kirimKeAdmin(
                                'pembayaran_success',
                                [
                                    'nama_peserta'   => $pendaftaran->peserta->nama_lengkap,
                                    'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                                    'nominal'        => "Rp " . number_format((int) $lockedPembayaran->amount, 0, ',', '.'),
                                    'tanggal'        => now()->isoFormat('D MMMM YYYY')
                                ]
                            );
                        }
                    }
                }

                return [
                    'success' => true,
                    'message' => "Status pembayaran berhasil diperbarui menjadi: {$newStatus} via {$source}",
                ];
            });
        } catch (Exception $e) {
            throw $e;
        }
    }

    // =========================================================================
    // 3. GET STATUS — Cek status pembayaran untuk pendaftaran
    // =========================================================================

    /**
     * Mengambil status pembayaran terkini untuk sebuah pendaftaran.
     *
     * Return mencakup:
     *  - has_payment     : boolean, apakah ada record pembayaran
     *  - current_status  : status pembayaran terkini (pending/paid/expired/failed)
     *  - can_create_new  : boolean, apakah bisa buat pembayaran baru
     *  - payment         : data pembayaran lengkap (jika ada)
     *  - history         : riwayat semua pembayaran untuk pendaftaran ini
     *
     * @param  int   $pendaftaranId  ID pendaftaran
     * @return array ['success', 'message', 'data']
     */
    public function getStatus(int $pendaftaranId): array
    {
        try {
            $pendaftaran = $this->pendaftaranRepo->findById($pendaftaranId);

            if (!$pendaftaran) {
                return $this->notFound('Pendaftaran', $pendaftaranId);
            }

            // Ambil semua pembayaran untuk pendaftaran ini
            $payments = $this->pembayaranRepo->all(
                ['pendaftaran_id' => $pendaftaranId],
                ['biayaRegistrasi']
            );

            // Cari pembayaran aktif (paid atau pending)
            $activePayment = $payments
                ->whereIn('status', self::ACTIVE_PAYMENT_STATUSES)
                ->sortByDesc('created_at')
                ->first();

            // Tentukan apakah bisa buat pembayaran baru
            $canCreateNew = !$activePayment && in_array($pendaftaran->status, self::PAYABLE_STATUSES);

            return [
                'success' => true,
                'message' => 'Status pembayaran berhasil diambil.',
                'data'    => [
                    'has_payment'    => $payments->isNotEmpty(),
                    'current_status' => $activePayment?->status ?? ($payments->isNotEmpty() ? $payments->sortByDesc('created_at')->first()->status : null),
                    'can_create_new' => $canCreateNew,
                    'payment'        => $activePayment ? [
                        'id'          => $activePayment->id,
                        'order_id'    => $activePayment->order_id,
                        'amount'      => (int) $activePayment->amount,
                        'status'      => $activePayment->status,
                        'metode'      => $activePayment->metode,
                        'snap_token'  => $activePayment->snap_token,
                        'client_key'  => config('midtrans.client_key'),
                        'waktu_bayar' => $activePayment->waktu_bayar?->format('d/m/Y H:i:s'),
                        'created_at'  => $activePayment->created_at?->format('d/m/Y H:i:s'),
                    ] : null,
                    'history' => $payments->sortByDesc('created_at')->map(fn($p) => [
                        'id'          => $p->id,
                        'order_id'    => $p->order_id,
                        'amount'      => (int) $p->amount,
                        'status'      => $p->status,
                        'metode'      => $p->metode,
                        'waktu_bayar' => $p->waktu_bayar?->format('d/m/Y H:i:s'),
                        'created_at'  => $p->created_at?->format('d/m/Y H:i:s'),
                    ])->values()->all(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('[PembayaranService::getStatus] ' . $e->getMessage(), [
                'pendaftaran_id' => $pendaftaranId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil status pembayaran.',
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 4. MANUAL KONFIRMASI — Upload bukti manual oleh admin
    // =========================================================================

    /**
     * Konfirmasi pembayaran secara manual oleh admin.
     *
     * Digunakan ketika peserta membayar via transfer bank manual:
     *  1. Admin mengupload bukti transfer
     *  2. Status diubah ke PAID
     *  3. Pendaftaran di-update ke verifikasi (jika masih status submit)
     *  4. Notifikasi dikirim ke peserta
     *
     * @param  int          $pembayaranId  ID pembayaran yang akan dikonfirmasi
     * @param  UploadedFile $bukti         File bukti transfer
     * @param  int          $userId        ID admin yang melakukan konfirmasi
     * @return array        ['success', 'message', 'data']
     */
    public function manualKonfirmasi(int $pembayaranId, UploadedFile $bukti, int $userId): array
    {
        try {
            $pembayaran = $this->pembayaranRepo->findById($pembayaranId, ['pendaftaran', 'pendaftaran.peserta']);

            if (!$pembayaran) {
                return $this->notFound('Pembayaran', $pembayaranId);
            }

            // Guard: hanya bisa konfirmasi pembayaran yang masih PENDING
            if ($pembayaran->status !== PembayaranPpdb::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => "Pembayaran tidak dapat dikonfirmasi. Status saat ini: {$pembayaran->status}. Hanya pembayaran pending yang bisa dikonfirmasi manual.",
                    'data'    => null,
                ];
            }

            // Validasi ukuran file (max 5 MB)
            if ($bukti->getSize() > self::MAX_BUKTI_SIZE_KB * 1024) {
                return [
                    'success' => false,
                    'message' => 'Ukuran file bukti transfer melebihi batas maksimum 5MB.',
                    'data'    => null,
                ];
            }

            // Validasi tipe file
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            if (!in_array($bukti->getMimeType(), $allowedMimes)) {
                return [
                    'success' => false,
                    'message' => 'Format file tidak valid. Gunakan JPG, PNG, WebP, atau PDF.',
                    'data'    => null,
                ];
            }

            // Upload bukti transfer
            $noPendaftaran = $pembayaran->pendaftaran?->no_pendaftaran ?? 'unknown';
            $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $noPendaftaran);
            $timestamp = now()->format('YmdHis');
            $extension = $bukti->getClientOriginalExtension();
            $namaFile  = "bukti_{$safeName}_{$timestamp}.{$extension}";
            $directory = "pembayaran/bukti";

            $path = $bukti->storeAs($directory, $namaFile, 'public');

            if (!$path) {
                throw new Exception('Gagal menyimpan file bukti transfer ke storage.');
            }

            // Atomic update: update pembayaran + pendaftaran
            DB::transaction(function () use ($pembayaran, $path, $userId) {
                // Update pembayaran
                $this->pembayaranRepo->update($pembayaran->id, [
                    'status'      => PembayaranPpdb::STATUS_PAID,
                    'metode'      => 'manual',
                    'waktu_bayar' => now(),
                    'bukti_bayar' => $path,
                    'keterangan'  => "Dikonfirmasi manual oleh admin (User ID: {$userId})",
                ]);

                // Update status pendaftaran ke verifikasi (jika masih submit)
                $pendaftaran = $pembayaran->pendaftaran;
                if ($pendaftaran && $pendaftaran->status === Pendaftaran::STATUS_SUBMIT) {
                    $this->pendaftaranRepo->updateStatus(
                        $pendaftaran->id,
                        Pendaftaran::STATUS_VERIFIKASI
                    );
                }

                // Notifikasi ke peserta
                if ($pendaftaran?->peserta?->user_id) {
                    $this->notifikasiService->kirim(
                        $pendaftaran->peserta->user_id,
                        'pembayaran_success',
                        [
                            'nama_peserta'   => $pendaftaran->peserta->nama_lengkap ?? 'Peserta',
                            'no_pendaftaran' => $pendaftaran->no_pendaftaran,
                            'nominal'        => "Rp " . number_format((int) $pembayaran->amount, 0, ',', '.'),
                            'tanggal'        => now()->isoFormat('D MMMM YYYY')
                        ]
                    );
                }
            });

            $this->logActivity->log(
                'Konfirmasi Manual Pembayaran',
                "Pembayaran #{$pembayaran->order_id} dikonfirmasi manual oleh admin (User ID: {$userId}). Bukti: {$path}"
            );

            return [
                'success' => true,
                'message' => "Pembayaran #{$pembayaran->order_id} berhasil dikonfirmasi secara manual.",
                'data'    => $this->pembayaranRepo->findById($pembayaran->id, ['pendaftaran', 'biayaRegistrasi']),
            ];
        } catch (Exception $e) {
            Log::error('[PembayaranService::manualKonfirmasi] ' . $e->getMessage(), [
                'pembayaran_id' => $pembayaranId,
                'user_id'       => $userId,
                'trace'         => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengkonfirmasi pembayaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // 5. CANCEL PAYMENT — Batalkan pembayaran pending
    // =========================================================================

    /**
     * Membatalkan pembayaran yang masih berstatus PENDING.
     *
     * Setelah cancel, peserta bisa membuat pembayaran baru melalui createSnapToken().
     * Pembayaran yang sudah PAID tidak bisa di-cancel melalui method ini.
     *
     * @param  int   $pembayaranId  ID pembayaran yang akan dibatalkan
     * @param  int   $userId        ID user yang melakukan cancel
     * @return array ['success', 'message', 'data']
     */
    public function cancelPayment(int $pembayaranId, int $userId): array
    {
        try {
            $pembayaran = $this->pembayaranRepo->findById($pembayaranId, ['pendaftaran']);

            if (!$pembayaran) {
                return $this->notFound('Pembayaran', $pembayaranId);
            }

            // Guard: hanya bisa cancel pembayaran PENDING
            if ($pembayaran->status !== PembayaranPpdb::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => "Pembayaran tidak dapat dibatalkan. Status saat ini: {$pembayaran->status}. Hanya pembayaran pending yang bisa dibatalkan.",
                    'data'    => null,
                ];
            }

            // Update status ke FAILED (cancelled)
            $this->pembayaranRepo->update($pembayaran->id, [
                'status'     => PembayaranPpdb::STATUS_FAILED,
                'keterangan' => "Dibatalkan oleh user (User ID: {$userId}) pada " . now()->format('d/m/Y H:i:s'),
            ]);

            $this->logActivity->log(
                'Batalkan Pembayaran',
                "Pembayaran #{$pembayaran->order_id} dibatalkan oleh user ID: {$userId}"
            );

            return [
                'success' => true,
                'message' => "Pembayaran #{$pembayaran->order_id} berhasil dibatalkan. Anda dapat membuat pembayaran baru.",
                'data'    => $this->pembayaranRepo->findById($pembayaran->id),
            ];
        } catch (Exception $e) {
            Log::error('[PembayaranService::cancelPayment] ' . $e->getMessage(), [
                'pembayaran_id' => $pembayaranId,
                'user_id'       => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Validasi signature Midtrans.
     *
     * Formula: SHA512(order_id + status_code + gross_amount + ServerKey)
     *
     * KRITIS: Fungsi ini TIDAK BOLEH di-skip dalam kondisi apapun.
     * Tanpa validasi ini, attacker bisa mengirim fake callback dan
     * mengubah status pembayaran secara ilegal.
     *
     * @param  string|null $orderId      Order ID dari callback
     * @param  string|null $statusCode   Status code HTTP dari Midtrans
     * @param  string|null $grossAmount  Gross amount dari callback
     * @param  string|null $signatureKey Signature key dari callback
     * @return bool                       True jika signature valid
     */
    private function validateSignature(?string $orderId, ?string $statusCode, ?string $grossAmount, ?string $signatureKey): bool
    {
        // Semua parameter wajib ada
        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return false;
        }

        $serverKey = config('midtrans.server_key');

        if (!$serverKey) {
            Log::critical('[PembayaranService::validateSignature] MIDTRANS_SERVER_KEY not configured!');
            return false;
        }

        // Generate expected signature
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // Timing-safe comparison untuk mencegah timing attack
        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Map transaction_status Midtrans ke status internal PembayaranPpdb.
     *
     * Referensi: https://docs.midtrans.com/docs/https-notification-webhooks
     *
     * @param  string|null $transactionStatus  Status dari Midtrans
     * @param  string|null $fraudStatus        Fraud status (untuk capture)
     * @return string                           Status internal
     */
    private function mapMidtransStatus(?string $transactionStatus, ?string $fraudStatus): string
    {
        return match ($transactionStatus) {
            'capture' => ($fraudStatus === 'accept' || $fraudStatus === 'challenge')
                ? PembayaranPpdb::STATUS_PAID
                : PembayaranPpdb::STATUS_FAILED,

            'settlement' => PembayaranPpdb::STATUS_PAID,

            'pending' => PembayaranPpdb::STATUS_PENDING,

            'deny', 'cancel' => PembayaranPpdb::STATUS_FAILED,

            'expire' => PembayaranPpdb::STATUS_EXPIRED,

            'refund', 'partial_refund' => PembayaranPpdb::STATUS_REFUND,

            default => PembayaranPpdb::STATUS_FAILED,
        };
    }

    /**
     * Ambil biaya registrasi aktif untuk jalur dan tahun pelajaran tertentu.
     *
     * @param  int $jalurId  ID jalur pendaftaran
     * @param  int $tahunId  ID tahun pelajaran
     * @return \App\Models\Ppdb\BiayaRegistrasi|null
     */
    private function getActiveBiaya(int $jalurId, int $tahunId)
    {
        $biayaList = $this->biayaRepo->all([
            'jalur_pendaftaran_id' => $jalurId,
            'tahun_pelajaran_id'  => $tahunId,
            'is_aktif'            => true,
        ]);

        return $biayaList->first();
    }

    /**
     * Return standar untuk entity not found.
     *
     * @param  string $entity  Nama entity
     * @param  int    $id      ID entity
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
