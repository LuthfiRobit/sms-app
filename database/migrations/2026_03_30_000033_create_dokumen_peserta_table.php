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
        Schema::create('dokumen_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->foreignId('syarat_pendaftaran_id')->constrained('syarat_pendaftaran')->restrictOnDelete();
            $table->string('nama_file', 255);
            $table->string('path_file', 255);
            $table->string('mime_type', 50)->nullable();
            $table->integer('ukuran_file')->nullable();
            $table->enum('status_verifikasi', ['pending', 'valid', 'invalid'])->default('pending');
            $table->text('keterangan_verifikasi')->nullable();
            
            // verified_by mengacu ke users.id_user (unsignedInteger)
            $table->unsignedInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id_user')->on('users')->restrictOnDelete();
            
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->index(['pendaftaran_id', 'status_verifikasi'], 'dp_pendaftaran_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_peserta');
    }
};
