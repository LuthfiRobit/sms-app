<?php

namespace App\Services\Akademik;

use App\Models\Akademik\Absensi;
use App\Models\Akademik\KalenderLibur;
use App\Models\Akademik\NotifikasiJadwalLog;
use App\Models\Master\JadwalKbm;
use App\Repositories\DeviceTokenRepositoryInterface;
use App\Services\Notifikasi\ExpoPushService;
use App\Support\WaktuSekolah;
use Illuminate\Database\QueryException;

/**
 * Pengingat push ke guru yang jadwal KBM-nya sudah selesai tapi absensi
 * SISWA di kelas itu belum diisi — melengkapi AbsensiNotifikasiService (yang
 * mengingatkan absensi GURU sendiri, bukan absensi siswa yang diajarnya).
 * Mirror struktur JadwalNotifikasiService, reuse tabel notifikasi_jadwal_log
 * dengan jenis baru supaya tidak perlu migrasi/tabel baru.
 */
class AbsensiBelumInputNotifikasiService
{
    public function __construct(
        protected ExpoPushService $push,
        protected DeviceTokenRepositoryInterface $deviceRepo,
    ) {}

    /** @return array{diproses:int,token_terkirim:int} */
    public function kirimReminderDue(): array
    {
        $now = WaktuSekolah::now();
        $hari = WaktuSekolah::hari($now);

        if (! $hari) {
            return ['diproses' => 0, 'token_terkirim' => 0]; // Minggu / hari libur
        }

        $tanggal = $now->toDateString();
        $grace = (int) config('sekolah.notif_belum_input_absensi_grace_minutes');
        $batasBawah = $now->copy()->subMinutes($grace);

        $jadwals = JadwalKbm::with([
            'guru:id,nama,gelar_depan,gelar_belakang,user_id',
            'mataPelajaran:id,nama',
            'rombel:id,nama,tingkat',
        ])
            ->where('hari', $hari)
            ->whereTime('jam_selesai', '<=', $batasBawah->format('H:i:s'))
            ->get();

        $diproses = 0;
        $tokenTerkirim = 0;

        foreach ($jadwals as $jadwal) {
            if (KalenderLibur::isLibur($tanggal, $jadwal->lembaga_id)) {
                continue;
            }

            $sudahAbsen = Absensi::where('rombel_id', $jadwal->rombel_id)
                ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
                ->whereDate('tanggal', $tanggal)
                ->exists();

            if ($sudahAbsen) {
                continue;
            }

            $sudahDiingatkan = NotifikasiJadwalLog::where('jadwal_kbm_id', $jadwal->id)
                ->whereDate('tanggal', $tanggal)
                ->where('jenis', 'belum_input_absensi')
                ->exists();

            if ($sudahDiingatkan) {
                continue;
            }

            $tokens = $jadwal->guru?->user_id
                ? $this->deviceRepo->tokensForUser($jadwal->guru->user_id)
                : [];

            $jamSelesai = substr((string) $jadwal->jam_selesai, 0, 5);
            $kelas = trim("{$jadwal->rombel?->tingkat} {$jadwal->rombel?->nama}");
            $mapel = $jadwal->mataPelajaran?->nama ?? 'mata pelajaran';

            $hasil = $this->push->sendToTokens(
                $tokens,
                'Belum Input Absensi',
                "Kelas {$kelas} — {$mapel} sudah selesai pukul {$jamSelesai}, absensi siswa belum diisi.",
                [
                    'type' => 'reminder_belum_input_absensi',
                    'jadwal_id' => $jadwal->id,
                    'rombel_id' => $jadwal->rombel_id,
                    'mapel_id' => $jadwal->mata_pelajaran_id,
                ]
            );

            // Catat log agar tidak dikirim ganda (unique constraint juga menjaga).
            try {
                NotifikasiJadwalLog::create([
                    'jadwal_kbm_id' => $jadwal->id,
                    'guru_id' => $jadwal->guru_id,
                    'tanggal' => $tanggal,
                    'jenis' => 'belum_input_absensi',
                    'jumlah_token' => count($tokens),
                    'sent_at' => now(),
                ]);
            } catch (QueryException $e) {
                continue; // sudah tercatat oleh proses lain; jangan hitung
            }

            foreach ($hasil['invalid_tokens'] ?? [] as $tokenInvalid) {
                $this->deviceRepo->forget($tokenInvalid);
            }

            $diproses++;
            $tokenTerkirim += $hasil['sent'] ?? 0;
        }

        return ['diproses' => $diproses, 'token_terkirim' => $tokenTerkirim];
    }
}
