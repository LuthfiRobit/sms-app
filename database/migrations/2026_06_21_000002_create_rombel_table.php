<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rombel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran')->cascadeOnDelete();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->tinyInteger('tingkat')->unsigned(); // 1-6 (MI), 7-9 (MTs/SMP), 10-12 (MA/SMK)
            $table->string('nama', 50);                // "VII-A", "X IPA 1", "XII TJKT 2"
            $table->string('wali_kelas', 255)->nullable();
            $table->tinyInteger('kapasitas')->unsigned()->default(32);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rombel');
    }
};
