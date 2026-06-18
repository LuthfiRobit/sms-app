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
        DB::statement("ALTER TABLE peserta_orang_tua MODIFY COLUMN pendidikan ENUM(
            'SD','SMP','SMA','D1','D2','D3','S1','S2','S3','Tidak_Sekolah'
        ) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE peserta_orang_tua MODIFY COLUMN pendidikan ENUM(
            'SD','SMP','SMA','D3','S1','S2','S3','Tidak_ada'
        ) NULL DEFAULT NULL");
    }
};
