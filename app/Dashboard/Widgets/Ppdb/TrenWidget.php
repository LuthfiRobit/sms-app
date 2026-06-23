<?php

namespace App\Dashboard\Widgets\Ppdb;

use App\Dashboard\DashboardWidget;
use App\Models\Master\Lembaga;
use App\Models\Transaksi\Pendaftaran;
use Carbon\Carbon;

class TrenWidget extends DashboardWidget
{
    public function id(): string    { return 'ppdb.tren'; }
    public function title(): string { return 'Tren & Distribusi'; }
    public function size(): string  { return 'col-12'; }
    public function order(): int    { return 10; }
    public function cacheTtl(): int { return 300; }

    protected function build(): array
    {
        $lid   = app('active_lembaga_id');
        $start = Carbon::now()->subDays(13)->startOfDay();

        $rawTren = Pendaftaran::when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $trenDates = [];
        $trenValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $date         = Carbon::now()->subDays($i)->format('Y-m-d');
            $trenDates[]  = Carbon::now()->subDays($i)->isoFormat('D MMM');
            $trenValues[] = (int) $rawTren->get($date, 0);
        }

        $statusCounts = Pendaftaran::when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'draft'        => $statusCounts->get('draft', 0),
            'submit'       => $statusCounts->get('submit', 0),
            'verifikasi'   => $statusCounts->get('verifikasi', 0),
            'lulus'        => $statusCounts->get('lulus', 0),
            'tidak_lulus'  => $statusCounts->get('tidak_lulus', 0),
            'daftar_ulang' => $statusCounts->get('daftar_ulang', 0),
            'siswa_tetap'  => $statusCounts->get('siswa_tetap', 0),
        ];

        // Plain PHP array — aman di-serialize ke file cache (Eloquent model tidak)
        $perLembaga = Lembaga::orderBy('urutan')
            ->get(['id', 'kode', 'nama'])
            ->map(fn ($l) => [
                'kode'               => $l->kode,
                'nama'               => $l->nama,
                'pendaftaran_count'  => ($lid && $l->id !== $lid)
                    ? 0
                    : Pendaftaran::where('lembaga_id', $l->id)->count(),
            ])
            ->all();

        return compact('trenDates', 'trenValues', 'stats', 'perLembaga');
    }

    public function view(): string
    {
        return 'admin.dashboard.widgets.ppdb.tren';
    }
}
