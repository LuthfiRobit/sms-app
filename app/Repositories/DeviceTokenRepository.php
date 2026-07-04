<?php

namespace App\Repositories;

use App\Models\DeviceToken;

class DeviceTokenRepository implements DeviceTokenRepositoryInterface
{
    public function __construct(protected DeviceToken $model) {}

    public function register(int $userId, string $token, ?string $platform): void
    {
        // Token unik global: kalau sudah ada (mis. pindah akun di HP yang sama),
        // pindahkan kepemilikan ke user saat ini.
        $this->model->updateOrCreate(
            ['token' => $token],
            ['user_id' => $userId, 'platform' => $platform, 'last_used_at' => now()],
        );
    }

    public function forget(string $token): void
    {
        $this->model->where('token', $token)->delete();
    }

    public function tokensForUser(int $userId): array
    {
        return $this->model->where('user_id', $userId)->pluck('token')->all();
    }
}
