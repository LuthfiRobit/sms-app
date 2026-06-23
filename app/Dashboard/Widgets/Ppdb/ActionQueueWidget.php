<?php

namespace App\Dashboard\Widgets\Ppdb;

use App\Dashboard\DashboardWidget;
use App\Models\Transaksi\Pendaftaran;

class ActionQueueWidget extends DashboardWidget
{
    public function id(): string     { return 'ppdb.action-queue'; }
    public function title(): string  { return 'Perlu Tindakan'; }
    public function size(): string   { return 'col-12 col-xl-7'; }
    public function order(): int     { return 15; }
    public function cacheTtl(): int  { return 0; }  // Eloquent models tidak serializable
    public function permission(): ?string { return 'admin.pendaftaran.index'; }

    protected function build(): array
    {
        $lid = app('active_lembaga_id');

        $recentPendaftaran = Pendaftaran::with([
            'peserta:id,nama_lengkap',
            'lembaga:id,nama,kode',
            'jalurPendaftaran:id,nama',
        ])
            ->when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->whereIn('status', ['submit', 'verifikasi'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return compact('recentPendaftaran', 'lid');
    }

    public function view(): string
    {
        return 'admin.dashboard.widgets.ppdb.action-queue';
    }
}
