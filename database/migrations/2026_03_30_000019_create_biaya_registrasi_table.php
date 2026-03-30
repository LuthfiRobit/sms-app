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
        Schema::create('biaya_registrasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jalur_pendaftaran_id')->constrained('jalur_pendaftaran')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->restrictOnDelete();
            $table->string('nama', 100);
            $table->decimal('nominal', 12, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->index(['jalur_pendaftaran_id', 'tahun_pelajaran_id'], 'br_jalur_tp_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_registrasi');
    }
};
