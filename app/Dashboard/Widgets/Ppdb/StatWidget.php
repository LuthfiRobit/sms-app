<?php

namespace App\Dashboard\Widgets\Ppdb;

use App\Dashboard\DashboardWidget;
use App\Models\Master\Lembaga;
use App\Models\Ppdb\PembukaanPpdb;
use App\Models\Transaksi\Pendaftaran;
use App\Models\Transaksi\PembayaranPpdb;

class StatWidget extends DashboardWidget
{
    public function id(): string    { return 'ppdb.stat'; }
    public function title(): string { return 'Statistik PPDB'; }
    public function size(): string  { return 'col-12'; }
    public function order(): int    { return 5; }
    public function cacheTtl(): int { return 120; }

    protected function build(): array
    {
        $lid = app('active_lembaga_id');

        $statusCounts = Pendaftaran::when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total'        => $statusCounts->sum(),
            'submit'       => $statusCounts->get('submit', 0),
            'verifikasi'   => $statusCounts->get('verifikasi', 0),
            'lulus'        => $statusCounts->get('lulus', 0),
            'tidak_lulus'  => $statusCounts->get('tidak_lulus', 0),
            'daftar_ulang' => $statusCounts->get('daftar_ulang', 0),
            'siswa_tetap'  => $statusCounts->get('siswa_tetap', 0),
        ];
        $stats['needs_action'] = $stats['submit'] + $stats['verifikasi'];

        $payQuery = PembayaranPpdb::when(
            $lid,
            fn ($q) => $q->whereHas('pendaftaran', fn ($p) => $p->where('lembaga_id', $lid))
        );

        $payCounts = (clone $payQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats['paid']          = $payCounts->get('paid', 0);
        $stats['pending_bayar'] = $payCounts->get('pending', 0);
        $stats['paid_amount']   = (clone $payQuery)->where('status', 'paid')->sum('amount');

        $pembukaanAktifCount = PembukaanPpdb::where('status', 'buka')
            ->when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->count();

        $lembagaCount = Lembaga::count();

        return compact('stats', 'pembukaanAktifCount', 'lembagaCount');
    }

    public function view(): string
    {
        return 'admin.dashboard.widgets.ppdb.stat';
    }
}
