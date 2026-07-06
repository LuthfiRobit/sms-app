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
        Schema::create('model_pembelajaran_sintaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_pembelajaran_id')->constrained('model_pembelajaran')->cascadeOnDelete();
            // 3 fase ini tetap/fixed dari kerangka Pembelajaran Mendalam, bukan
            // per-model — setiap langkah sintaks model manapun masuk ke salah satunya.
            $table->enum('meta_fase', ['Memahami', 'Mengaplikasi', 'Merefleksi']);
            $table->string('nama_sintaks', 150);
            $table->unsignedInteger('urutan');
            $table->timestamps();

            $table->unique(['model_pembelajaran_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_pembelajaran_sintaks');
    }
};
