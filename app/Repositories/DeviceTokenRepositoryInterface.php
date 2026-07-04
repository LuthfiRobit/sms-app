<?php

namespace App\Repositories;

interface DeviceTokenRepositoryInterface
{
    /** Daftarkan/segarkan Expo push token sebuah perangkat untuk user. */
    public function register(int $userId, string $token, ?string $platform): void;

    /** Hapus token (mis. saat logout). */
    public function forget(string $token): void;

    /** Ambil semua Expo push token milik user (array of string). */
    public function tokensForUser(int $userId): array;
}
