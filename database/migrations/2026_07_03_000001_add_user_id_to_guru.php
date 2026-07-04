<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            // Jembatan akun login (users) ke record guru untuk aplikasi mobile.
            // users.id_user bertipe INT UNSIGNED, jadi kolom ini harus unsignedInteger
            // (bukan foreignId/bigint) agar FK tidak incompatible.
            // Nullable: guru boleh belum punya akun; NULL = belum bisa login mobile.
            $table->unsignedInteger('user_id')->nullable()->after('lembaga_id');
            $table->foreign('user_id')->references('id_user')->on('users')->nullOnDelete();
            $table->string('email', 100)->nullable()->after('nama');
            $table->string('no_hp', 20)->nullable()->after('email');
            $table->string('foto', 500)->nullable()->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'email', 'no_hp', 'foto']);
        });
    }
};
