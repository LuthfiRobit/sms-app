<?php

namespace App\Jobs;

use App\Services\Integrations\FonnteService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Kirim satu pesan WhatsApp laporan kehadiran/nilai ke satu nomor wali murid.
 *
 * SENGAJA dijalankan SYNCHRONOUS (dipanggil via dispatchSync(), bukan
 * dispatch() ke queue) — versi awal didesain sebagai queued job (ShouldQueue)
 * supaya tidak memblokir respons API, tapi di lapangan proses queue worker
 * terpisah (`php artisan queue:listen`) tidak selalu konsisten berjalan di
 * lingkungan development guru/admin. Akibatnya aplikasi melaporkan "berhasil"
 * (job berhasil DITITIPKAN ke tabel `jobs`) padahal pesannya tidak pernah
 * benar-benar diproses/terkirim — guru baru sadar setelah wali murid
 * mengonfirmasi tidak menerima apa-apa. Karena aksi ini dipicu manual oleh
 * satu guru per kelas (bukan proses berfrekuensi tinggi) dan jumlah siswa
 * per kelas kecil (~20-40), menunggu beberapa detik ekstra saat tombol
 * "Selesai Mengajar" ditekan jauh lebih aman daripada gagal terkirim diam-diam.
 */
class KirimAbsensiWhatsappJob
{
    use Dispatchable;

    public function __construct(
        public readonly int $pesertaId,
        public readonly string $namaSiswa,
        public readonly string $noHp,
        public readonly string $pesan,
    ) {}

    /** @throws RuntimeException Kalau Fonnte gagal mengirim — pemanggil (KelasMobileService) yang menangkap per-siswa. */
    public function handle(FonnteService $fonnte): void
    {
        $hasil = $fonnte->kirimPesan($this->noHp, $this->pesan);

        if (! $hasil['success']) {
            Log::warning('[KirimAbsensiWhatsappJob] Gagal kirim', [
                'peserta_id' => $this->pesertaId,
                'nama' => $this->namaSiswa,
                'response' => $hasil['response'],
            ]);

            throw new RuntimeException('Fonnte gagal mengirim pesan: '.json_encode($hasil['response']));
        }

        Log::info('[KirimAbsensiWhatsappJob] Notifikasi absensi berhasil dikirim', [
            'peserta_id' => $this->pesertaId,
            'nama' => $this->namaSiswa,
        ]);
    }
}
