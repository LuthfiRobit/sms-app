<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Zona Waktu Operasional Sekolah
    |--------------------------------------------------------------------------
    | app.timezone di-set UTC untuk penyimpanan. Namun jadwal KBM & absensi
    | berpatokan waktu lokal sekolah. Nilai ini dipakai untuk mencocokkan
    | "hari ini" dan "jam sekarang" pada penjadwalan notifikasi.
    */
    'timezone' => env('SEKOLAH_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Lead Time Notifikasi Jadwal Mengajar (menit)
    |--------------------------------------------------------------------------
    | Notifikasi dikirim ke guru sekian menit sebelum jam_mulai kelasnya.
    */
    'notif_lead_minutes' => (int) env('NOTIF_JADWAL_LEAD_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Lead Time Reminder Belum Absen Masuk (menit)
    |--------------------------------------------------------------------------
    | Notifikasi dikirim ke guru yang belum absen masuk, sekian menit sebelum
    | jam_masuk_batas lembaganya.
    */
    'notif_belum_absen_lead_minutes' => (int) env('NOTIF_BELUM_ABSEN_LEAD_MINUTES', 15),

];
