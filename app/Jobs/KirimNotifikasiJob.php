<?php

namespace App\Jobs;

use App\Mail\PpdbNotifikasiMail;
use App\Models\Notifikasi;
use App\Models\User;
use App\Services\LogActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class KirimNotifikasiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout dalam detik sebelum job dianggap gagal.
     */
    public int $timeout = 60;

    /**
     * Backoff (detik) antar percobaan ulang: 60s, 120s, 300s
     */
    public array $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     *
     * @param int    $userId    ID user penerima
     * @param string $channel   Channel pengiriman: 'email' | 'inapp'
     * @param string $event     Nama event template
     * @param array  $data      Konteks data notifikasi
     * @param string $judul     Judul notifikasi (sudah dirender)
     * @param string $bodyEmail Body untuk email (sudah dirender)
     * @param string $bodyInapp Body untuk in-app (sudah dirender)
     * @param int|null $notifikasiId ID record notifikasi (untuk update status)
     */
    public function __construct(
        public readonly int     $userId,
        public readonly string  $channel,
        public readonly string  $event,
        public readonly array   $data,
        public readonly string  $judul,
        public readonly string  $bodyEmail,
        public readonly string  $bodyInapp,
        public readonly ?int    $notifikasiId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LogActivityService $logActivity): void
    {
        try {
            match ($this->channel) {
                'email'  => $this->kirimEmail(),
                'inapp'  => $this->kirimInapp(),
                default  => throw new \InvalidArgumentException("Channel tidak dikenal: {$this->channel}"),
            };

            // Update status notifikasi jika ada record-nya
            if ($this->notifikasiId) {
                Notifikasi::where('id', $this->notifikasiId)
                    ->update([
                        'status'      => 'terkirim',
                        'waktu_kirim' => now(),
                    ]);
            }

            Log::info('[KirimNotifikasiJob] Notifikasi berhasil dikirim', [
                'user_id'       => $this->userId,
                'channel'       => $this->channel,
                'event'         => $this->event,
                'notifikasi_id' => $this->notifikasiId,
            ]);

            $logActivity->log(
                'Kirim Notifikasi',
                "[{$this->channel}] Event: {$this->event} → User ID: {$this->userId}"
            );
        } catch (Throwable $e) {
            $this->handleFailure($e);
            throw $e; // Re-throw agar Laravel bisa retry
        }
    }

    /**
     * Kirim notifikasi via email.
     */
    private function kirimEmail(): void
    {
        $user = User::where('id_user', $this->userId)->firstOrFail();

        if (empty($user->email)) {
            Log::warning('[KirimNotifikasiJob] User tidak memiliki email', [
                'user_id' => $this->userId,
            ]);
            return;
        }

        Mail::to($user->email, $user->name)
            ->send(new PpdbNotifikasiMail(
                judul:     $this->judul,
                body:      $this->bodyEmail,
                event:     $this->event,
                namaPenerima: $user->name,
            ));
    }

    /**
     * Simpan notifikasi in-app ke database.
     */
    private function kirimInapp(): void
    {
        // Jika sudah ada record (dibuat dengan status pending), update saja
        if ($this->notifikasiId) {
            Notifikasi::where('id', $this->notifikasiId)->update([
                'status'      => 'terkirim',
                'waktu_kirim' => now(),
            ]);
            return;
        }

        // Buat record baru jika belum ada
        Notifikasi::create([
            'user_id'     => $this->userId,
            'tipe'        => 'inapp',
            'judul'       => $this->judul,
            'isi'         => $this->bodyInapp,
            'status'      => 'terkirim',
            'dibaca'      => false,
            'waktu_kirim' => now(),
        ]);
    }

    /**
     * Handle job failure gracefully.
     */
    private function handleFailure(Throwable $e): void
    {
        Log::error('[KirimNotifikasiJob] Gagal mengirim notifikasi', [
            'user_id'       => $this->userId,
            'channel'       => $this->channel,
            'event'         => $this->event,
            'notifikasi_id' => $this->notifikasiId,
            'error'         => $e->getMessage(),
            'attempt'       => $this->attempts(),
        ]);

        // Jika ini percobaan terakhir, tandai notifikasi sebagai gagal
        if ($this->attempts() >= $this->tries && $this->notifikasiId) {
            Notifikasi::where('id', $this->notifikasiId)
                ->update(['status' => 'gagal']);
        }
    }

    /**
     * The job failed to process (dipanggil setelah semua retry habis).
     */
    public function failed(Throwable $exception): void
    {
        Log::critical('[KirimNotifikasiJob] Job FINAL FAILED setelah semua retry', [
            'user_id'       => $this->userId,
            'channel'       => $this->channel,
            'event'         => $this->event,
            'notifikasi_id' => $this->notifikasiId,
            'error'         => $exception->getMessage(),
        ]);

        if ($this->notifikasiId) {
            Notifikasi::where('id', $this->notifikasiId)
                ->update(['status' => 'gagal']);
        }
    }
}
