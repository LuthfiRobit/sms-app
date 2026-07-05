<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Helper waktu operasional sekolah. app.timezone = UTC untuk penyimpanan,
 * tapi jadwal & absensi berpatokan waktu lokal (config sekolah.timezone).
 */
class WaktuSekolah
{
    /** Waktu sekarang dalam zona waktu sekolah (mis. Asia/Jakarta). */
    public static function now(): Carbon
    {
        return Carbon::now(config('sekolah.timezone'));
    }

    /**
     * Nama hari (Bahasa Indonesia) sesuai enum jadwal_kbm.
     * Mengembalikan null untuk Minggu (tidak ada jadwal KBM).
     */
    public static function hari(?Carbon $waktu = null): ?string
    {
        $waktu = $waktu ?? self::now();

        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][$waktu->dayOfWeekIso] ?? null;
    }
}
