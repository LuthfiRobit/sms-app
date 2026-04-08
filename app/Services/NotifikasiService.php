<?php

namespace App\Services;

use App\Jobs\KirimNotifikasiJob;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * NotifikasiService
 *
 * Layanan multi-channel notification untuk sistem PPDB.
 * Mendukung channel email dan in-app via Laravel Queue (non-blocking).
 * Semua pengiriman di-dispatch ke queue database — request tidak tertahan.
 *
 * Cara penggunaan:
 *   app(NotifikasiService::class)->kirim($userId, 'pendaftaran_submit', [
 *       'nama_peserta'   => 'Budi Santoso',
 *       'no_pendaftaran' => 'REG-2026-0001',
 *       'jalur'          => 'Reguler',
 *   ]);
 */
class NotifikasiService
{
    // =========================================================================
    // 1. KIRIM — dispatch ke queue untuk setiap channel
    // =========================================================================

    /**
     * Kirim notifikasi ke user via satu atau lebih channel.
     * Job di-dispatch ke queue sehingga tidak memblok HTTP request.
     *
     * @param int    $userId   ID user penerima (referensi ke users.id_user)
     * @param string $event    Event template: 'pendaftaran_submit', 'pembayaran_success', dst.
     * @param array  $data     Konteks data: nama_peserta, no_pendaftaran, jalur, status, catatan, dll.
     * @param array  $channels Channel aktif: ['email', 'inapp'] (default keduanya)
     */
    public function kirim(
        int    $userId,
        string $event,
        array  $data = [],
        array  $channels = ['email', 'inapp'],
    ): void {
        try {
            $template = $this->getTemplate($event, $data);

            foreach ($channels as $channel) {
                $channel = strtolower(trim($channel));

                if ($channel === 'sms') {
                    // SMS: skip jika tidak ada konfigurasi SMS gateway
                    if (!$this->hasSmsGateway()) {
                        Log::debug('[NotifikasiService] Channel SMS dilewati — tidak ada konfigurasi gateway.');
                        continue;
                    }
                }

                // Buat record notifikasi dengan status 'pending' untuk channel inapp & email
                $notifikasiId = null;
                if (in_array($channel, ['email', 'inapp'])) {
                    $tipeRecord = match ($channel) {
                        'email' => 'email',
                        'inapp' => 'inapp',
                        default => 'inapp',
                    };

                    // Untuk in-app, langsung buat record sekarang (akses saat login)
                    // Untuk email, buat record sebagai jejak pengiriman
                    $notifikasi = Notifikasi::create([
                        'user_id'     => $userId,
                        'tipe'        => $tipeRecord,
                        'judul'       => $template['judul'],
                        'isi'         => $template['body_inapp'],
                        'status'      => 'pending',
                        'dibaca'      => false,
                        'waktu_kirim' => null,
                    ]);

                    $notifikasiId = $notifikasi->id;
                }

                // Dispatch job ke queue
                KirimNotifikasiJob::dispatch(
                    userId:        $userId,
                    channel:       $channel,
                    event:         $event,
                    data:          $data,
                    judul:         $template['judul'],
                    bodyEmail:     $template['body_email'],
                    bodyInapp:     $template['body_inapp'],
                    notifikasiId:  $notifikasiId,
                )->onQueue('notifikasi');
            }

            Log::info('[NotifikasiService::kirim] Job dispatched ke queue', [
                'user_id'  => $userId,
                'event'    => $event,
                'channels' => $channels,
            ]);
        } catch (\Throwable $e) {
            Log::error('[NotifikasiService::kirim] Gagal dispatch notifikasi', [
                'user_id' => $userId,
                'event'   => $event,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim notifikasi ke semua user admin.
     * Berguna untuk event seperti 'admin_pendaftaran_baru'.
     *
     * @param string $event    Nama event template
     * @param array  $data     Konteks data notifikasi
     * @param array  $channels Channel yang digunakan
     */
    public function kirimKeAdmin(
        string $event,
        array  $data = [],
        array  $channels = ['inapp'],
    ): void {
        try {
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'superadmin', 'Admin', 'Super Admin']);
            })->get();

            foreach ($admins as $admin) {
                $this->kirim($admin->id_user, $event, $data, $channels);
            }

            Log::info('[NotifikasiService::kirimKeAdmin] Notifikasi admin dispatched', [
                'event'       => $event,
                'jumlah_admin'=> $admins->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[NotifikasiService::kirimKeAdmin] ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 2. GET TEMPLATE — template berbasis event dalam Bahasa Indonesia
    // =========================================================================

    /**
     * Ambil template notifikasi berdasarkan nama event.
     * Placeholder diganti menggunakan Str::replace() dari array $data.
     *
     * Placeholder yang didukung:
     *   {nama_peserta}, {no_pendaftaran}, {jalur}, {status}, {catatan},
     *   {jurusan}, {nominal}, {tanggal}, {tahun}
     *
     * @param  string $event Nama event
     * @param  array  $data  Konteks untuk mengganti placeholder
     * @return array{judul: string, body_email: string, body_inapp: string}
     */
    public function getTemplate(string $event, array $data = []): array
    {
        $templates = [

            // ------------------------------------------------------------------
            'pendaftaran_submit' => [
                'judul'      => 'Pendaftaran #{no_pendaftaran} Diterima',
                'body_email' => "Halo {nama_peserta},\n\nSelamat! Pendaftaran Anda dengan nomor <strong>#{no_pendaftaran}</strong> telah berhasil diterima oleh sistem kami.\n\n<strong>Detail Pendaftaran:</strong>\n• Jalur: {jalur}\n• Status: Menunggu Verifikasi Dokumen\n\nTim panitia PPDB akan segera memverifikasi kelengkapan dokumen Anda. Proses verifikasi berlangsung maksimal <strong>3 hari kerja</strong>.\n\nPantau status pendaftaran Anda melalui portal peserta secara berkala.",
                'body_inapp' => 'Pendaftaran #{no_pendaftaran} Anda telah berhasil diterima. Menunggu verifikasi dokumen oleh panitia.',
            ],

            // ------------------------------------------------------------------
            'pembayaran_success' => [
                'judul'      => 'Pembayaran #{no_pendaftaran} Berhasil',
                'body_email' => "Halo {nama_peserta},\n\nPembayaran biaya pendaftaran untuk nomor <strong>#{no_pendaftaran}</strong> telah berhasil dikonfirmasi.\n\n<strong>Informasi Pembayaran:</strong>\n• Nominal: {nominal}\n• Status: Lunas ✅\n• Tanggal: {tanggal}\n\nAnda dapat melanjutkan proses pendaftaran berikutnya. Simpan bukti pembayaran ini sebagai referensi.",
                'body_inapp' => 'Pembayaran #{no_pendaftaran} sebesar {nominal} telah dikonfirmasi. Proses pendaftaran dapat dilanjutkan.',
            ],

            // ------------------------------------------------------------------
            'verifikasi_approve' => [
                'judul'      => 'Pendaftaran Anda Diverifikasi ✔️',
                'body_email' => "Halo {nama_peserta},\n\nKabar baik! Dokumen pendaftaran Anda dengan nomor <strong>#{no_pendaftaran}</strong> telah <strong>diverifikasi dan dinyatakan lengkap</strong> oleh tim panitia.\n\n<strong>Jalur Pendaftaran:</strong> {jalur}\n<strong>Status:</strong> Dokumen Terverifikasi\n\nAnda akan mengikuti proses seleksi sesuai jadwal yang telah ditentukan. Pantau pengumuman hasil seleksi melalui portal peserta.",
                'body_inapp' => 'Dokumen pendaftaran #{no_pendaftaran} telah diverifikasi dan dinyatakan lengkap. Lanjut ke proses seleksi.',
            ],

            // ------------------------------------------------------------------
            'verifikasi_reject' => [
                'judul'      => 'Pendaftaran Perlu Perbaikan ⚠️',
                'body_email' => "Halo {nama_peserta},\n\nDokumen pendaftaran Anda dengan nomor <strong>#{no_pendaftaran}</strong> perlu dilengkapi atau diperbaiki.\n\n<strong>Catatan dari Panitia:</strong>\n{catatan}\n\nMohon segera lakukan perbaikan dan upload ulang dokumen yang diminta melalui portal peserta. Perbaikan harus diselesaikan sebelum batas waktu yang ditentukan agar pendaftaran Anda dapat diproses.",
                'body_inapp' => 'Dokumen #{no_pendaftaran} perlu diperbaiki. Catatan: {catatan}. Segera update di portal.',
            ],

            // ------------------------------------------------------------------
            'pengumuman_lulus' => [
                'judul'      => 'Selamat! Anda Diterima 🎉',
                'body_email' => "Halo {nama_peserta},\n\n<strong>Selamat! Anda dinyatakan DITERIMA</strong> di SMK melalui jalur <strong>{jalur}</strong>.\n\n<strong>Program Keahlian:</strong> {jurusan}\n<strong>No. Pendaftaran:</strong> #{no_pendaftaran}\n\n<strong>📅 Langkah Selanjutnya — Daftar Ulang:</strong>\nAnda WAJIB melakukan daftar ulang sesuai jadwal yang tertera di portal peserta. Kegagalan melakukan daftar ulang pada waktu yang ditentukan akan mengakibatkan kelulusan Anda dibatalkan.\n\nSelamat bergabung bersama kami! 🏫",
                'body_inapp' => 'Selamat! Anda DITERIMA di SMK jalur {jalur} — {jurusan}. Segera lakukan daftar ulang!',
            ],

            // ------------------------------------------------------------------
            'pengumuman_tidak_lulus' => [
                'judul'      => 'Hasil Seleksi #{no_pendaftaran}',
                'body_email' => "Halo {nama_peserta},\n\nTerima kasih telah mengikuti proses seleksi PPDB SMK melalui jalur <strong>{jalur}</strong>.\n\nDengan mempertimbangkan kuota yang tersedia dan hasil seleksi secara keseluruhan, kami menyampaikan bahwa Anda <strong>belum dapat diterima</strong> pada jalur tersebut untuk tahun ajaran ini.\n\nJangan berkecil hati! Anda masih dapat mendaftar melalui jalur penerimaan lain yang masih tersedia. Informasi selengkapnya dapat dilihat di portal peserta.\n\nTerima kasih atas kepercayaan Anda. Semoga sukses ke depannya.",
                'body_inapp' => 'Hasil seleksi jalur {jalur}: Belum diterima. Cek info jalur penerimaan lain di portal.',
            ],

            // ------------------------------------------------------------------
            'daftar_ulang_reminder' => [
                'judul'      => '🔔 Pengingat Daftar Ulang — Segera Dilakukan!',
                'body_email' => "Halo {nama_peserta},\n\nIni adalah <strong>pengingat penting</strong> bahwa batas waktu daftar ulang Anda semakin dekat!\n\n<strong>No. Pendaftaran:</strong> #{no_pendaftaran}\n<strong>Program Keahlian:</strong> {jurusan}\n<strong>Batas Daftar Ulang:</strong> {tanggal}\n\n⚠️ <strong>PERHATIAN:</strong> Jika Anda tidak menyelesaikan daftar ulang sebelum batas waktu yang ditentukan, status kelulusan Anda akan dibatalkan secara otomatis.\n\nSegera akses portal peserta dan lengkapi proses daftar ulang Anda sekarang.",
                'body_inapp' => 'PENGINGAT: Batas daftar ulang #{no_pendaftaran} adalah {tanggal}. Segera selesaikan!',
            ],

            // ------------------------------------------------------------------
            'admin_pendaftaran_baru' => [
                'judul'      => 'Pendaftaran Baru: {nama_peserta}',
                'body_email' => "Halo Admin,\n\nTerdapat pendaftaran baru yang masuk dan memerlukan verifikasi.\n\n<strong>Detail Pendaftaran:</strong>\n• Nama Peserta: {nama_peserta}\n• No. Pendaftaran: #{no_pendaftaran}\n• Jalur: {jalur}\n• Waktu Daftar: {tanggal}\n\nSilakan akses dashboard admin untuk memproses pendaftaran ini.",
                'body_inapp' => 'Pendaftaran baru dari {nama_peserta} (#{no_pendaftaran}) jalur {jalur} menunggu verifikasi.',
            ],
        ];

        // Fallback jika event tidak ditemukan
        if (!array_key_exists($event, $templates)) {
            Log::warning("[NotifikasiService::getTemplate] Event '{$event}' tidak ditemukan, menggunakan template default.");
            return [
                'judul'      => 'Notifikasi PPDB',
                'body_email' => "Halo {nama_peserta},\n\nAnda memiliki notifikasi baru dari sistem PPDB. Silakan cek portal peserta untuk informasi lebih lanjut.",
                'body_inapp' => 'Anda memiliki notifikasi baru dari sistem PPDB.',
            ];
        }

        $template = $templates[$event];

        // Siapkan replacement map dengan nilai default jika tidak ada di $data
        $replacements = array_merge([
            '{nama_peserta}'    => '-',
            '{no_pendaftaran}'  => '-',
            '{jalur}'           => '-',
            '{status}'          => '-',
            '{catatan}'         => 'Tidak ada catatan tambahan.',
            '{jurusan}'         => '-',
            '{nominal}'         => '-',
            '{tanggal}'         => now()->locale('id')->isoFormat('D MMMM YYYY'),
            '{tahun}'           => now()->year,
        ], $this->buildReplacementMap($data));

        $search  = array_keys($replacements);
        $replace = array_values($replacements);

        return [
            'judul'      => Str::replace($search, $replace, $template['judul']),
            'body_email' => Str::replace($search, $replace, $template['body_email']),
            'body_inapp' => Str::replace($search, $replace, $template['body_inapp']),
        ];
    }

    // =========================================================================
    // 3. MARK AS READ
    // =========================================================================

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     * Validasi kepemilikan dilakukan untuk mencegah akses tidak sah.
     *
     * @param int $notifikasiId ID notifikasi
     * @param int $userId       ID user yang mengklaim kepemilikan
     * @return bool             true jika berhasil diupdate
     */
    public function markAsRead(int $notifikasiId, int $userId): bool
    {
        try {
            $updated = Notifikasi::where('id', $notifikasiId)
                ->where('user_id', $userId)
                ->where('dibaca', false) // Hanya update jika belum dibaca
                ->update(['dibaca' => true]);

            return $updated > 0;
        } catch (\Throwable $e) {
            Log::error('[NotifikasiService::markAsRead] ' . $e->getMessage(), [
                'notifikasi_id' => $notifikasiId,
                'user_id'       => $userId,
            ]);
            return false;
        }
    }

    /**
     * Tandai SEMUA notifikasi user sebagai sudah dibaca.
     *
     * @param int $userId
     * @return int Jumlah record yang diupdate
     */
    public function markAllAsRead(int $userId): int
    {
        return Notifikasi::where('user_id', $userId)
            ->where('dibaca', false)
            ->update(['dibaca' => true]);
    }

    // =========================================================================
    // 4. GET UNREAD COUNT
    // =========================================================================

    /**
     * Hitung jumlah notifikasi yang belum dibaca oleh user.
     * Digunakan untuk badge notifikasi di navbar/header.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return Notifikasi::where('user_id', $userId)
            ->where('dibaca', false)
            ->where('tipe', 'inapp')        // Hanya in-app yang tampil di UI
            ->where('status', 'terkirim')   // Hanya yang sudah terkirim
            ->count();
    }

    // =========================================================================
    // 5. INDEX — daftar notifikasi dengan filter
    // =========================================================================

    /**
     * Ambil daftar notifikasi milik user dengan opsi filter.
     *
     * @param int   $userId
     * @param array $filters Opsi filter:
     *                       - tipe: 'email'|'inapp'|'sms'
     *                       - dibaca: true|false
     *                       - status: 'pending'|'terkirim'|'gagal'
     *                       - per_page: int (gunakan paginate jika ada, default Collection)
     *                       - limit: int
     * @return Collection<int, Notifikasi>
     */
    public function index(int $userId, array $filters = []): Collection
    {
        $query = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if (isset($filters['tipe'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (isset($filters['dibaca'])) {
            $query->where('dibaca', (bool) $filters['dibaca']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['limit']) && is_int($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }

    /**
     * Ambil notifikasi belum dibaca (in-app) untuk ditampilkan saat login.
     * Alias yang lebih ekspresif untuk getUnreadNotifications.
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getUnreadInapp(int $userId, int $limit = 10): Collection
    {
        return $this->index($userId, [
            'tipe'   => 'inapp',
            'dibaca' => false,
            'status' => 'terkirim',
            'limit'  => $limit,
        ]);
    }

    // =========================================================================
    // 6. HELPERS LEGACY (backward-compatible)
    // =========================================================================

    /**
     * Alias backward-compatible untuk metode lama.
     * @deprecated Gunakan kirim() dengan channel ['inapp']
     */
    public function getBelumDibaca(int $userId): Collection
    {
        return $this->getUnreadInapp($userId);
    }

    /**
     * @deprecated Gunakan markAsRead()
     */
    public function tandaiDibaca(int $id, int $userId): bool
    {
        return $this->markAsRead($id, $userId);
    }

    /**
     * @deprecated Gunakan markAllAsRead()
     */
    public function tandaiSemuaDibaca(int $userId): int
    {
        return $this->markAllAsRead($userId);
    }

    /**
     * @deprecated Gunakan getUnreadCount()
     */
    public function countBelumDibaca(int $userId): int
    {
        return $this->getUnreadCount($userId);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Konversi array $data menjadi map {placeholder} => nilai.
     * Key dari $data otomatis dibungkus kurung kurawal.
     */
    private function buildReplacementMap(array $data): array
    {
        $map = [];
        foreach ($data as $key => $value) {
            if (!is_array($value) && !is_object($value)) {
                $map["{{$key}}"] = (string) $value;
            }
        }
        return $map;
    }

    /**
     * Cek apakah SMS gateway terkonfigurasi di .env.
     * Saat ini selalu false — bisa diisi logic cek config saat tersedia.
     */
    private function hasSmsGateway(): bool
    {
        return !empty(config('services.sms.api_key'))
            || !empty(config('services.sms.token'));
    }
}
