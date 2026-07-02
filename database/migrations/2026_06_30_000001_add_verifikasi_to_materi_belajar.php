<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Map existing values before changing enum
        DB::table('materi_belajar')
            ->where('status', 'aktif')
            ->update(['status' => 'disetujui']);

        DB::table('materi_belajar')
            ->where('status', 'nonaktif')
            ->update(['status' => 'ditolak']);

        Schema::table('materi_belajar', function (Blueprint $table) {
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending')->change();
            $table->text('catatan_revisi')->nullable()->after('status');
            $table->unsignedInteger('diverifikasi_by')->nullable()->after('catatan_revisi');
            $table->timestamp('diverifikasi_at')->nullable()->after('diverifikasi_by');
        });
    }

    public function down(): void
    {
        Schema::table('materi_belajar', function (Blueprint $table) {
            $table->dropColumn(['catatan_revisi', 'diverifikasi_by', 'diverifikasi_at']);
        });

        DB::table('materi_belajar')
            ->where('status', 'disetujui')
            ->update(['status' => 'aktif']);

        DB::table('materi_belajar')
            ->whereIn('status', ['pending', 'ditolak'])
            ->update(['status' => 'nonaktif']);

        Schema::table('materi_belajar', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->change();
        });
    }
};
