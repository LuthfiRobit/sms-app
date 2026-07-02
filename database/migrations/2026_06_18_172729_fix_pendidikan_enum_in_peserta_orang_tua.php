<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peserta_orang_tua', function (Blueprint $table) {
            $table->enum('pendidikan', ['SD','SMP','SMA','D1','D2','D3','S1','S2','S3','Tidak_Sekolah'])
                ->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('peserta_orang_tua', function (Blueprint $table) {
            $table->enum('pendidikan', ['SD','SMP','SMA','D3','S1','S2','S3','Tidak_ada'])
                ->nullable()->default(null)->change();
        });
    }
};
