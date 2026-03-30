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
        Schema::create('peserta_orang_tua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('peserta')->cascadeOnDelete();
            $table->enum('tipe', ['ayah', 'ibu', 'wali']);
            $table->string('nama', 100);
            $table->string('nik', 16)->nullable();
            $table->string('pekerjaan', 50)->nullable();
            $table->string('penghasilan', 20)->nullable();
            $table->enum('pendidikan', ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3', 'Tidak_ada'])->nullable();
            $table->string('kebutuhan_khusus', 50)->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->timestamps();

            $table->index(['peserta_id', 'tipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_orang_tua');
    }
};
