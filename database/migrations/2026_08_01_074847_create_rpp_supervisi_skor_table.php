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
        Schema::create('rpp_supervisi_skor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpp_supervisi_id')->constrained('rpp_supervisi')->cascadeOnDelete();
            $table->enum('instrumen', ['perencanaan', 'pelaksanaan', 'asesmen']);
            // Kunci stabil ke config('rpp_supervisi.instrumen.{instrumen}.kriteria.*.kode') — bukan
            // master data DB, jadi tidak ada FK; validasi kecocokan dilakukan di service/controller.
            $table->string('kode', 20);
            $table->unsignedTinyInteger('skor');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['rpp_supervisi_id', 'instrumen', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_supervisi_skor');
    }
};
