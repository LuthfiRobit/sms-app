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
        Schema::table('absensi', function (Blueprint $table) {
            // Penanda "notifikasi WhatsApp ke wali sudah dikirim utk sesi ini" —
            // mencegah guru tidak sengaja mengetuk "Selesai Mengajar" dua kali
            // dan mengirim pesan dobel ke orang tua siswa.
            $table->timestamp('notifikasi_terkirim_at')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('notifikasi_terkirim_at');
        });
    }
};
