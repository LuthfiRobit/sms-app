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
        Schema::table('model_pembelajaran', function (Blueprint $table) {
            // Label & contoh singkat untuk satu poin "artefak khas" di RPP (mis.
            // "Rumusan Masalah" untuk PBL, "Perencanaan Proyek" untuk PjBL) — beda
            // dari sintaks/Inti yang menjabarkan urutan kegiatan, ini menampung
            // konten substansi yang jadi pijakan kegiatan tsb. Nullable: model
            // custom (mis. LOK-R) boleh tidak punya artefak khas — poinnya
            // otomatis disembunyikan kalau kosong.
            $table->string('label_artefak', 150)->nullable()->after('deskripsi');
            $table->text('deskripsi_artefak')->nullable()->after('label_artefak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_pembelajaran', function (Blueprint $table) {
            $table->dropColumn(['label_artefak', 'deskripsi_artefak']);
        });
    }
};
