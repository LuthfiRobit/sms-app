<?php

namespace App\Console\Commands;

use App\Services\Akademik\AbsensiGuruAlpaService;
use Illuminate\Console\Command;

class TandaiAlpaGuru extends Command
{
    protected $signature = 'absensi-guru:tandai-alpa';

    protected $description = 'Tandai alpa untuk guru yang belum absen masuk hari ini (per lembaga, skip hari libur).';

    public function handle(AbsensiGuruAlpaService $service): int
    {
        $hasil = $service->prosesHariIni();

        $this->info("Selesai: {$hasil['tanggal']} — {$hasil['dibuat']} guru ditandai alpa, {$hasil['lembaga_dilewati']} lembaga dilewati (libur).");

        return self::SUCCESS;
    }
}
