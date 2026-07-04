<?php

namespace App\Support;

/**
 * Token QR kehadiran siswa: payload (peserta_id + tanggal) yang ditandatangani
 * HMAC-SHA256 memakai APP_KEY. Dipakai halaman TAP web dan API mobile —
 * format identik sehingga QR yang dicetak dari web bisa discan dari HP.
 */
class QrToken
{
    public static function generate(int $pesertaId, string $tanggal): string
    {
        $payload = base64_encode(json_encode(['peserta_id' => $pesertaId, 'tanggal' => $tanggal]));
        $sig = hash_hmac('sha256', $payload, config('app.key'));

        return $payload.'.'.substr($sig, 0, 16);
    }

    /** @return array{peserta_id:int,tanggal:string}|null null jika tanda tangan tidak sah */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$payload, $shortSig] = $parts;
        $fullSig = hash_hmac('sha256', $payload, config('app.key'));

        if (! hash_equals(substr($fullSig, 0, 16), $shortSig)) {
            return null;
        }

        $data = json_decode(base64_decode($payload), true);
        if (! isset($data['peserta_id'], $data['tanggal'])) {
            return null;
        }

        return ['peserta_id' => (int) $data['peserta_id'], 'tanggal' => (string) $data['tanggal']];
    }
}
