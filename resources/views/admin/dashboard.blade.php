@extends('admin.layouts.app')

@section('title', 'Dashboard PPDB')

@section('content')

@php
    $lembagaAktif = $activeLembagaId
        ? $perLembaga->firstWhere('id', $activeLembagaId)?->nama ?? 'Lembaga'
        : 'Semua Lembaga';
    $needsAction = $stats['submit'] + $stats['verifikasi'];
    $paidFmt     = 'Rp ' . number_format($paidAmount, 0, ',', '.');
@endphp

{{-- ══ HERO BANNER ══════════════════════════════════════════════════════ --}}
<div class="dash-hero mb-4">
    <div class="dash-hero-left">
        <div class="dash-hero-eyebrow">
            <span class="dash-hero-dot"></span>
            {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
        </div>
        <h1 class="dash-hero-title">Dashboard PPDB</h1>
        <p class="dash-hero-sub">
            {{ $lembagaAktif }}
            @if($needsAction > 0)
                &mdash; <span class="dash-hero-action-hint">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $needsAction }} pendaftaran perlu ditindaklanjuti
                </span>
            @endif
        </p>
    </div>
    <div class="dash-hero-right">
        <a href="{{ route('admin.pendaftaran.index') }}" class="dash-quick-btn">
            <i class="bi bi-file-earmark-text"></i><span>Pendaftaran</span>
        </a>
        <a href="{{ route('admin.pembayaran.index') }}" class="dash-quick-btn">
            <i class="bi bi-wallet2"></i><span>Pembayaran</span>
        </a>
        <a href="{{ route('admin.ppdb.jalur.index') }}" class="dash-quick-btn">
            <i class="bi bi-trophy"></i><span>Seleksi</span>
        </a>
        <a href="{{ route('admin.peserta.index') }}" class="dash-quick-btn">
            <i class="bi bi-people"></i><span>Peserta</span>
        </a>
    </div>
</div>

{{-- ══ STAT CARDS ════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

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

    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card {{ $needsAction > 0 ? 'dash-stat-card--alert' : '' }}">
            <div class="dash-stat-icon" style="background:#fff3e0;color:#f57c00">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($needsAction) }}</div>
                <div class="dash-stat-label">Perlu Diproses</div>
            </div>
            @if($needsAction > 0)
                <div class="dash-stat-trend" style="color:#f57c00">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
            @endif
        </div>
    </div>

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

    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#e3f2fd;color:#1565c0">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ number_format($stats['lulus'] + $stats['siswa_tetap']) }}</div>
                <div class="dash-stat-label">Diterima</div>
            </div>
            <div class="dash-stat-trend" style="color:#6b7280;font-size:.72rem">
                dari {{ $stats['total'] > 0 ? round(($stats['lulus'] + $stats['siswa_tetap']) / $stats['total'] * 100) : 0 }}%
            </div>
        </div>
    </div>

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

    <div class="col-xl col-md-4 col-6">
        <div class="dash-stat-card">
            <div class="dash-stat-icon" style="background:#f3e5f5;color:#6a1b9a">
                <i class="bi bi-building"></i>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-val">{{ $pembukaanAktif->count() }}</div>
                <div class="dash-stat-label">PPDB Aktif</div>
            </div>
            <div class="dash-stat-trend" style="color:#6b7280;font-size:.72rem">
                dari {{ $perLembaga->count() }} lembaga
            </div>
        </div>
    </div>

</div>

{{-- ══ CHARTS ════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Tren Pendaftar --}}
    <div class="col-xl-8">
        <div class="dash-card h-100">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Tren Pendaftar</div>
                    <div class="dash-card-sub">14 hari terakhir</div>
                </div>
                <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-outline-secondary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="dash-card-body">
                <div id="chartTren" style="min-height:220px"></div>
            </div>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="col-xl-4">
        <div class="dash-card h-100">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Status Pendaftaran</div>
                    <div class="dash-card-sub">Distribusi saat ini</div>
                </div>
            </div>
            <div class="dash-card-body">
                <div id="chartStatus" style="min-height:180px"></div>
                <div class="dash-legend mt-2">
                    @foreach([
                        ['Draft',       $stats['draft'],        '#9e9e9e'],
                        ['Dikirim',     $stats['submit'],       '#1565c0'],
                        ['Verifikasi',  $stats['verifikasi'],   '#f57c00'],
                        ['Lulus',       $stats['lulus'],        '#00843D'],
                        ['Tdk Lulus',   $stats['tidak_lulus'],  '#c62828'],
                        ['Daftar Ulang',$stats['daftar_ulang'], '#C8952A'],
                        ['Siswa Tetap', $stats['siswa_tetap'],  '#2e7d32'],
                    ] as [$label, $count, $color])
                        @if($count > 0)
                        <div class="dash-legend-item">
                            <span class="dash-legend-dot" style="background:{{ $color }}"></span>
                            <span class="dash-legend-label">{{ $label }}</span>
                            <span class="dash-legend-count">{{ $count }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══ PER LEMBAGA BAR ══════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Pendaftar per Lembaga</div>
                    <div class="dash-card-sub">Tahun PPDB berjalan</div>
                </div>
            </div>
            <div class="dash-card-body">
                <div id="chartLembaga" style="min-height:200px"></div>
            </div>
        </div>
    </div>
</div>

{{-- ══ BOTTOM ROW ════════════════════════════════════════════════════════ --}}
<div class="row g-3">

    {{-- Pendaftaran Perlu Aksi --}}
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Perlu Tindakan</div>
                    <div class="dash-card-sub">Pendaftaran masuk & dalam verifikasi</div>
                </div>
                <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-outline-secondary">
                    Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="dash-card-body p-0">
                @if($recentPendaftaran->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Tidak ada pendaftaran yang perlu diproses
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 dash-table">
                            <thead>
                                <tr>
                                    <th>Nama Peserta</th>
                                    @if(!$activeLembagaId)<th>Lembaga</th>@endif
                                    <th>Jalur</th>
                                    <th>Status</th>
                                    <th>Masuk</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPendaftaran as $p)
                                <tr>
                                    <td class="fw-600">{{ $p->peserta?->nama_lengkap ?? '—' }}</td>
                                    @if(!$activeLembagaId)
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $p->lembaga?->kode ?? '—' }}</span>
                                        </td>
                                    @endif
                                    <td class="text-muted small">{{ $p->jalurPendaftaran?->nama ?? '—' }}</td>
                                    <td>
                                        @if($p->status === 'submit')
                                            <span class="badge bg-primary">Dikirim</span>
                                        @elseif($p->status === 'verifikasi')
                                            <span class="badge bg-warning text-dark">Verifikasi</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $p->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.pendaftaran.show', $p->id) }}"
                                           class="btn btn-xs btn-outline-primary">
                                            Proses
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Pembukaan PPDB Aktif + Konfirmasi Bayar --}}
    <div class="col-xl-5">

        {{-- Pembayaran Manual Menunggu --}}
        @if($pendingPayment->isNotEmpty())
        <div class="dash-card mb-3">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title text-warning">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Konfirmasi Transfer Manual
                    </div>
                    <div class="dash-card-sub">Bukti upload menunggu verifikasi</div>
                </div>
                <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-sm btn-outline-warning">
                    Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="dash-card-body p-0">
                <ul class="dash-pay-list">
                    @foreach($pendingPayment as $pay)
                    <li class="dash-pay-item">
                        <div class="dash-pay-avatar">
                            {{ strtoupper(substr($pay->pendaftaran?->peserta?->nama_lengkap ?? 'P', 0, 1)) }}
                        </div>
                        <div class="dash-pay-info">
                            <div class="dash-pay-name">{{ $pay->pendaftaran?->peserta?->nama_lengkap ?? '—' }}</div>
                            <div class="dash-pay-meta">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                &middot; {{ $pay->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <a href="{{ route('admin.pembayaran.show', $pay->id) }}"
                           class="btn btn-xs btn-warning text-dark fw-bold">Verifikasi</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Pembukaan PPDB Aktif --}}
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">PPDB Sedang Buka</div>
                    <div class="dash-card-sub">{{ $pembukaanAktif->count() }} pembukaan aktif</div>
                </div>
            </div>
            <div class="dash-card-body p-0">
                @if($pembukaanAktif->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                        Tidak ada PPDB yang sedang buka
                    </div>
                @else
                    <ul class="dash-pembukaan-list">
                        @foreach($pembukaanAktif as $pb)
                        <li class="dash-pembukaan-item">
                            <div class="dash-pembukaan-jenis">
                                <span class="badge" style="background:var(--color-primary);font-size:.65rem">
                                    {{ $pb->lembaga?->jenis ?? '?' }}
                                </span>
                            </div>
                            <div class="dash-pembukaan-info">
                                <div class="dash-pembukaan-nama">{{ $pb->lembaga?->nama ?? '—' }}</div>
                                <div class="dash-pembukaan-meta">
                                    {{ $pb->jalur_pendaftaran_count }} jalur
                                    @if($pb->selesai)
                                        &middot; tutup {{ \Carbon\Carbon::parse($pb->selesai)->isoFormat('D MMM') }}
                                    @endif
                                </div>
                            </div>
                            <span class="dash-buka-badge">Buka</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>

</div>

{{-- ══ STYLES ════════════════════════════════════════════════════════════ --}}
<style>
/* Hero */
.dash-hero {
    background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 65%, #009a42 100%);
    border-radius: 14px;
    padding: clamp(1.25rem, 3vw, 2rem) clamp(1.5rem, 3vw, 2.25rem);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,132,61,.25);
}
.dash-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cg fill='none' stroke='%23fff' stroke-opacity='0.05' stroke-width='0.8'%3E%3Cpath d='M30 2L58 30L30 58L2 30Z'/%3E%3Cpath d='M30 15L45 30L30 45L15 30Z'/%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.dash-hero-left { position: relative; z-index: 1; }
.dash-hero-eyebrow {
    display: flex; align-items: center; gap: .5rem;
    font-size: .72rem; font-weight: 700; color: rgba(255,255,255,.7);
    text-transform: uppercase; letter-spacing: 1px; margin-bottom: .4rem;
}
.dash-hero-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #86efac;
    box-shadow: 0 0 0 3px rgba(134,239,172,.25);
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 3px rgba(134,239,172,.25); }
    50%      { box-shadow: 0 0 0 6px rgba(134,239,172,.1); }
}
.dash-hero-title {
    font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 900;
    color: #fff; margin: 0 0 .3rem; letter-spacing: -.5px;
}
.dash-hero-sub { color: rgba(255,255,255,.8); font-size: .875rem; margin: 0; }
.dash-hero-action-hint { color: #fde68a; font-weight: 700; }
.dash-hero-right {
    position: relative; z-index: 1;
    display: flex; gap: .625rem; flex-wrap: wrap;
}
.dash-quick-btn {
    display: flex; flex-direction: column; align-items: center;
    gap: .25rem; padding: .625rem .875rem;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    border-radius: 10px; color: rgba(255,255,255,.9);
    text-decoration: none; font-size: .72rem; font-weight: 700;
    backdrop-filter: blur(6px);
    transition: all .18s;
}
.dash-quick-btn i { font-size: 1.15rem; }
.dash-quick-btn:hover { background: rgba(255,255,255,.25); color: #fff; transform: translateY(-2px); }

/* Stat Cards */
.dash-stat-card {
    background: #fff; border-radius: 12px;
    border: 1.5px solid var(--color-border);
    padding: 1rem 1.125rem;
    display: flex; align-items: center; gap: .875rem;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .15s;
    position: relative; overflow: hidden;
}
.dash-stat-card:hover { box-shadow: var(--shadow-md); }
.dash-stat-card--alert { border-color: #fbbf24; }
.dash-stat-icon {
    width: 42px; height: 42px; flex-shrink: 0;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
}
.dash-stat-body { flex: 1; min-width: 0; }
.dash-stat-val {
    font-size: 1.5rem; font-weight: 900;
    color: var(--color-text); line-height: 1; margin-bottom: .2rem;
    letter-spacing: -.5px;
}
.dash-stat-label { font-size: .72rem; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .5px; }
.dash-stat-trend { font-size: .75rem; font-weight: 700; flex-shrink: 0; text-align: right; }

/* Cards */
.dash-card {
    background: #fff; border-radius: 12px;
    border: 1.5px solid var(--color-border);
    box-shadow: var(--shadow-sm); overflow: hidden;
}
.dash-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem .75rem;
    border-bottom: 1px solid var(--color-border-light);
}
.dash-card-title { font-weight: 800; font-size: .9rem; color: var(--color-text); }
.dash-card-sub   { font-size: .72rem; color: var(--color-text-muted); margin-top: .1rem; }
.dash-card-body  { padding: 1rem 1.25rem; }

/* Legend */
.dash-legend { display: flex; flex-direction: column; gap: .35rem; }
.dash-legend-item {
    display: flex; align-items: center; gap: .5rem;
    font-size: .78rem;
}
.dash-legend-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.dash-legend-label { flex: 1; color: var(--color-text-muted); }
.dash-legend-count { font-weight: 700; color: var(--color-text); }

/* Table */
.dash-table { font-size: .82rem; }
.dash-table th {
    font-size: .68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .5px;
    color: var(--color-text-muted); border-bottom-width: 1px;
    padding: .625rem 1rem; background: var(--color-bg);
}
.dash-table td { padding: .625rem 1rem; vertical-align: middle; }
.btn-xs { padding: .2rem .55rem; font-size: .72rem; border-radius: 6px; }

/* Payment list */
.dash-pay-list { list-style: none; margin: 0; padding: 0; }
.dash-pay-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem 1.25rem;
    border-bottom: 1px solid var(--color-border-light);
}
.dash-pay-item:last-child { border-bottom: none; }
.dash-pay-avatar {
    width: 36px; height: 36px; flex-shrink: 0;
    border-radius: 50%; background: var(--color-primary-light);
    color: var(--color-primary-hover); font-weight: 800; font-size: .82rem;
    display: flex; align-items: center; justify-content: center;
}
.dash-pay-name  { font-weight: 700; font-size: .82rem; }
.dash-pay-meta  { font-size: .72rem; color: var(--color-text-muted); }

/* Pembukaan list */
.dash-pembukaan-list { list-style: none; margin: 0; padding: 0; }
.dash-pembukaan-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .75rem 1.25rem;
    border-bottom: 1px solid var(--color-border-light);
}
.dash-pembukaan-item:last-child { border-bottom: none; }
.dash-pembukaan-nama { font-weight: 700; font-size: .82rem; }
.dash-pembukaan-meta { font-size: .72rem; color: var(--color-text-muted); }
.dash-pembukaan-info { flex: 1; min-width: 0; }
.dash-buka-badge {
    padding: .2rem .625rem; border-radius: 999px;
    background: #dcfce7; color: #15803d;
    font-size: .65rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .5px;
    flex-shrink: 0;
}

@media(max-width:575.98px) {
    .dash-hero { flex-direction: column; align-items: flex-start; }
    .dash-stat-val { font-size: 1.25rem; }
}
</style>

@endsection

@push('vendor-scripts')
<script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/apexcharts.min.js') }}"></script>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const GREEN  = '#00843D';
    const GOLD   = '#C8952A';
    const BLUE   = '#1565c0';
    const ORANGE = '#f57c00';
    const RED    = '#c62828';
    const GREY   = '#9e9e9e';

    // ── Tren Chart ─────────────────────────────────────────────────────────
    const trenDates  = @json($trenDates);
    const trenValues = @json($trenValues);

    new ApexCharts(document.getElementById('chartTren'), {
        series: [{ name: 'Pendaftar', data: trenValues }],
        chart: {
            type: 'area', height: 220,
            toolbar: { show: false }, sparkline: { enabled: false },
            animations: { speed: 600 },
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: .02, stops: [0, 95] }
        },
        colors: [GREEN],
        xaxis: {
            categories: trenDates,
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: {
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
            min: 0,
        },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        tooltip: { theme: 'light' },
    }).render();

    // ── Status Donut ───────────────────────────────────────────────────────
    const statusData = [
        { label: 'Draft',        val: {{ $stats['draft'] }},       color: GREY },
        { label: 'Dikirim',      val: {{ $stats['submit'] }},      color: BLUE },
        { label: 'Verifikasi',   val: {{ $stats['verifikasi'] }},  color: ORANGE },
        { label: 'Lulus',        val: {{ $stats['lulus'] }},       color: GREEN },
        { label: 'Tdk Lulus',    val: {{ $stats['tidak_lulus'] }}, color: RED },
        { label: 'Daftar Ulang', val: {{ $stats['daftar_ulang'] }},color: GOLD },
        { label: 'Siswa Tetap',  val: {{ $stats['siswa_tetap'] }}, color: '#2e7d32' },
    ].filter(d => d.val > 0);

    if (statusData.length > 0) {
        new ApexCharts(document.getElementById('chartStatus'), {
            series: statusData.map(d => d.val),
            labels: statusData.map(d => d.label),
            colors: statusData.map(d => d.color),
            chart: { type: 'donut', height: 180, toolbar: { show: false } },
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '65%' } } },
            tooltip: { theme: 'light' },
        }).render();
    } else {
        document.getElementById('chartStatus').innerHTML =
            '<div style="height:180px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:.82rem">Belum ada data</div>';
    }

    // ── Per Lembaga Bar ────────────────────────────────────────────────────
    const lembagaNames  = @json($perLembaga->pluck('kode'));
    const lembagaValues = @json($perLembaga->pluck('pendaftaran_count'));

    new ApexCharts(document.getElementById('chartLembaga'), {
        series: [{ name: 'Pendaftar', data: lembagaValues }],
        chart: {
            type: 'bar', height: 200,
            toolbar: { show: false },
        },
        plotOptions: {
            bar: { horizontal: true, borderRadius: 5, barHeight: '55%' }
        },
        dataLabels: {
            enabled: true,
            formatter: v => v || '–',
            style: { fontSize: '11px', fontWeight: 700 },
        },
        colors: [GREEN],
        xaxis: {
            categories: lembagaNames,
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
        },
        yaxis: { labels: { style: { fontSize: '11px', colors: '#374151' } } },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        tooltip: { theme: 'light' },
    }).render();

})();
</script>
@endpush
