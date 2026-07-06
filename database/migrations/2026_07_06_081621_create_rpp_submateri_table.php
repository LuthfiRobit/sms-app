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
        Schema::create('rpp_submateri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpp_id')->constrained('rpp')->cascadeOnDelete();
            // Tidak dibatasi jumlahnya — guru bisa tambah sebanyak yang dibutuhkan.
            // Inilah yang ditampilkan sebagai daftar materi per-sesi di app mobile guru,
            // menggantikan judul RPP yang cuma satu baris untuk keperluan itu.
            $table->string('teks', 255);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_submateri');
    }
};
