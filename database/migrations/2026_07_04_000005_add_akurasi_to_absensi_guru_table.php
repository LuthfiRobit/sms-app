<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->unsignedInteger('akurasi_masuk_m')->nullable()->after('jarak_masuk_m');
            $table->unsignedInteger('akurasi_pulang_m')->nullable()->after('jarak_pulang_m');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->dropColumn(['akurasi_masuk_m', 'akurasi_pulang_m']);
        });
    }
};
