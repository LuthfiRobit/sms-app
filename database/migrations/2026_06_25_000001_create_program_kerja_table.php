<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembaga_id');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->cascadeOnDelete();
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->string('bidang', 50); // kesiswaan | sarpras | humas | kurikulum | umum
            $table->string('nama_program', 255);
            $table->text('deskripsi')->nullable();
            $table->string('tujuan', 500)->nullable();
            $table->enum('status', ['draft', 'diajukan', 'diverifikasi', 'ditolak', 'disetujui', 'aktif', 'selesai'])->default('draft');
            // Pengaju
            $table->unsignedInteger('dibuat_oleh')->nullable();
            $table->foreign('dibuat_oleh')->references('id_user')->on('users')->nullOnDelete();
            $table->timestamp('diajukan_at')->nullable();
            $table->text('catatan_pengajuan')->nullable();
            // Verifikasi (level 1)
            $table->unsignedInteger('diverifikasi_by')->nullable();
            $table->foreign('diverifikasi_by')->references('id_user')->on('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            // Approval (level 2 - Kepala Sekolah)
            $table->unsignedInteger('disetujui_by')->nullable();
            $table->foreign('disetujui_by')->references('id_user')->on('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('catatan_approval')->nullable();
            // Rejection tracking
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_kerja');
    }
};
