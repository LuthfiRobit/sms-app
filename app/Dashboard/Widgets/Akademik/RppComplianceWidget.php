<?php

namespace App\Dashboard\Widgets\Akademik;

use App\Dashboard\DashboardWidget;
use App\Models\Akademik\Rpp;
use App\Models\Master\JadwalKbm;

class RppComplianceWidget extends DashboardWidget
{
    public function id(): string
    {
        return 'akademik.rpp-compliance';
    }

    public function title(): string
    {
        return 'Kepatuhan RPP';
    }

    public function size(): string
    {
        return 'col-md-6';
    }

    public function order(): int
    {
        return 6;
    }

    public function cacheTtl(): int
    {
        return 120;
    }

    public function permission(): ?string
    {
        return 'admin.akademik.rpp.index';
    }

    protected function build(): array
    {
        $lid = app('active_lembaga_id');

        $kombinasiJadwal = JadwalKbm::when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->get(['guru_id', 'mata_pelajaran_id', 'tahun_pelajaran_id'])
            ->unique(fn ($j) => "{$j->guru_id}.{$j->mata_pelajaran_id}.{$j->tahun_pelajaran_id}");

        $rppDisetujuiSet = Rpp::where('status', 'disetujui')
            ->when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->get(['guru_id', 'mata_pelajaran_id', 'tahun_pelajaran_id'])
            ->map(fn ($r) => "{$r->guru_id}.{$r->mata_pelajaran_id}.{$r->tahun_pelajaran_id}")
            ->flip();

        $totalKombinasi = $kombinasiJadwal->count();
        $belumPunyaRpp = $kombinasiJadwal
            ->reject(fn ($j) => $rppDisetujuiSet->has("{$j->guru_id}.{$j->mata_pelajaran_id}.{$j->tahun_pelajaran_id}"))
            ->count();

        return compact('totalKombinasi', 'belumPunyaRpp');
    }

    public function view(): string
    {
        return 'admin.dashboard.widgets.akademik.rpp-compliance';
    }
}
