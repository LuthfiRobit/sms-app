<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_indikator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembaga_id');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->cascadeOnDelete();
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->string('nama_indikator', 255);
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['akademik', 'ppdb', 'program_kerja', 'sarpras', 'kesiswaan', 'humas', 'umum']);
            $table->string('satuan', 50)->default('%'); // %, siswa, kegiatan, dst
            $table->decimal('target', 10, 2)->default(100);
            $table->enum('sumber_data', ['manual', 'akademik_nilai', 'ppdb_pendaftar', 'ppdb_diterima', 'program_kerja', 'absensi'])->default('manual');
            $table->boolean('is_auto')->default(false); // auto-calculated from sumber_data
            $table->tinyInteger('urutan')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_indikator');
    }
};
