<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FonnteService
 *
 * Service class untuk mengirim notifikasi WhatsApp dan mengecek status device melalui API Fonnte.
 * Dokumentasi API: https://fonnte.com/api
 */
class FonnteService
{
    private string $apiUrl = 'https://api.fonnte.com/send';

    private string $deviceUrl = 'https://api.fonnte.com/device';

    private string $token;

    private string $sender;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', '');
        $this->sender = config('services.fonnte.sender', '');
    }

    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string  $nomorTujuan  Nomor HP tujuan (format 628xxx atau 08xxx)
     * @param  string  $pesan  Isi pesan yang akan dikirim
     * @return array ['success' => bool, 'response' => mixed]
     */
    public function kirimPesan(string $nomorTujuan, string $pesan): array
    {
        if (empty($this->token)) {
            Log::warning('[Fonnte] Token belum dikonfigurasi di .env');

            return [
                'success' => false,
                'response' => 'Token Fonnte belum dikonfigurasi di .env',
            ];
        }

        // Clean recipient number format
        $nomorTujuan = $this->formatNumber($nomorTujuan);

        try {
            Log::info("[Fonnte] Mengirim pesan ke: {$nomorTujuan}");

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->post($this->apiUrl, [
                    'target' => $nomorTujuan,
                    'message' => $pesan,
                    'countryCode' => '62',
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['status']) && $body['status'] === true) {
                Log::info("[Fonnte] ✓ Pesan berhasil dikirim ke {$nomorTujuan}");

                return [
                    'success' => true,
                    'response' => $body,
                ];
            }

            Log::warning('[Fonnte] ✗ Gagal kirim pesan: '.json_encode($body));

            return [
                'success' => false,
                'response' => $body ?? $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('[Fonnte] Exception saat mengirim pesan: '.$e->getMessage());

            return [
                'success' => false,
                'response' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cek status koneksi device pada akun Fonnte.
     *
     * @return array ['success' => bool, 'response' => mixed]
     */
    public function checkDeviceStatus(): array
    {
        if (empty($this->token)) {
            return [
                'success' => false,
                'response' => 'Token Fonnte belum dikonfigurasi di .env',
            ];
        }

        try {
            Log::info('[Fonnte] Memeriksa status device');

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $this->token,
                ])
                ->post($this->deviceUrl);

            $body = $response->json();

            if ($response->successful() && isset($body['status'])) {
                return [
                    'success' => (bool) $body['status'],
                    'response' => $body,
                ];
            }

            Log::warning('[Fonnte] Gagal mengambil status device: '.json_encode($body));

            return [
                'success' => false,
                'response' => $body ?? $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('[Fonnte] Exception saat cek status device: '.$e->getMessage());

            return [
                'success' => false,
                'response' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format nomor HP agar sesuai dengan format standar (dimulai dengan 62 atau negara tujuan).
     */
    private function formatNumber(string $number): string
    {
        // Hapus karakter non-digit
        $number = preg_replace('/[^0-9]/', '', $number);

        // Jika nomor diawali dengan 0, ubah menjadi 62
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return $number;
    }
}
