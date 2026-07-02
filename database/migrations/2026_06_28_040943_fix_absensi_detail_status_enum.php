<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate any 'alpha' values to 'alpa' before altering the constraint
        DB::table('absensi_detail')
            ->where('status', 'alpha')
            ->update(['status' => 'alpa']);

        Schema::table('absensi_detail', function (Blueprint $table) {
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir')->change();
        });
    }

    public function down(): void
    {
        DB::table('absensi_detail')
            ->where('status', 'alpa')
            ->update(['status' => 'alpha']);

        Schema::table('absensi_detail', function (Blueprint $table) {
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpha'])->default('hadir')->change();
        });
    }
};
