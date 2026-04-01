<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * NotifikasiService
 *
 * Mengelola pengiriman dan pembacaan notifikasi in-app.
 * Notifikasi disimpan ke tabel `notifikasi` dan bisa di-extend
 * untuk multi-channel (email, SMS) di masa mendatang.
 */
class NotifikasiService
{
    /**
     * Kirim notifikasi ke satu user.
     *
     * @param int    $userId   ID user penerima
     * @param string $tipe     Tipe notifikasi: 'info' | 'warning' | 'success' | 'error'
     * @param string $judul    Judul singkat notifikasi
     * @param string $isi      Isi pesan lengkap
     * @return Notifikasi|null Record notifikasi yang tersimpan, null jika gagal
     */
    public function kirim(int $userId, string $tipe, string $judul, string $isi): ?Notifikasi
    {
        try {
            return Notifikasi::create([
                'user_id'     => $userId,
                'tipe'        => $tipe,
                'judul'       => $judul,
                'isi'         => $isi,
                'status'      => 'terkirim',
                'dibaca'      => false,
                'waktu_kirim' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('[NotifikasiService::kirim] Gagal mengirim notifikasi: ' . $e->getMessage(), [
                'user_id' => $userId,
                'judul'   => $judul,
            ]);
            return null;
        }
    }

    /**
     * Kirim notifikasi ke semua user dengan role admin.
     * Digunakan saat ada aksi yang perlu diketahui seluruh admin.
     *
     * @param string $tipe  Tipe notifikasi
     * @param string $judul Judul singkat
     * @param string $isi   Isi pesan
     * @return int          Jumlah notifikasi yang berhasil dikirim
     */
    public function kirimKeAdmin(string $tipe, string $judul, string $isi): int
    {
        $kirim = 0;
        try {
            // Ambil semua user yang memiliki role admin/superadmin
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'superadmin', 'Admin', 'Super Admin']);
            })->get();

            foreach ($admins as $admin) {
                $result = $this->kirim($admin->id_user, $tipe, $judul, $isi);
                if ($result) {
                    $kirim++;
                }
            }
        } catch (\Exception $e) {
            Log::error('[NotifikasiService::kirimKeAdmin] ' . $e->getMessage());
        }

        return $kirim;
    }

    /**
     * Ambil notifikasi yang belum dibaca untuk satu user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getBelumDibaca(int $userId): Collection
    {
        return Notifikasi::where('user_id', $userId)
            ->belumDibaca()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca.
     *
     * @param int $id      ID notifikasi
     * @param int $userId  ID user (untuk validasi kepemilikan)
     * @return bool
     */
    public function tandaiDibaca(int $id, int $userId): bool
    {
        return Notifikasi::where('id', $id)
            ->where('user_id', $userId)
            ->update(['dibaca' => true]) > 0;
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     *
     * @param int $userId
     * @return int Jumlah record yang diupdate
     */
    public function tandaiSemuaDibaca(int $userId): int
    {
        return Notifikasi::where('user_id', $userId)
            ->where('dibaca', false)
            ->update(['dibaca' => true]);
    }

    /**
     * Hitung notifikasi belum dibaca milik user.
     *
     * @param int $userId
     * @return int
     */
    public function countBelumDibaca(int $userId): int
    {
        return Notifikasi::where('user_id', $userId)
            ->where('dibaca', false)
            ->count();
    }
}
