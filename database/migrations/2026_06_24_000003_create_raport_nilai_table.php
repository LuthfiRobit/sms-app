<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raport_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengajuan_raport_id');
            $table->foreign('pengajuan_raport_id')->references('id')->on('pengajuan_raport')->cascadeOnDelete();
            $table->unsignedBigInteger('peserta_id');
            $table->foreign('peserta_id')->references('id')->on('peserta')->cascadeOnDelete();
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajaran')->cascadeOnDelete();
            $table->decimal('nilai_harian', 5, 2)->nullable();
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat', 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->unique(['pengajuan_raport_id', 'peserta_id', 'mata_pelajaran_id'], 'raport_nilai_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_nilai');
    }
};
