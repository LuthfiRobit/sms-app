<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_raport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembaga_id');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->cascadeOnDelete();
            $table->unsignedBigInteger('rombel_id');
            $table->foreign('rombel_id')->references('id')->on('rombel')->cascadeOnDelete();
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('semester')->cascadeOnDelete();
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->unsignedInteger('dibuat_oleh')->nullable();
            $table->foreign('dibuat_oleh')->references('id_user')->on('users')->nullOnDelete();
            $table->enum('status', ['draft', 'diajukan', 'diverifikasi', 'ditolak', 'disetujui'])->default('draft');
            $table->text('catatan_pengajuan')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamp('diajukan_at')->nullable();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamp('disetujui_at')->nullable();
            $table->unsignedInteger('diverifikasi_by')->nullable();
            $table->foreign('diverifikasi_by')->references('id_user')->on('users')->nullOnDelete();
            $table->unsignedInteger('disetujui_by')->nullable();
            $table->foreign('disetujui_by')->references('id_user')->on('users')->nullOnDelete();
            $table->unique(['rombel_id', 'semester_id', 'tahun_pelajaran_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_raport');
    }
};
