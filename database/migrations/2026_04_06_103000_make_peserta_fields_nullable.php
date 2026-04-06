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
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('nisn', 10)->nullable()->change();
            $table->string('nik', 16)->nullable()->change();
            $table->string('nama_lengkap', 100)->nullable()->change();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->change();
            $table->string('tempat_lahir', 50)->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->string('nisn', 10)->nullable(false)->change();
            $table->string('nik', 16)->nullable(false)->change();
            $table->string('nama_lengkap', 100)->nullable(false)->change();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable(false)->change();
            $table->string('tempat_lahir', 50)->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])->nullable(false)->change();
        });
    }
};
