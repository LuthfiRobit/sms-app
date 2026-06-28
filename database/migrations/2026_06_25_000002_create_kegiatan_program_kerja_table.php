<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_program_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_kerja_id');
            $table->foreign('program_kerja_id')->references('id')->on('program_kerja')->cascadeOnDelete();
            $table->string('nama_kegiatan', 255);
            $table->text('deskripsi')->nullable();
            $table->string('penanggung_jawab', 255)->nullable(); // text name
            $table->text('target')->nullable(); // target yang ingin dicapai
            $table->text('indikator')->nullable(); // indikator keberhasilan
            $table->decimal('anggaran', 15, 2)->default(0); // budget
            $table->tinyInteger('bulan_mulai')->unsigned()->default(1); // 1-12
            $table->tinyInteger('bulan_selesai')->unsigned()->default(12); // 1-12
            // Realisasi
            $table->decimal('realisasi_anggaran', 15, 2)->nullable();
            $table->tinyInteger('realisasi_persen')->unsigned()->default(0); // 0-100
            $table->enum('status_kegiatan', ['belum', 'proses', 'selesai', 'dibatalkan'])->default('belum');
            $table->text('catatan_realisasi')->nullable();
            $table->tinyInteger('urutan')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_program_kerja');
    }
};
