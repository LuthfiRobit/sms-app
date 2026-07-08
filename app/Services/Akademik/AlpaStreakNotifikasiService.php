<?php

namespace App\Services\Akademik;

use App\Jobs\KirimAbsensiWhatsappJob;
use App\Models\Akademik\AlpaStreakNotifikasiLog;
use App\Models\Peserta\Peserta;
use App\Models\Peserta\PesertaOrangTua;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Deteksi alpa berturut-turut per (peserta, mata_pelajaran) — bukan lintas
 * mapel harian, karena Absensi memang dimodelkan per rombel+mapel+tanggal.
 * Dipanggil dari dalam KelasMobileService::kirimNotifikasiSelesai() (bukan
 * simpanAbsensi()) supaya hanya jalan saat guru menandai sesi benar-benar
 * final, dan bisa memanfaatkan eager-load detail.peserta.orangTua yang
 * sudah ada di titik itu.
 */
class AlpaStreakNotifikasiService
{
    public function __construct(protected AkademikSettingService $settingService) {}

    public function cekDanNotifikasi(Peserta $peserta, int $mapelId, int $lembagaId, string $mapelNama): void
    {
        $threshold = (int) $this->settingService->get($lembagaId)->alpa_beruntun_threshold;

        [$panjangStreak, $tanggalMulai] = $this->hitungStreak($peserta->id, $mapelId);

        if ($panjangStreak < $threshold || ! $tanggalMulai) {
            return;
        }

        $wali = $this->resolveWaliKontak($peserta);
        if (! $wali) {
            return;
        }

        // Coba catat episode ini — kalau sudah pernah tercatat (unique
        // constraint), episode ini sudah pernah dinotif, jangan kirim lagi.
        try {
            AlpaStreakNotifikasiLog::create([
                'peserta_id' => $peserta->id,
                'mata_pelajaran_id' => $mapelId,
                'streak_mulai_tanggal' => $tanggalMulai,
                'streak_length_saat_kirim' => $panjangStreak,
                'sent_at' => now(),
            ]);
        } catch (QueryException $e) {
            return;
        }

        $pesan = $this->buildPesanAlpaStreak($wali->nama, $peserta->nama_lengkap, $panjangStreak, $mapelNama, $tanggalMulai);

        // dispatchSync() — synchronous, sama seperti notifikasi absensi rutin
        // (lihat KirimAbsensiWhatsappJob). Gagal kirim TIDAK menghapus log
        // yang sudah dibuat, supaya tidak retry-spam tiap sesi berikutnya
        // kalau nomornya memang bermasalah.
        try {
            KirimAbsensiWhatsappJob::dispatchSync($peserta->id, $peserta->nama_lengkap, $wali->no_hp, $pesan);
        } catch (\Throwable $e) {
            // sudah tercatat di log job itu sendiri; tidak perlu ditangani di sini.
        }
    }

    /**
     * Hitung berapa banyak status 'alpa' berturut-turut dari sesi TERBARU
     * mundur, untuk peserta+mapel ini, sampai ketemu status lain (atau
     * mentok guard rail 50 baris — bukan batas bisnis, cuma pengaman query).
     *
     * @return array{0:int,1:?string} [panjang streak, tanggal mulai streak]
     */
    protected function hitungStreak(int $pesertaId, int $mapelId): array
    {
        $rows = DB::table('absensi_detail')
            ->join('absensi', 'absensi.id', '=', 'absensi_detail.absensi_id')
            ->where('absensi_detail.peserta_id', $pesertaId)
            ->where('absensi.mata_pelajaran_id', $mapelId)
            ->orderByDesc('absensi.tanggal')
            ->limit(50)
            ->get(['absensi_detail.status', 'absensi.tanggal']);

        $panjang = 0;
        $tanggalMulai = null;

        foreach ($rows as $row) {
            if ($row->status !== 'alpa') {
                break;
            }
            $panjang++;
            $tanggalMulai = $row->tanggal;
        }

        return [$panjang, $tanggalMulai];
    }

    /** Prioritas kontak: wali > ayah > ibu — reuse logic KelasMobileService::resolveWaliKontak(). */
    protected function resolveWaliKontak(Peserta $peserta): ?PesertaOrangTua
    {
        $adaNoHp = fn ($tipe) => $peserta->orangTua->first(fn ($o) => $o->tipe === $tipe && filled($o->no_hp));

        return $adaNoHp(PesertaOrangTua::TIPE_WALI)
            ?? $adaNoHp(PesertaOrangTua::TIPE_AYAH)
            ?? $adaNoHp(PesertaOrangTua::TIPE_IBU);
    }

    protected function buildPesanAlpaStreak(string $namaWali, string $namaSiswa, int $panjangStreak, string $mapelNama, string $tanggalMulai): string
    {
        $tanggalFormatted = Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM YYYY');

        return "Disampaikan kepada Bapak/Ibu {$namaWali}, bahwa ananda {$namaSiswa} tercatat ALPA (tanpa keterangan) sebanyak {$panjangStreak}x berturut-turut pada mata pelajaran {$mapelNama}, terhitung sejak {$tanggalFormatted}. Mohon menjadi perhatian dan konfirmasi Bapak/Ibu. Terima kasih.";
    }
}
