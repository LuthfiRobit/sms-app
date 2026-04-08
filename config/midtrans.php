<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Server Key digunakan untuk komunikasi server-to-server (Snap token
    | creation, notification verification). JANGAN pernah expose di client.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Client Key digunakan di frontend (snap.js) untuk menampilkan
    | popup pembayaran Midtrans.
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | false = Sandbox (testing), true = Production (live).
    | PASTIKAN env MIDTRANS_IS_PRODUCTION=true di server produksi.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Sanitize Input
    |--------------------------------------------------------------------------
    |
    | Jika true, Midtrans akan men-sanitize input parameter sebelum diproses.
    |
    */
    'is_sanitized' => true,

    /*
    |--------------------------------------------------------------------------
    | 3D Secure
    |--------------------------------------------------------------------------
    |
    | Jika true, transaksi kartu kredit akan melalui 3DS verification
    | untuk keamanan tambahan (highly recommended).
    |
    */
    'is_3ds' => true,

];
