<?php

namespace App\Console\Commands;

use App\Services\Akademik\JadwalNotifikasiService;
use Illuminate\Console\Command;

class KirimNotifikasiJadwal extends Command
{
    protected $signature = 'notif:jadwal-mengajar';

    protected $description = 'Kirim notifikasi pengingat ke guru menjelang jam mengajar.';

    public function handle(JadwalNotifikasiService $service): int
    {
        $hasil = $service->kirimReminderDue();

        $this->info(sprintf(
            'Notifikasi jadwal: %d jadwal diproses, %d token terkirim.',
            $hasil['diproses'],
            $hasil['token_terkirim'],
        ));

        return self::SUCCESS;
    }
}
