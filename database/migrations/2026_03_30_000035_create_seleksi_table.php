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
        Schema::create('seleksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            
            $table->unsignedInteger('reviewer_id')->nullable();
            $table->foreign('reviewer_id')->references('id_user')->on('users')->restrictOnDelete();
            
            $table->string('model_penilaian', 50);
            $table->decimal('nilai', 5, 2)->default(0.00);
            $table->decimal('bobot', 3, 2)->default(1.00);
            $table->text('keterangan')->nullable();
            $table->dateTime('waktu_nilai')->nullable();
            $table->timestamps();

            $table->index(['pendaftaran_id'], 'seleksi_pendaftaran_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seleksi');
    }
};
