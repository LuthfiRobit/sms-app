<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_jadwal_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_kbm_id')->constrained('jadwal_kbm')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 30)->default('reminder'); // reminder sebelum mengajar
            $table->unsignedSmallInteger('jumlah_token')->default(0); // token tujuan saat dikirim
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Satu jadwal hanya dinotifikasi sekali per hari per jenis.
            $table->unique(['jadwal_kbm_id', 'tanggal', 'jenis'], 'uq_notif_jadwal_harian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_jadwal_log');
    }
};
