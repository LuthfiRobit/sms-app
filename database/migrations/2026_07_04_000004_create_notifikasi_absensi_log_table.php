<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_absensi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 30)->default('belum_absen_masuk');
            $table->unsignedSmallInteger('jumlah_token')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['guru_id', 'tanggal', 'jenis'], 'uq_notif_absensi_harian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_absensi_log');
    }
};
