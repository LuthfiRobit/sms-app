<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->boolean('is_koreksi_manual')->default(false)->after('flag_mock_location');
            $table->unsignedInteger('dikoreksi_oleh')->nullable()->after('is_koreksi_manual');
            $table->foreign('dikoreksi_oleh')->references('id_user')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->dropForeign(['dikoreksi_oleh']);
            $table->dropColumn(['is_koreksi_manual', 'dikoreksi_oleh']);
        });
    }
};
