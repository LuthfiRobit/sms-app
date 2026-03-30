<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kuota_jurusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->restrictOnDelete();
            $table->foreignId('jalur_pendaftaran_id')->constrained('jalur_pendaftaran')->cascadeOnDelete();
            $table->foreignId('jurusan_id')->constrained('jurusan')->restrictOnDelete();
            $table->integer('kuota')->default(0);
            $table->integer('terisi')->default(0);
            $table->timestamps();

            $table->unique(['tahun_pelajaran_id', 'jalur_pendaftaran_id', 'jurusan_id'], 'kj_tp_jalur_jurusan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota_jurusan');
    }
};
