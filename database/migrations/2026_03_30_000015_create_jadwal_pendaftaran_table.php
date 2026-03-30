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
        Schema::create('jadwal_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jalur_pendaftaran_id')->constrained('jalur_pendaftaran')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->enum('tipe', ['pendaftaran', 'seleksi', 'pengumuman', 'daftar_ulang']);
            $table->dateTime('mulai');
            $table->dateTime('selesai');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['jalur_pendaftaran_id', 'tipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pendaftaran');
    }
};
