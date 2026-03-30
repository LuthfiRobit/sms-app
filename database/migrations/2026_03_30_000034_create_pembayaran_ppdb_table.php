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
        Schema::create('pembayaran_ppdb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->restrictOnDelete();
            $table->foreignId('biaya_registrasi_id')->constrained('biaya_registrasi')->restrictOnDelete();
            $table->string('metode', 50)->nullable();
            $table->enum('status', ['pending', 'paid', 'expired', 'failed', 'refund'])->default('pending');
            $table->string('snap_token', 255)->nullable();
            $table->string('order_id', 100)->unique()->nullable();
            $table->decimal('amount', 12, 2);
            $table->dateTime('waktu_bayar')->nullable();
            $table->string('bukti_bayar', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();

            $table->index(['pendaftaran_id', 'status'], 'pp_pendaftaran_status_idx');
            $table->index(['order_id'], 'pp_order_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_ppdb');
    }
};
