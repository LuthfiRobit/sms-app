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
        Schema::create('profil_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('npsn', 8)->unique();
            $table->string('nss', 12)->nullable();
            $table->string('nama_sekolah', 100);
            $table->enum('status_sekolah', ['Negeri', 'Swasta']);
            $table->string('bentuk_pendidikan', 20);
            $table->text('alamat');
            $table->bigInteger('desa_id')->unsigned()->nullable();
            $table->string('kode_pos', 5);
            $table->string('telepon', 20);
            $table->string('email', 100);
            $table->string('website', 100)->nullable();
            $table->string('kepala_sekolah', 100);
            $table->string('nip_kepsek', 18)->nullable();
            $table->string('logo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_sekolah');
    }
};
