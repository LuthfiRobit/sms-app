<?php

namespace App\Console\Commands;

use App\Services\Akademik\AbsensiBelumInputNotifikasiService;
use Illuminate\Console\Command;

class KirimNotifikasiBelumInputAbsensi extends Command
{
    protected $signature = 'notif:belum-input-absensi';

    protected $description = 'Kirim notifikasi pengingat ke guru yang kelasnya sudah selesai tapi absensi siswa belum diisi.';

    public function handle(AbsensiBelumInputNotifikasiService $service): int
    {
        $hasil = $service->kirimReminderDue();

        $this->info(sprintf(
            'Notifikasi belum input absensi: %d jadwal diproses, %d token terkirim.',
            $hasil['diproses'],
            $hasil['token_terkirim'],
        ));

        return self::SUCCESS;
    }
}
