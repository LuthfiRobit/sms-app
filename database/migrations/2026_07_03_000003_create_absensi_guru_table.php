<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('lembaga_id')->constrained('lembaga')->cascadeOnDelete();
            $table->date('tanggal');

            // ── Absen masuk ───────────────────────────────────────────────
            $table->time('jam_masuk')->nullable();
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('lng_masuk', 10, 7)->nullable();
            $table->unsignedInteger('jarak_masuk_m')->nullable(); // hasil hitung server (Haversine)
            $table->string('selfie_masuk', 500)->nullable();

            // ── Absen pulang ──────────────────────────────────────────────
            $table->time('jam_pulang')->nullable();
            $table->decimal('lat_pulang', 10, 7)->nullable();
            $table->decimal('lng_pulang', 10, 7)->nullable();
            $table->unsignedInteger('jarak_pulang_m')->nullable();
            $table->string('selfie_pulang', 500)->nullable();

            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->boolean('flag_mock_location')->default(false); // deteksi fake-GPS
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal']); // maksimal 1 record per guru per hari
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_guru');
    }
};
