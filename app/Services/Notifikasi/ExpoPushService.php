<?php

namespace App\Services\Notifikasi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\LazyCollection;

/**
 * Pengirim push notification via Expo Push Notification Service.
 * Server cukup POST ke API Expo (tanpa OAuth/kredensial Firebase) —
 * Expo yang menjembatani pengiriman ke FCM (Android) / APNs (iOS).
 *
 * Token yang diterima harus berformat Expo push token: "ExponentPushToken[...]".
 */
class ExpoPushService
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    private const BATCH_SIZE = 100; // batas per request dari Expo

    /**
     * Kirim notifikasi ke banyak token perangkat.
     *
     * @return array{sent:int,failed:int,skipped:bool,reason?:string,invalid_tokens?:array}
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_unique(array_filter(
            $tokens,
            fn ($t) => str_starts_with((string) $t, 'ExponentPushToken[')
        )));

        if (empty($tokens)) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => true, 'reason' => 'no_tokens'];
        }

        $sent = 0;
        $failed = 0;
        $invalidTokens = [];

        foreach (array_chunk($tokens, self::BATCH_SIZE) as $batch) {
            $messages = array_map(fn ($token) => [
                'to' => $token,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => 'high',
            ], $batch);

            $resp = Http::post(self::ENDPOINT, $messages);

            if (! $resp->successful()) {
                $failed += count($batch);

                continue;
            }

            foreach (LazyCollection::make($resp->json('data', []))->zip($batch) as [$tiket, $token]) {
                if (($tiket['status'] ?? null) === 'ok') {
                    $sent++;
                } else {
                    $failed++;
                    if (($tiket['details']['error'] ?? null) === 'DeviceNotRegistered') {
                        $invalidTokens[] = $token;
                    }
                }
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'skipped' => false, 'invalid_tokens' => $invalidTokens];
    }
}
