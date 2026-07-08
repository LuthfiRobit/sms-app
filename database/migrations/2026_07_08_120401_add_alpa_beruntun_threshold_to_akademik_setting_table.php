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
        Schema::table('akademik_setting', function (Blueprint $table) {
            $table->unsignedTinyInteger('alpa_beruntun_threshold')->default(3)->after('kkm_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akademik_setting', function (Blueprint $table) {
            $table->dropColumn('alpa_beruntun_threshold');
        });
    }
};
