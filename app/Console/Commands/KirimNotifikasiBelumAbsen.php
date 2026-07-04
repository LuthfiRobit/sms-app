<?php

namespace App\Console\Commands;

use App\Services\Akademik\AbsensiNotifikasiService;
use Illuminate\Console\Command;

class KirimNotifikasiBelumAbsen extends Command
{
    protected $signature = 'notif:belum-absen-masuk';

    protected $description = 'Kirim pengingat ke guru yang belum absen masuk menjelang jam_masuk_batas lembaganya.';

    public function handle(AbsensiNotifikasiService $service): int
    {
        $hasil = $service->kirimReminderDue();

        $this->info("Selesai: {$hasil['diproses']} guru diingatkan, {$hasil['token_terkirim']} token terkirim.");

        return self::SUCCESS;
    }
}
