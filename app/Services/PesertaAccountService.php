<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\Hash;

class PesertaAccountService
{
    /**
     * Buat akun user baru untuk peserta PPDB.
     *
     * Pola mengikuti konvensi project:
     * - Primary key users = id_user
     * - Role dicari via kolom `name` (bukan role_name)
     * - Username = email peserta
     * - Status default = 'pending' (menunggu verifikasi OTP)
     */
    public function createPesertaAccount(
        string $namaLengkap,
        string $email,
        string $password,
        string $status = 'pending'
    ): User {
        if (User::where('email', $email)->exists()) {
            throw new Exception('Email sudah terdaftar. Gunakan email lain atau login.');
        }

        $user = User::create([
            'name'     => $namaLengkap,
            'email'    => $email,
            'username' => $email, // Username = email untuk peserta
            'password' => Hash::make($password),
            'status'   => $status,
        ]);

        // Cari role berdasarkan kolom `name` sesuai struktur tabel roles aktual
        $roleId = Role::where('name', 'peserta')->value('id');

        if (!$roleId) {
            $user->forceDelete();
            throw new Exception("Role 'peserta' tidak ditemukan. Jalankan RolePesertaSeeder terlebih dahulu.");
        }

        // Gunakan id_user sesuai primary key di tabel users project ini
        UserRole::create([
            'user_id' => $user->id_user,
            'role_id' => $roleId,
        ]);

        return $user;
    }

    /**
     * Aktifkan akun setelah verifikasi OTP berhasil.
     */
    public function activateAccount(User $user): bool
    {
        return $user->update(['status' => 'active']);
    }

    /**
     * Cek apakah user memiliki role peserta.
     *
     * Menggunakan UserRole model dengan id_user dan role.name
     * sesuai struktur aktual tabel user_role & roles.
     */
    public function isPeserta(User $user): bool
    {
        $roleId = Role::where('name', 'peserta')->value('id');

        if (!$roleId) {
            return false;
        }

        return UserRole::where('user_id', $user->id_user)
            ->where('role_id', $roleId) // kolom pivot = role_id, bukan id
            ->exists();
    }

    /**
     * Update profil dasar (name, email) — tidak mengubah password.
     *
     * Validasi email unik mengecualikan user yang sedang diupdate
     * via primary key id_user.
     */
    public function updateBasicProfile(User $user, string $namaLengkap, string $email): User
    {
        if (
            $email !== $user->email &&
            User::where('email', $email)
                ->where('id_user', '!=', $user->id_user)
                ->exists()
        ) {
            throw new Exception('Email sudah digunakan akun lain.');
        }

        $user->update([
            'name'  => $namaLengkap,
            'email' => $email,
        ]);

        return $user->fresh();
    }

    /**
     * Ubah password peserta dengan validasi password lama.
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): bool
    {
        if (!Hash::check($oldPassword, $user->password)) {
            throw new Exception('Password lama tidak sesuai.');
        }

        return $user->update(['password' => Hash::make($newPassword)]);
    }
}
