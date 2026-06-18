<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->foreignId('lembaga_id')->nullable()->after('id')->constrained('lembaga')->restrictOnDelete();
            $table->index('lembaga_id');
        });

        Schema::table('pembukaan_ppdb', function (Blueprint $table) {
            $table->foreignId('lembaga_id')->nullable()->after('id')->constrained('lembaga')->restrictOnDelete();
            $table->index('lembaga_id');
        });

        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->foreignId('lembaga_id')->nullable()->after('id')->constrained('lembaga')->restrictOnDelete();
            $table->index('lembaga_id');
        });
    }

    public function down(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropForeign(['lembaga_id']);
            $table->dropIndex(['lembaga_id']);
            $table->dropColumn('lembaga_id');
        });

        Schema::table('pembukaan_ppdb', function (Blueprint $table) {
            $table->dropForeign(['lembaga_id']);
            $table->dropIndex(['lembaga_id']);
            $table->dropColumn('lembaga_id');
        });

        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->dropForeign(['lembaga_id']);
            $table->dropIndex(['lembaga_id']);
            $table->dropColumn('lembaga_id');
        });
    }
};
