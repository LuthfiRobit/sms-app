<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base URL Layanan Face Verification
    |--------------------------------------------------------------------------
    |
    | Alamat microservice Flask (self-hosted) untuk verifikasi wajah guru.
    | Tanpa trailing slash, mis: http://127.0.0.1:5000
    |
    */
    'base_url' => env('FACE_RECOGNITION_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Dikirim via header X-API-Key, harus sama persis dengan
    | FACE_SERVICE_API_KEY di environment layanan Flask.
    |
    */
    'api_key' => env('FACE_RECOGNITION_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Kill-Switch
    |--------------------------------------------------------------------------
    |
    | Matikan fitur verifikasi wajah tanpa ubah kode — absen tetap jalan
    | normal (fail-mode permisif), cuma tidak pernah memanggil layanan.
    |
    */
    'enabled' => env('FACE_RECOGNITION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Timeout (detik)
    |--------------------------------------------------------------------------
    |
    | /verify memproses video (lebih lambat) — beri timeout lebih longgar
    | dibanding /enroll yang cuma memproses 1 foto.
    |
    */
    'timeout' => (int) env('FACE_RECOGNITION_TIMEOUT', 20),
    'connect_timeout' => (int) env('FACE_RECOGNITION_CONNECT_TIMEOUT', 5),
    'enroll_timeout' => (int) env('FACE_RECOGNITION_ENROLL_TIMEOUT', 10),

];
