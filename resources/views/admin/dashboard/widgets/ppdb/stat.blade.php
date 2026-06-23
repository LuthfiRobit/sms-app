@php
    $paidFmt    = 'Rp ' . number_format($stats['paid_amount'], 0, ',', '.');
    $diterima   = $stats['lulus'] + $stats['siswa_tetap'];
    $pctDiterima= $stats['total'] > 0 ? round($diterima / $stats['total'] * 100) : 0;
@endphp

<div class="row g-3">

    {{-- Total Pendaftar --}}
    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#e8f5ee;color:#00843D">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($stats['total']) }}</div>
                <div class="dash-stat-label">Total Pendaftar</div>
            </div>
            <div class="dash-stat-trend" style="color:#00843D">
                <i class="bi bi-person-plus"></i>
            </div>
        </div>
    </div>

    {{-- Perlu Diproses --}}
    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card {{ $stats['needs_action'] > 0 ? 'dash-stat-card--alert' : '' }}">
            <div class="dash-stat-icon" style="background:#fff3e0;color:#f57c00">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($stats['needs_action']) }}</div>
                <div class="dash-stat-label">Perlu Diproses</div>
            </div>
            @if($stats['needs_action'] > 0)
                <div class="dash-stat-trend" style="color:#f57c00">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
            @endif
        </div>
    </div>

    {{-- Sudah Bayar --}}
    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#e8f5e9;color:#2e7d32">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($stats['paid']) }}</div>
                <div class="dash-stat-label">Sudah Bayar</div>
            </div>
            @if($stats['pending_bayar'] > 0)
                <div class="dash-stat-trend" style="color:#f57c00">
                    <span class="badge bg-warning text-dark" style="font-size:.65rem">{{ $stats['pending_bayar'] }} menunggu</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Diterima --}}
    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#e3f2fd;color:#1565c0">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($diterima) }}</div>
                <div class="dash-stat-label">Diterima</div>
            </div>
            <div class="dash-stat-trend" style="color:#6b7280;font-size:.72rem">
                {{ $pctDiterima }}% dari total
            </div>
        </div>
    </div>

    {{-- Total Pemasukan --}}
    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#fdf3dc;color:#C8952A">
                <i class="bi bi-bank"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val" style="font-size:1.1rem">{{ $paidFmt }}</div>
                <div class="dash-stat-label">Total Pemasukan</div>
            </div>
        </div>
    </div>

    {{-- PPDB Aktif --}}
    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#f3e5f5;color:#6a1b9a">
                <i class="bi bi-building"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($pembukaanAktifCount) }}</div>
                <div class="dash-stat-label">PPDB Aktif</div>
            </div>
            <div class="dash-stat-trend" style="color:#6b7280;font-size:.72rem">
                dari {{ $lembagaCount }} lembaga
            </div>
        </div>
    </div>

</div>
