<?php

namespace App\Services\Akademik;

use App\Models\Akademik\NotifikasiJadwalLog;
use App\Models\Master\JadwalKbm;
use App\Repositories\DeviceTokenRepositoryInterface;
use App\Services\Notifikasi\ExpoPushService;
use App\Support\WaktuSekolah;
use Illuminate\Database\QueryException;

class JadwalNotifikasiService
{
    public function __construct(
        protected ExpoPushService $push,
        protected DeviceTokenRepositoryInterface $deviceRepo,
    ) {}

    /**
     * Kirim pengingat ke guru untuk jadwal yang mulai dalam rentang lead time.
     * Idempoten: tiap jadwal hanya dinotifikasi sekali per hari.
     *
     * @return array{diproses:int,token_terkirim:int}
     */
    public function kirimReminderDue(): array
    {
        $now = WaktuSekolah::now();
        $hari = WaktuSekolah::hari($now);

        if (! $hari) {
            return ['diproses' => 0, 'token_terkirim' => 0]; // Minggu / hari libur
        }

        $tanggal = $now->toDateString();
        $lead = config('sekolah.notif_lead_minutes');
        $batasAtas = $now->copy()->addMinutes($lead);

        $jadwals = JadwalKbm::with([
            'guru:id,nama,gelar_depan,gelar_belakang,user_id',
            'mataPelajaran:id,nama',
            'rombel:id,nama,tingkat',
        ])
            ->where('hari', $hari)
            ->whereTime('jam_mulai', '>', $now->format('H:i:s'))
            ->whereTime('jam_mulai', '<=', $batasAtas->format('H:i:s'))
            ->get();

        $diproses = 0;
        $tokenTerkirim = 0;

        foreach ($jadwals as $jadwal) {
            $sudah = NotifikasiJadwalLog::where('jadwal_kbm_id', $jadwal->id)
                ->whereDate('tanggal', $tanggal)
                ->where('jenis', 'reminder')
                ->exists();

            if ($sudah) {
                continue;
            }

            $tokens = $jadwal->guru?->user_id
                ? $this->deviceRepo->tokensForUser($jadwal->guru->user_id)
                : [];

            $jamMulai = substr((string) $jadwal->jam_mulai, 0, 5);
            $kelas = trim("{$jadwal->rombel?->tingkat} {$jadwal->rombel?->nama}");
            $mapel = $jadwal->mataPelajaran?->nama ?? 'mata pelajaran';

            $judul = 'Waktunya Mengajar';
            $isi = "Kelas {$kelas} — {$mapel}, mulai pukul {$jamMulai}"
                .($jadwal->ruangan ? " di {$jadwal->ruangan}" : '').'.';

            $hasil = $this->push->sendToTokens($tokens, $judul, $isi, [
                'type' => 'jadwal_reminder',
                'jadwal_id' => $jadwal->id,
                'rombel_id' => $jadwal->rombel_id,
                'mapel_id' => $jadwal->mata_pelajaran_id,
                'jam_mulai' => $jamMulai,
            ]);

            // Catat log agar tidak dikirim ganda (unique constraint juga menjaga).
            try {
                NotifikasiJadwalLog::create([
                    'jadwal_kbm_id' => $jadwal->id,
                    'guru_id' => $jadwal->guru_id,
                    'tanggal' => $tanggal,
                    'jenis' => 'reminder',
                    'jumlah_token' => count($tokens),
                    'sent_at' => now(),
                ]);
            } catch (QueryException $e) {
                continue; // sudah tercatat oleh proses lain; jangan hitung
            }

            // Bersihkan token yang tidak valid.
            foreach ($hasil['invalid_tokens'] ?? [] as $tokenInvalid) {
                $this->deviceRepo->forget($tokenInvalid);
            }

            $diproses++;
            $tokenTerkirim += $hasil['sent'] ?? 0;
        }

        return ['diproses' => $diproses, 'token_terkirim' => $tokenTerkirim];
    }
}
