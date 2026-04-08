<?php

namespace App\Http\Controllers\Webhook;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Transaksi\PembayaranService;

/**
 * WebhookController
 *
 * Endpoint untuk menerima callback/notification dari payment gateway.
 *
 * PENTING:
 *  - Route HARUS di-exclude dari CSRF verification
 *  - Rate limit diterapkan via middleware throttle:60,1
 *  - SELALU return HTTP 200 ke Midtrans (agar tidak retry terus-menerus)
 *  - Semua webhook yang masuk di-log ke webhook.log (termasuk yang gagal)
 *
 * @see config/midtrans.php
 * @see PembayaranService::handleCallback()
 */
class WebhookController extends Controller
{
    public function __construct(
        protected PembayaranService $pembayaranService
    ) {
    }

    /**
     * Handle Midtrans payment notification webhook.
     *
     * Midtrans akan mengirim POST request ke endpoint ini setiap kali
     * ada perubahan status transaksi. Endpoint ini harus:
     *
     *  1. Validasi Content-Type = application/json
     *  2. Log semua incoming webhook ke file terpisah (webhook.log)
     *  3. Panggil PembayaranService->handleCallback()
     *  4. SELALU return HTTP 200 OK
     *     → Jika return non-200, Midtrans akan retry hingga 5x
     *     → Retry interval: 2min, 10min, 30min, 1h, 3h
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function midtrans(Request $request): JsonResponse
    {
        // --- 1. Log incoming webhook (ALL — termasuk invalid) ---
        $this->logWebhook('INCOMING', $request->all(), $request->ip());

        // --- 2. Validasi Content-Type ---
        if (!$request->isJson() && $request->header('Content-Type') !== 'application/json') {
            $this->logWebhook('REJECTED', [
                'reason'       => 'Invalid Content-Type',
                'content_type' => $request->header('Content-Type'),
            ], $request->ip());

            // Tetap return 200 untuk menghindari retry dari Midtrans
            // Tapi beri informasi bahwa request invalid
            return response()->json([
                'status'  => 'error',
                'message' => 'Content-Type must be application/json',
            ], 200);
        }

        // --- 3. Proses callback melalui service ---
        try {
            $result = $this->pembayaranService->handleCallback($request->all());

            // Log hasil processing
            $this->logWebhook(
                $result['success'] ? 'PROCESSED' : 'FAILED',
                [
                    'order_id' => $request->input('order_id'),
                    'status'   => $request->input('transaction_status'),
                    'result'   => $result['message'],
                ],
                $request->ip()
            );
        } catch (\Exception $e) {
            // Catch semua exception agar SELALU return 200
            $this->logWebhook('EXCEPTION', [
                'order_id' => $request->input('order_id'),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ], $request->ip());

            Log::error('[WebhookController::midtrans] Unhandled exception: ' . $e->getMessage(), [
                'order_id' => $request->input('order_id'),
                'payload'  => $request->all(),
            ]);
        }

        // --- 4. SELALU return HTTP 200 OK ---
        // Midtrans mengharapkan 200 sebagai acknowledgment.
        // Jika return non-200, Midtrans akan retry dan bisa menyebabkan
        // duplicate processing (yang sudah kita handle dengan idempotency).
        return response()->json([
            'status'  => 'ok',
            'message' => 'Webhook received',
        ], 200);
    }

    /**
     * Log webhook ke file terpisah: storage/logs/webhook.log
     *
     * Format log yang terstruktur untuk memudahkan debugging dan audit:
     *  [TIMESTAMP] [TYPE] [IP] {payload_json}
     *
     * @param string      $type    Tipe log: INCOMING, PROCESSED, FAILED, REJECTED, EXCEPTION
     * @param array       $data    Data yang akan di-log
     * @param string|null $ip      IP address pengirim
     * @return void
     */
    private function logWebhook(string $type, array $data, ?string $ip = null): void
    {
        try {
            // Gunakan channel 'webhook' yang terpisah
            Log::channel('webhook')->info("[{$type}]", [
                'ip'        => $ip,
                'timestamp' => now()->toIso8601String(),
                'data'      => $data,
            ]);
        } catch (\Exception $e) {
            // Fallback ke default log jika webhook channel tidak tersedia
            Log::warning("[WebhookController] Failed to write to webhook log: {$e->getMessage()}", [
                'type' => $type,
                'data' => $data,
            ]);
        }
    }
}
