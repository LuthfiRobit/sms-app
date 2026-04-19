<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Composite Indexes untuk PPDB
 *
 * Menambahkan composite index pada tabel-tabel yang sering di-join / di-filter
 * untuk menghilangkan full-table-scan pada DataTable berpaginasi.
 *
 * Index yang ditambahkan:
 *
 * 1. peserta                 — filter nama + soft-delete
 * 2. pendaftaran             — filter status, jalur, tahun, peserta
 * 3. pembayaran_ppdb         — filter status per pendaftaran
 * 4. hasil_seleksi           — ranking per jalur
 * 5. dokumen_peserta         — kelengkapan syarat per pendaftaran
 * 6. seleksi                 — nilai per pendaftaran
 */
return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------------
        // 1. peserta — DataTable list dengan filter nama + soft-delete
        // -----------------------------------------------------------------------
        Schema::table('peserta', function (Blueprint $table) {
            // DataTable search by name (LIKE '%...%') → partial index tidak didukung MySQL,
            // tapi index tunggal pada nama_lengkap + deleted_at membantu ORDER BY
            if (!$this->hasIndex('peserta', 'idx_peserta_nama_deleted')) {
                $table->index(['nama_lengkap', 'deleted_at'], 'idx_peserta_nama_deleted');
            }
            // Filter by agama dan kecamatan (dari alamat, tapi join sering dilakukan)
            if (!$this->hasIndex('peserta', 'idx_peserta_agama')) {
                $table->index('agama', 'idx_peserta_agama');
            }
        });

        // -----------------------------------------------------------------------
        // 2. pendaftaran — DataTable filter status, jalur, tahun, peserta + sort
        // -----------------------------------------------------------------------
        Schema::table('pendaftaran', function (Blueprint $table) {
            // Filter utama DataTable admin: status + jalur + tahun
            if (!$this->hasIndex('pendaftaran', 'idx_pendaftaran_status_jalur_tahun')) {
                $table->index(
                    ['status', 'jalur_pendaftaran_id', 'tahun_pelajaran_id'],
                    'idx_pendaftaran_status_jalur_tahun'
                );
            }
            // Validasi double-daftar: peserta_id + jalur + tahun + status
            if (!$this->hasIndex('pendaftaran', 'idx_pendaftaran_peserta_jalur_tahun')) {
                $table->index(
                    ['peserta_id', 'jalur_pendaftaran_id', 'tahun_pelajaran_id', 'status'],
                    'idx_pendaftaran_peserta_jalur_tahun'
                );
            }
            // ORDER BY tanggal_daftar (default sort DataTable)
            if (!$this->hasIndex('pendaftaran', 'idx_pendaftaran_tanggal_daftar')) {
                $table->index(['tanggal_daftar', 'status'], 'idx_pendaftaran_tanggal_daftar');
            }
        });

        // -----------------------------------------------------------------------
        // 3. pembayaran_ppdb — filter status per pendaftaran (idempotency + DataTable)
        // -----------------------------------------------------------------------
        Schema::table('pembayaran_ppdb', function (Blueprint $table) {
            if (!$this->hasIndex('pembayaran_ppdb', 'idx_pembayaran_pendaftaran_status')) {
                $table->index(['pendaftaran_id', 'status'], 'idx_pembayaran_pendaftaran_status');
            }
            if (!$this->hasIndex('pembayaran_ppdb', 'idx_pembayaran_order_id')) {
                $table->index('order_id', 'idx_pembayaran_order_id');
            }
        });

        // -----------------------------------------------------------------------
        // 4. hasil_seleksi — query ranking per jalur (via pendaftaran join)
        // -----------------------------------------------------------------------
        Schema::table('hasil_seleksi', function (Blueprint $table) {
            if (!$this->hasIndex('hasil_seleksi', 'idx_hasil_seleksi_pendaftaran_peringkat')) {
                $table->index(['pendaftaran_id', 'peringkat'], 'idx_hasil_seleksi_pendaftaran_peringkat');
            }
            if (!$this->hasIndex('hasil_seleksi', 'idx_hasil_seleksi_status')) {
                $table->index('status_kelulusan', 'idx_hasil_seleksi_status');
            }
        });

        // -----------------------------------------------------------------------
        // 5. dokumen_peserta — cek kelengkapan syarat per pendaftaran
        // -----------------------------------------------------------------------
        Schema::table('dokumen_peserta', function (Blueprint $table) {
            if (!$this->hasIndex('dokumen_peserta', 'idx_dokumen_peserta_pendaftaran_syarat')) {
                $table->index(
                    ['pendaftaran_id', 'syarat_pendaftaran_id'],
                    'idx_dokumen_peserta_pendaftaran_syarat'
                );
            }
            if (!$this->hasIndex('dokumen_peserta', 'idx_dokumen_peserta_status')) {
                $table->index(['pendaftaran_id', 'status_verifikasi'], 'idx_dokumen_peserta_status');
            }
        });

        // -----------------------------------------------------------------------
        // 6. seleksi — query nilai per pendaftaran (hitungRanking)
        // -----------------------------------------------------------------------
        Schema::table('seleksi', function (Blueprint $table) {
            if (!$this->hasIndex('seleksi', 'idx_seleksi_pendaftaran')) {
                $table->index('pendaftaran_id', 'idx_seleksi_pendaftaran');
            }
        });

        // -----------------------------------------------------------------------
        // 7. pendaftaran_field_value — join ke formulir field
        // -----------------------------------------------------------------------
        Schema::table('pendaftaran_field_value', function (Blueprint $table) {
            if (!$this->hasIndex('pendaftaran_field_value', 'idx_pfv_pendaftaran_field')) {
                $table->index(
                    ['pendaftaran_id', 'formulir_field_id'],
                    'idx_pfv_pendaftaran_field'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('peserta', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_peserta_nama_deleted');
            $table->dropIndexIfExists('idx_peserta_agama');
        });

        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_pendaftaran_status_jalur_tahun');
            $table->dropIndexIfExists('idx_pendaftaran_peserta_jalur_tahun');
            $table->dropIndexIfExists('idx_pendaftaran_tanggal_daftar');
        });

        Schema::table('pembayaran_ppdb', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_pembayaran_pendaftaran_status');
            $table->dropIndexIfExists('idx_pembayaran_order_id');
        });

        Schema::table('hasil_seleksi', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_hasil_seleksi_pendaftaran_peringkat');
            $table->dropIndexIfExists('idx_hasil_seleksi_status');
        });

        Schema::table('dokumen_peserta', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_dokumen_peserta_pendaftaran_syarat');
            $table->dropIndexIfExists('idx_dokumen_peserta_status');
        });

        Schema::table('seleksi', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_seleksi_pendaftaran');
        });

        Schema::table('pendaftaran_field_value', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_pfv_pendaftaran_field');
        });
    }

    /**
     * Helper: Cek apakah index sudah ada (idempotent migration).
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return !empty($indexes);
    }
};
