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
        Schema::create('pembukaan_ppdb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->restrictOnDelete();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->date('mulai');
            $table->date('selesai');
            $table->enum('status', ['buka', 'tutup', 'draft'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun_pelajaran_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembukaan_ppdb');
    }
};
