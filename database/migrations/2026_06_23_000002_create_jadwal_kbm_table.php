<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_kbm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained('rombel')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->tinyInteger('jam_ke')->unsigned()->default(1);
            $table->string('ruangan', 50)->nullable();
            $table->timestamps();
            // Satu rombel tidak boleh ada dua jadwal di hari + jam yang sama
            $table->unique(['rombel_id', 'hari', 'jam_mulai'], 'uq_rombel_hari_jam');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_kbm');
    }
};
