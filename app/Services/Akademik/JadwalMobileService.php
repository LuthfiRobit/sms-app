<?php

namespace App\Services\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use App\Support\WaktuSekolah;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Penyaji jadwal mengajar untuk aplikasi mobile guru (read-only).
 */
class JadwalMobileService
{
    /** Jadwal mengajar guru untuk hari ini, lengkap dengan status & flag absensi. */
    public function hariIni(Guru $guru): array
    {
        $now = WaktuSekolah::now();
        $hari = WaktuSekolah::hari($now);
        $tanggal = $now->toDateString();

        if (! $hari) {
            return ['hari' => null, 'tanggal' => $tanggal, 'jadwal' => []];
        }

        $jadwals = JadwalKbm::with(['mataPelajaran:id,nama,kode', 'rombel:id,nama,tingkat'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        // Kombinasi rombel+mapel yang absensinya sudah dibuat hari ini.
        $absensiKeys = DB::table('absensi')
            ->where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggal)
            ->get(['rombel_id', 'mata_pelajaran_id'])
            ->map(fn ($r) => $r->rombel_id.'-'.$r->mata_pelajaran_id)
            ->flip();

        $jadwal = $jadwals->map(fn ($j) => $this->format($j, $now, $tanggal, $absensiKeys))->all();

        return ['hari' => $hari, 'tanggal' => $tanggal, 'jadwal' => $jadwal];
    }

    /** Jadwal mengajar guru sepekan, dikelompokkan per hari. */
    public function mingguan(Guru $guru): array
    {
        return JadwalKbm::with(['mataPelajaran:id,nama', 'rombel:id,nama,tingkat'])
            ->where('guru_id', $guru->id)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari')
            ->map(fn ($grup) => $grup->map(fn ($j) => [
                'id' => $j->id,
                'jam_ke' => $j->jam_ke,
                'jam_mulai' => substr((string) $j->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $j->jam_selesai, 0, 5),
                'mata_pelajaran' => $j->mataPelajaran?->nama,
                'rombel' => trim("{$j->rombel?->tingkat} {$j->rombel?->nama}"),
                'ruangan' => $j->ruangan,
            ])->values())
            ->all();
    }

    private function format(JadwalKbm $j, Carbon $now, string $tanggal, $absensiKeys): array
    {
        $tz = config('sekolah.timezone');
        $mulai = Carbon::parse($tanggal.' '.$j->jam_mulai, $tz);
        $selesai = Carbon::parse($tanggal.' '.$j->jam_selesai, $tz);

        $status = $now->gt($selesai)
            ? 'selesai'
            : ($now->gte($mulai) ? 'berlangsung' : 'akan_datang');

        return [
            'id' => $j->id,
            'jam_ke' => $j->jam_ke,
            'jam_mulai' => substr((string) $j->jam_mulai, 0, 5),
            'jam_selesai' => substr((string) $j->jam_selesai, 0, 5),
            'mata_pelajaran' => $j->mataPelajaran?->nama,
            'rombel' => trim("{$j->rombel?->tingkat} {$j->rombel?->nama}"),
            'rombel_id' => $j->rombel_id,
            'ruangan' => $j->ruangan,
            'status' => $status,
            // Penanda untuk gating Fase 3: nilai baru bisa dibuka jika true.
            'absensi_dibuat' => isset($absensiKeys[$j->rombel_id.'-'.$j->mata_pelajaran_id]),
        ];
    }
}
