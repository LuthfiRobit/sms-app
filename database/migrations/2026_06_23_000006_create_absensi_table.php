<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained('rombel')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajaran')->nullOnDelete();
            $table->date('tanggal');
            $table->tinyInteger('jam_ke')->unsigned()->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['rombel_id', 'tanggal', 'mata_pelajaran_id'], 'uq_absensi_rombel_tgl_mapel');
        });

        Schema::create('absensi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absensi_id')->constrained('absensi')->cascadeOnDelete();
            $table->foreignId('peserta_id')->constrained('peserta')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
            $table->string('keterangan', 255)->nullable();
            $table->unique(['absensi_id', 'peserta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_detail');
        Schema::dropIfExists('absensi');
    }
};
