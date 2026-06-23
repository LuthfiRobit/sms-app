<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raport_absensi_rekap', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengajuan_raport_id');
            $table->foreign('pengajuan_raport_id')->references('id')->on('pengajuan_raport')->cascadeOnDelete();
            $table->unsignedBigInteger('peserta_id');
            $table->foreign('peserta_id')->references('id')->on('peserta')->cascadeOnDelete();
            $table->integer('hadir')->default(0);
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpa')->default(0);
            $table->unique(['pengajuan_raport_id', 'peserta_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_absensi_rekap');
    }
};
