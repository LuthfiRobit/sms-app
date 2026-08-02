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
        Schema::create('rpp_supervisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpp_id')->constrained('rpp')->cascadeOnDelete();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->date('tanggal_supervisi');

            // Kalau dikosongkan, ambil default dari profil_sekolah (kepala_sekolah/nip_kepsek)
            // saat ditampilkan — tidak dituliskan otomatis di sini supaya tetap akurat
            // walau profil sekolah berubah setelah supervisi ini dibuat.
            $table->string('nama_supervisor', 150)->nullable();
            $table->string('nip_supervisor', 30)->nullable();
            $table->string('jabatan_supervisor', 100)->nullable();

            // Catatan & rencana tindak lanjut dipisah per instrumen — meniru
            // dokumen sumber yang punya 3 blok "Catatan Khusus"/"Rencana Tindak
            // Lanjut" terpisah (satu per Perencanaan/Pelaksanaan/Asesmen).
            $table->text('catatan_perencanaan')->nullable();
            $table->text('rtl_perencanaan')->nullable();
            $table->text('catatan_pelaksanaan')->nullable();
            $table->text('rtl_pelaksanaan')->nullable();
            $table->text('catatan_asesmen')->nullable();
            $table->text('rtl_asesmen')->nullable();

            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['rpp_id', 'tanggal_supervisi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_supervisi');
    }
};
