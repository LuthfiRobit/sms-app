<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_realisasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpi_indikator_id');
            $table->foreign('kpi_indikator_id')->references('id')->on('kpi_indikator')->cascadeOnDelete();
            $table->string('periode', 100); // e.g. 'Semester 1 2025/2026'
            $table->decimal('nilai_realisasi', 10, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->unsignedInteger('dicatat_oleh')->nullable();
            $table->foreign('dicatat_oleh')->references('id_user')->on('users')->nullOnDelete();
            $table->timestamp('dicatat_at')->nullable();
            $table->unique(['kpi_indikator_id', 'periode']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_realisasi');
    }
};
