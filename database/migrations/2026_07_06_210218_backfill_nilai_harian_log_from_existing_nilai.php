<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Supaya nilai harian yang sudah diinput sebelum sistem riwayat ini ada
     * tidak "hilang" begitu nilai_harian mulai dihitung otomatis dari
     * nilai_harian_log — tiap baris `nilai` yang sudah punya nilai_harian
     * dikonversi jadi satu entri log awal, ditandai jelas sebagai migrasi.
     */
    public function up(): void
    {
        $rows = DB::table('nilai')->whereNotNull('nilai_harian')->get();

        $now = now();
        $inserts = $rows->map(fn ($n) => [
            'lembaga_id' => $n->lembaga_id,
            'rombel_id' => $n->rombel_id,
            'peserta_id' => $n->peserta_id,
            'mata_pelajaran_id' => $n->mata_pelajaran_id,
            'semester_id' => $n->semester_id,
            'tahun_pelajaran_id' => $n->tahun_pelajaran_id,
            'tanggal' => $n->updated_at ? \Illuminate\Support\Carbon::parse($n->updated_at)->toDateString() : $now->toDateString(),
            'pertemuan_ke' => null,
            'keterangan' => 'Nilai Awal (sebelum sistem riwayat per sesi)',
            'nilai' => $n->nilai_harian,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if (! empty($inserts)) {
            DB::table('nilai_harian_log')->insert($inserts);
        }
    }

    public function down(): void
    {
        DB::table('nilai_harian_log')->where('keterangan', 'Nilai Awal (sebelum sistem riwayat per sesi)')->delete();
    }
};
