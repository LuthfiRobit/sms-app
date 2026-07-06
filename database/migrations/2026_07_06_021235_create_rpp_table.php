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
        Schema::create('rpp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semester')->cascadeOnDelete();
            // restrict, bukan cascade/null — hapus master Model Pembelajaran tidak
            // boleh diam-diam merusak RPP yang sudah memakainya.
            $table->foreignId('model_pembelajaran_id')->constrained('model_pembelajaran')->restrictOnDelete();

            // Identitas header — sengaja BUKAN poin dinamis, karena ini akan selalu
            // ada di templat manapun dan dipakai untuk kolom judul/sortir di daftar.
            $table->string('fase_kelas', 100);
            $table->string('materi', 255);
            $table->string('alokasi_waktu', 100);

            // Verifikasi — pola & nama kolom sama persis dengan MateriBelajar.
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_revisi')->nullable();
            $table->unsignedInteger('diverifikasi_by')->nullable();
            $table->timestamp('diverifikasi_at')->nullable();

            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->timestamps();

            $table->index(['guru_id', 'mata_pelajaran_id', 'tahun_pelajaran_id', 'semester_id'], 'rpp_guru_mapel_tahun_semester_idx');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp');
    }
};
