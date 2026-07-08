<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat jadwal mengajar guru — dicek tiap menit; service menjaga idempoten.
Schedule::command('notif:jadwal-mengajar')->everyMinute()->withoutOverlapping();

// Auto-alpa guru — sekali sehari, akhir hari. WAJIB ->timezone() eksplisit:
// app.timezone di project ini UTC, tanpa ini dailyAt() jalan jam 23:30 UTC (06:30 WIB).
Schedule::command('absensi-guru:tandai-alpa')
    ->dailyAt('23:30')
    ->timezone(config('sekolah.timezone'))
    ->withoutOverlapping();

// Pengingat belum absen masuk — dicek tiap menit (per lembaga, jendela lead-time
// sebelum jam_masuk_batas berbeda-beda); service menjaga idempoten.
Schedule::command('notif:belum-absen-masuk')->everyMinute()->withoutOverlapping();

// Pengingat belum input absensi siswa — dicek tiap menit (banyak jam_selesai
// berbeda sepanjang hari); service menjaga idempoten via notifikasi_jadwal_log.
Schedule::command('notif:belum-input-absensi')->everyMinute()->withoutOverlapping();
