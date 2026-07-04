<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembaga', function (Blueprint $table) {
            // Titik acuan geofence absensi guru (koordinat sekolah + radius toleransi).
            $table->decimal('latitude', 10, 7)->nullable()->after('logo');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedSmallInteger('radius_meter')->default(100)->after('longitude');
            // Batas jam masuk; lewat dari ini absen dihitung "terlambat".
            $table->time('jam_masuk_batas')->nullable()->after('radius_meter');
        });
    }

    public function down(): void
    {
        Schema::table('lembaga', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meter', 'jam_masuk_batas']);
        });
    }
};
