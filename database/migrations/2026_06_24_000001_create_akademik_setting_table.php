<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akademik_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembaga_id')->unique();
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->cascadeOnDelete();
            $table->boolean('allow_manual_nilai')->default(false);
            $table->decimal('bobot_harian', 5, 2)->default(40.00);
            $table->decimal('bobot_uts', 5, 2)->default(30.00);
            $table->decimal('bobot_uas', 5, 2)->default(30.00);
            $table->tinyInteger('kkm_default')->unsigned()->default(70);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akademik_setting');
    }
};
