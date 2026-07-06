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
        Schema::create('rpp_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpp_bagian_id')->constrained('rpp_bagian')->cascadeOnDelete();
            // Kunci stabil dipakai internal (mis. cari poin "model_pembelajaran" untuk blok
            // Inti) — tidak berubah walau label ditampilkan diedit admin.
            $table->string('kode', 100)->unique();
            $table->string('label', 255);
            $table->enum('tipe', ['teks', 'teks_panjang', 'daftar_poin', 'pasangan_kolom', 'pilih_master', 'model_pembelajaran']);
            $table->string('kolom1_label', 100)->nullable();
            $table->string('kolom2_label', 100)->nullable();
            $table->string('master_kategori', 100)->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_poin');
    }
};
