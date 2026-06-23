<?php

namespace App\Dashboard\Widgets\Ppdb;

use App\Dashboard\DashboardWidget;
use App\Models\Ppdb\PembukaanPpdb;
use App\Models\Transaksi\PembayaranPpdb;

class PembukaanAktifWidget extends DashboardWidget
{
    public function id(): string    { return 'ppdb.pembukaan-aktif'; }
    public function title(): string { return 'PPDB Aktif & Pembayaran'; }
    public function size(): string  { return 'col-12 col-xl-5'; }
    public function order(): int    { return 16; }
    public function cacheTtl(): int { return 0; }  // Eloquent models tidak serializable

    protected function build(): array
    {
        $lid = app('active_lembaga_id');

        $pembukaanAktif = PembukaanPpdb::where('status', 'buka')
            ->when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->with(['lembaga:id,nama,kode,jenis', 'jalurPendaftaran:id,pembukaan_ppdb_id,nama'])
            ->withCount('jalurPendaftaran')
            ->get();

        $pendingPayment = PembayaranPpdb::with([
            'pendaftaran.peserta:id,nama_lengkap',
            'pendaftaran.lembaga:id,nama,kode',
        ])
            ->when($lid, fn ($q) => $q->whereHas(
                'pendaftaran',
                fn ($p) => $p->where('lembaga_id', $lid)
            ))
            ->where('status', 'pending')
            ->where('metode', 'manual')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return compact('pembukaanAktif', 'pendingPayment');
    }

    public function view(): string
    {
        return 'admin.dashboard.widgets.ppdb.pembukaan-aktif';
    }
}
