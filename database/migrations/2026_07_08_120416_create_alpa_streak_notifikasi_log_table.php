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
        Schema::create('alpa_streak_notifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('peserta')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->date('streak_mulai_tanggal');
            $table->unsignedSmallInteger('streak_length_saat_kirim');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Kunci dedup: 1 notifikasi per EPISODE streak (episode baru dimulai
            // kalau streak_mulai_tanggal beda, artinya streak lama sudah putus
            // oleh status non-alpa).
            $table->unique(['peserta_id', 'mata_pelajaran_id', 'streak_mulai_tanggal'], 'uniq_alpa_streak_episode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alpa_streak_notifikasi_log');
    }
};
