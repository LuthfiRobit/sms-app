<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            // users.id_user bertipe INT UNSIGNED → kolom harus unsignedInteger.
            $table->unsignedInteger('user_id');
            $table->string('token', 512);
            $table->enum('platform', ['android', 'ios', 'web'])->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id_user')->on('users')->cascadeOnDelete();
            $table->unique('token');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
