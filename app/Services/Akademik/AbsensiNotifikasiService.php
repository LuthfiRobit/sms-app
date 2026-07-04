<?php

namespace App\Services\Akademik;

use App\Models\Akademik\KalenderLibur;
use App\Models\Akademik\NotifikasiAbsensiLog;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Repositories\DeviceTokenRepositoryInterface;
use App\Services\Notifikasi\ExpoPushService;
use App\Support\WaktuSekolah;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

/**
 * Pengingat push "belum absen masuk", dikirim sekali per guru per hari
 * menjelang jam_masuk_batas lembaganya. Mirror struktur JadwalNotifikasiService.
 */
class AbsensiNotifikasiService
{
    public function __construct(
        protected ExpoPushService $push,
        protected DeviceTokenRepositoryInterface $deviceRepo,
    ) {}

    public function kirimReminderDue(): array
    {
        $now = WaktuSekolah::now();
        $tanggal = $now->toDateString();
        $lead = (int) config('sekolah.notif_belum_absen_lead_minutes');
        $diproses = 0;
        $tokenTerkirim = 0;

        Lembaga::aktif()->whereNotNull('jam_masuk_batas')->each(function (Lembaga $lembaga) use ($now, $tanggal, $lead, &$diproses, &$tokenTerkirim) {
            if (KalenderLibur::isLibur($tanggal, $lembaga->id)) {
                return;
            }

            $batas = Carbon::parse($tanggal.' '.$lembaga->jam_masuk_batas, config('sekolah.timezone'));
            $window = $batas->copy()->subMinutes($lead);

            // Kirim hanya sekali, tepat di jendela lead-time sebelum batas —
            // guru yang sudah terlambat tidak perlu diingatkan lagi di sini.
            if ($now->lt($window) || $now->gte($batas)) {
                return;
            }

            $guruBelumAbsen = Guru::where('lembaga_id', $lembaga->id)
                ->aktif()
                ->whereDoesntHave('absensiGuru', fn ($q) => $q->whereDate('tanggal', $tanggal)->whereNotNull('jam_masuk'))
                ->get(['id', 'user_id']);

            foreach ($guruBelumAbsen as $guru) {
                $sudahDikirim = NotifikasiAbsensiLog::where('guru_id', $guru->id)
                    ->whereDate('tanggal', $tanggal)
                    ->where('jenis', 'belum_absen_masuk')
                    ->exists();

                if ($sudahDikirim) {
                    continue;
                }

                $tokens = $guru->user_id ? $this->deviceRepo->tokensForUser($guru->user_id) : [];
                $hasil = $this->push->sendToTokens(
                    $tokens,
                    'Pengingat Absen Masuk',
                    "Anda belum absen masuk hari ini. Batas jam masuk pukul {$lembaga->jam_masuk_batas}.",
                    ['type' => 'reminder_absen_masuk']
                );

                try {
                    NotifikasiAbsensiLog::create([
                        'guru_id' => $guru->id,
                        'tanggal' => $tanggal,
                        'jenis' => 'belum_absen_masuk',
                        'jumlah_token' => count($tokens),
                        'sent_at' => now(),
                    ]);
                } catch (QueryException $e) {
                    continue;
                }

                foreach ($hasil['invalid_tokens'] ?? [] as $t) {
                    $this->deviceRepo->forget($t);
                }

                $diproses++;
                $tokenTerkirim += $hasil['sent'] ?? 0;
            }
        });

        return ['diproses' => $diproses, 'token_terkirim' => $tokenTerkirim];
    }
}
