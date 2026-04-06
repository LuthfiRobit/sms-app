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
        Schema::table('peserta_alamat', function (Blueprint $table) {
            $table->string('alamat', 255)->nullable()->change();
            $table->string('desa_kelurahan', 50)->nullable()->change();
            $table->string('kecamatan', 50)->nullable()->change();
            $table->string('kabupaten_kota', 50)->nullable()->change();
            $table->string('provinsi', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_alamat', function (Blueprint $table) {
            $table->string('alamat', 255)->nullable(false)->change();
            $table->string('desa_kelurahan', 50)->nullable(false)->change();
            $table->string('kecamatan', 50)->nullable(false)->change();
            $table->string('kabupaten_kota', 50)->nullable(false)->change();
            $table->string('provinsi', 50)->nullable(false)->change();
        });
    }
};
