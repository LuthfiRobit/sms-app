<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            // Kolom terpisah masuk/pulang — konsisten dengan pola jarak_masuk_m/
            // jarak_pulang_m yang sudah ada. Absen masuk & pulang dua kejadian
            // terpisah yang masing-masing diverifikasi sendiri; satu kolom
            // gabungan akan membuat hasil verifikasi masuk tertimpa saat pulang.
            $table->enum('face_verified_masuk', ['belum_dicek', 'cocok', 'tidak_cocok', 'tidak_terdaftar', 'layanan_error'])
                ->default('belum_dicek')->after('is_koreksi_manual');
            $table->float('face_confidence_masuk')->nullable()->after('face_verified_masuk');
            $table->boolean('face_liveness_ok_masuk')->nullable()->after('face_confidence_masuk');

            $table->enum('face_verified_pulang', ['belum_dicek', 'cocok', 'tidak_cocok', 'tidak_terdaftar', 'layanan_error'])
                ->default('belum_dicek')->after('face_liveness_ok_masuk');
            $table->float('face_confidence_pulang')->nullable()->after('face_verified_pulang');
            $table->boolean('face_liveness_ok_pulang')->nullable()->after('face_confidence_pulang');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->dropColumn([
                'face_verified_masuk', 'face_confidence_masuk', 'face_liveness_ok_masuk',
                'face_verified_pulang', 'face_confidence_pulang', 'face_liveness_ok_pulang',
            ]);
        });
    }
};
