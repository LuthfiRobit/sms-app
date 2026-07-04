<?php

namespace App\Services\Akademik;

use App\Models\Akademik\AbsensiGuru;
use App\Models\Akademik\KalenderLibur;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Services\LogActivityService;
use App\Support\WaktuSekolah;
use Illuminate\Database\QueryException;

/**
 * Menandai 'alpa' guru yang tidak melakukan absen masuk sepanjang hari itu.
 * Dijalankan sekali di akhir hari (lihat routes/console.php) — status ini
 * murni informasional/setelah-fakta, bukan real-time.
 */
class AbsensiGuruAlpaService
{
    public function __construct(protected LogActivityService $logActivity) {}

    public function prosesHariIni(): array
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $dibuat = 0;
        $lembagaDilewati = 0;

        Lembaga::aktif()->each(function (Lembaga $lembaga) use ($tanggal, &$dibuat, &$lembagaDilewati) {
            if (KalenderLibur::isLibur($tanggal, $lembaga->id)) {
                $lembagaDilewati++;

                return;
            }

            Guru::where('lembaga_id', $lembaga->id)
                ->aktif()
                ->whereDoesntHave('absensiGuru', fn ($q) => $q->whereDate('tanggal', $tanggal))
                ->chunkById(200, function ($guruChunk) use ($tanggal, $lembaga, &$dibuat) {
                    foreach ($guruChunk as $guru) {
                        try {
                            AbsensiGuru::create([
                                'guru_id' => $guru->id,
                                'lembaga_id' => $lembaga->id,
                                'tanggal' => $tanggal,
                                'status' => 'alpa',
                            ]);
                            $dibuat++;
                        } catch (QueryException $e) {
                            // unique(guru_id,tanggal) sudah terisi oleh proses/koreksi lain — aman diabaikan.
                            continue;
                        }
                    }
                });
        });

        $this->logActivity->log('Auto-Alpa Guru', "Auto-alpa {$tanggal}: {$dibuat} guru ditandai alpa, {$lembagaDilewati} lembaga dilewati (hari libur).");

        return ['tanggal' => $tanggal, 'dibuat' => $dibuat, 'lembaga_dilewati' => $lembagaDilewati];
    }
}
