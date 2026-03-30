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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id_user')->on('users')->cascadeOnDelete();
            $table->enum('tipe', ['email', 'sms', 'inapp'])->default('inapp');
            $table->string('judul', 100);
            $table->text('isi');
            $table->enum('status', ['pending', 'terkirim', 'gagal'])->default('pending');
            $table->boolean('dibaca')->default(false);
            $table->dateTime('waktu_kirim')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'dibaca']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
