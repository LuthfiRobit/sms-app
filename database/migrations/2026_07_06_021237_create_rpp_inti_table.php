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
        Schema::create('rpp_inti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpp_id')->constrained('rpp')->cascadeOnDelete();
            $table->foreignId('model_pembelajaran_sintaks_id')->constrained('model_pembelajaran_sintaks')->restrictOnDelete();
            $table->json('konten')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique(['rpp_id', 'model_pembelajaran_sintaks_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_inti');
    }
};
