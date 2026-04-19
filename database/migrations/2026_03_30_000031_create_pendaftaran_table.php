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
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->string('no_pendaftaran', 20)->unique();
            $table->foreignId('peserta_id')->constrained('peserta')->restrictOnDelete();
            $table->foreignId('jalur_pendaftaran_id')->constrained('jalur_pendaftaran')->restrictOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->restrictOnDelete();
            $table->enum('status', ['draft', 'submit', 'verifikasi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_tetap'])->default('draft');
            $table->dateTime('tanggal_daftar')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            
            // verified_by mengacu ke users.id_user yang tipenya unsignedInteger
            $table->unsignedInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id_user')->on('users')->restrictOnDelete();
            
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['peserta_id', 'tahun_pelajaran_id'], 'pd_peserta_tp_idx');
            $table->index(['jalur_pendaftaran_id', 'status'], 'pd_jalur_status_idx');
            $table->index(['no_pendaftaran']);
            $table->index(['status', 'jalur_pendaftaran_id', 'tahun_pelajaran_id'], 'idx_pendaftaran_status_jalur_tahun');
            $table->index(['peserta_id', 'jalur_pendaftaran_id', 'tahun_pelajaran_id', 'status'], 'idx_pendaftaran_peserta_jalur_tahun');
            $table->index(['tanggal_daftar', 'status'], 'idx_pendaftaran_tanggal_daftar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
