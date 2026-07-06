<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat nilai harian PER SESI — beda dari `nilai.nilai_harian` yang
     * cuma satu angka per semester (dan dulu tertimpa tiap simpan). Tiap
     * baris di sini adalah satu skor dari satu sesi/tugas; `nilai.nilai_harian`
     * sekarang jadi nilai TURUNAN (rata-rata dari baris-baris ini), dihitung
     * ulang otomatis oleh KelasMobileService setiap ada entri baru — bukan
     * lagi diinput manual langsung dari alur "masuk kelas" mobile.
     */
    public function up(): void
    {
        Schema::create('nilai_harian_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained('rombel')->cascadeOnDelete();
            $table->foreignId('peserta_id')->constrained('peserta')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semester')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedTinyInteger('pertemuan_ke')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->decimal('nilai', 5, 2);
            $table->timestamps();
            $table->index(
                ['rombel_id', 'mata_pelajaran_id', 'semester_id', 'peserta_id'],
                'idx_nilai_harian_log_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_harian_log');
    }
};
