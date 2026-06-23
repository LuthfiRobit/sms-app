<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->nullable()->constrained('lembaga')->nullOnDelete();
            $table->string('kode', 20);
            $table->string('nama', 255);
            $table->enum('kelompok', ['wajib', 'peminatan', 'mulok'])->default('wajib');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->smallInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['lembaga_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_pelajaran');
    }
};
