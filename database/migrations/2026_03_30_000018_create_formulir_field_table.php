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
        Schema::create('formulir_field', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulir_pendaftaran_id')->constrained('formulir_pendaftaran')->cascadeOnDelete();
            $table->string('kode_field', 50);
            $table->string('label', 100);
            $table->enum('tipe_field', ['text', 'number', 'date', 'select', 'file', 'textarea', 'radio', 'checkbox']);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_statis')->default(false);
            $table->string('dapodik_key', 50)->nullable();
            $table->integer('urutan')->default(0);
            $table->json('opsi')->nullable();
            $table->timestamps();

            $table->index(['formulir_pendaftaran_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulir_field');
    }
};
