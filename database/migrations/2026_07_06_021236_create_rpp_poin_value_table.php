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
        Schema::create('rpp_poin_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rpp_id')->constrained('rpp')->cascadeOnDelete();
            // restrict — admin nonaktifkan poin yang sudah dipakai, bukan hapus permanen.
            $table->foreignId('rpp_poin_id')->constrained('rpp_poin')->restrictOnDelete();
            // Dua kolom nilai (bukan satu kolom polimorfik) supaya tidak ambigu —
            // mana yang dibaca ditentukan dari rpp_poin.tipe, bukan ditebak dari bentuk data.
            $table->text('value_teks')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->unique(['rpp_id', 'rpp_poin_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpp_poin_value');
    }
};
