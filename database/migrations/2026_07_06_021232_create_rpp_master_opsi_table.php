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
        Schema::create('rpp_master_opsi', function (Blueprint $table) {
            $table->id();
            // Satu tabel dipakai ulang untuk semua poin bertipe pilih_master —
            // 'kategori' membedakan kelompok opsinya (mis. dimensi_profil_lulusan,
            // topik_panca_cinta), bebas ditambah admin lewat rpp_poin.master_kategori
            // tanpa migrasi baru.
            $table->string('kategori', 100);
            $table->string('nama', 255);
            $table->unsignedInteger('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_master_opsi');
    }
};
