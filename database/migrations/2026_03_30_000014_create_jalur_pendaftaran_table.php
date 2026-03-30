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
        Schema::create('jalur_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembukaan_ppdb_id')->constrained('pembukaan_ppdb')->cascadeOnDelete();
            $table->string('kode_jalur', 20);
            $table->string('nama', 50);
            $table->text('deskripsi')->nullable();
            $table->integer('kuota')->default(0);
            $table->integer('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('nonaktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pembukaan_ppdb_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jalur_pendaftaran');
    }
};
