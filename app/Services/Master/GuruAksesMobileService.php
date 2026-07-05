<?php

namespace App\Services\Master;

use App\Repositories\Master\GuruRepositoryInterface;
use App\Services\Integrations\FaceRecognitionService;
use App\Services\Rbac\UserService;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Kelola akses akun mobile (login guru) langsung dari halaman Data Guru,
 * tanpa harus lewat menu Kelola Pengguna yang generik untuk semua akun.
 */
class GuruAksesMobileService
{
    public function __construct(
        protected GuruRepositoryInterface $guruRepo,
        protected UserService $userService,
        protected FaceRecognitionService $faceRecognition,
    ) {}

    public function info(int $guruId): array
    {
        $guru = $this->resolveGuruWithUser($guruId, wajibAdaAkun: false);

        return [
            'guru_id' => $guru->id,
            'nama_lengkap' => $guru->nama_lengkap,
            'ada_akun' => (bool) $guru->user,
            'email' => $guru->user?->email,
            'username' => $guru->user?->username,
            'status' => $guru->user?->status,
            'wajah_terdaftar' => (bool) $guru->wajah_terdaftar_at,
            'wajah_terdaftar_at' => $guru->wajah_terdaftar_at?->toIso8601String(),
        ];
    }

    /** Reset password akun mobile ke password acak baru. Nilai kembalian (plain) hanya tampil sekali. */
    public function resetPassword(int $guruId): string
    {
        $guru = $this->resolveGuruWithUser($guruId);
        $password = Str::password(8, symbols: false, spaces: false);

        $this->userService->updateUser($guru->user->id_user, ['password' => $password]);

        return $password;
    }

    public function toggleStatus(int $guruId): string
    {
        $guru = $this->resolveGuruWithUser($guruId);

        return $this->userService->toggleStatus($guru->user->id_user)->status;
    }

    public function revokeSesi(int $guruId): void
    {
        $guru = $this->resolveGuruWithUser($guruId);

        $this->userService->revokeTokens($guru->user->id_user);
    }

    /** Hapus wajah referensi guru — dari layanan verifikasi maupun database lokal. */
    public function resetWajah(int $guruId): void
    {
        $guru = $this->resolveGuruWithUser($guruId, wajibAdaAkun: false);

        if (! $guru->wajah_terdaftar_at) {
            throw new RuntimeException('Guru ini belum mendaftarkan wajah referensi.');
        }

        $this->faceRecognition->deleteEnrollment($guru->id);
        $guru->update(['foto' => null, 'wajah_terdaftar_at' => null]);
    }

    private function resolveGuruWithUser(int $guruId, bool $wajibAdaAkun = true)
    {
        $guru = $this->guruRepo->findById($guruId);

        if (! $guru) {
            throw new RuntimeException('Data guru tidak ditemukan.');
        }

        $guru->loadMissing('user');

        if ($wajibAdaAkun && ! $guru->user) {
            throw new RuntimeException('Guru ini belum memiliki akun login mobile.');
        }

        return $guru;
    }
}
