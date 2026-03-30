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
        Schema::create('hasil_seleksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->unique('hs_pendaftaran_unique')->constrained('pendaftaran')->cascadeOnDelete();
            $table->decimal('total_nilai', 5, 2)->default(0.00);
            $table->enum('status_kelulusan', ['lulus', 'tidak_lulus', 'cadangan'])->default('tidak_lulus');
            $table->integer('peringkat')->nullable();
            
            $table->unsignedInteger('reviewer_id')->nullable();
            $table->foreign('reviewer_id')->references('id_user')->on('users')->restrictOnDelete();
            
            $table->dateTime('waktu_pengumuman')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_seleksi');
    }
};
