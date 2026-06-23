@extends('admin.layouts.app')
@section('title', 'Dashboard — Marifat')

@push('styles')
<style>
/* ─────────────────────────────────────────────────────────────
   Dashboard — LP Ma'arif NU Kraksaan
   Seluruh CSS mengacu pada token Bootstrap & template yang
   sudah terdefinisi di style.css dan custom-style.css agar
   warna, shadow, dan radius konsisten dengan halaman lain.

   Token yang dipakai:
     --bs-primary          → #00843D  (override di custom-style)
     --bs-border-color     → #eeeeee
     --bs-border-radius    → 8px
     --bs-card-border-radius → 8px
     --bs-gray-100..900    → Bootstrap gray scale
     --bs-body-color       → #212529
     --maarif-green        → #00843D
     --maarif-green-light  → #E8F5EE
     --maarif-green-dark   → #004E24
     --maarif-gold         → #C8952A
     --maarif-gold-light   → #FDF3DC
     --pc-card-box-shadow  → shadow kartu (none by default)
───────────────────────────────────────────────────────────── */

/* ── Greeting ──────────────────────────────────────────────── */
.dash-greeting {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding-bottom: 1.25rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--maarif-green-light);
}
.dash-greeting-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--maarif-green);
    margin-bottom: .3rem;
}
.dash-greeting-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--maarif-green);
    flex-shrink: 0;
    animation: dash-blink 2.4s ease-in-out infinite;
}
@keyframes dash-blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: .25; }
}
.dash-greeting-name {
    font-size: 1.65rem;
    font-weight: 900;
    color: var(--bs-gray-900);
    letter-spacing: -.5px;
    line-height: 1.1;
    margin: 0 0 .2rem;
}
.dash-greeting-sub {
    font-size: .82rem;
    color: var(--bs-gray-600);
    margin: 0;
}
.dash-lembaga-pill {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .4rem 1rem;
    background: var(--maarif-green-light);
    border: 1px solid rgba(0,132,61,.2);
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 700;
    color: var(--maarif-green-dark);
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Alert Banner ──────────────────────────────────────────── */
.dash-alert-action {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.25rem;
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: var(--bs-card-border-radius);
    margin-bottom: 1.5rem;
    font-size: .82rem;
}
.dash-alert-action > i    { color: var(--bs-warning); font-size: 1.1rem; flex-shrink: 0; }
.dash-alert-action b      { color: #7c5e0a; }
.dash-alert-action .ms-auto { flex-shrink: 0; }

/* ── Module Tabs ───────────────────────────────────────────── */
.dash-module-nav {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}
.dash-module-btn {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .5rem 1rem;
    border-radius: var(--bs-border-radius);
    border: 1px solid var(--bs-border-color);
    background: #fff;
    font-size: .8rem;
    font-weight: 600;
    color: var(--bs-gray-500);
    cursor: default;
    user-select: none;
    line-height: 1;
}
.dash-module-btn.is-active {
    background: var(--bs-primary);
    border-color: var(--bs-primary);
    color: #fff;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), .3);
}
.dash-module-cs {
    font-size: .6rem;
    font-weight: 700;
    padding: .15rem .4rem;
    border-radius: 4px;
    background: rgba(0,0,0,.08);
    text-transform: uppercase;
    letter-spacing: .5px;
    color: inherit;
}

/* ── Quick Actions ─────────────────────────────────────────── */
.dash-qa-row {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1.75rem;
}
.dash-qa-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem 1rem;
    background: #fff;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    font-size: .8rem;
    font-weight: 600;
    color: var(--bs-gray-700);
    text-decoration: none;
    transition: border-color .15s, color .15s, box-shadow .15s;
}
.dash-qa-btn:hover {
    border-color: var(--bs-primary);
    color: var(--bs-primary);
    box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), .1);
    text-decoration: none;
}
.dash-qa-btn i { font-size: .95rem; }

/* ── Section Label ─────────────────────────────────────────── */
.dash-section-hd {
    display: flex;
    align-items: center;
    gap: .625rem;
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: var(--bs-primary);
    margin-bottom: 1rem;
}
.dash-section-hd::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--bs-border-color);
}

/* ── Stat Cards ────────────────────────────────────────────── */
.dash-stat-card {
    background: #fff;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-card-border-radius);
    padding: 1rem 1.125rem;
    display: flex;
    align-items: center;
    gap: .875rem;
    box-shadow: var(--pc-card-box-shadow, 0 1px 4px rgba(0,0,0,.06));
    transition: box-shadow .15s;
    height: 100%;
}
.dash-stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.dash-stat-card--alert {
    border-color: var(--bs-warning);
    border-left: 3px solid var(--bs-warning);
}
.dash-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--bs-border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.dash-stat-body { flex: 1; min-width: 0; }
.dash-stat-val {
    font-size: 1.55rem;
    font-weight: 900;
    color: var(--bs-gray-900);
    line-height: 1;
    margin-bottom: .15rem;
    letter-spacing: -.5px;
}
.dash-stat-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--bs-gray-500);
}
.dash-stat-trend {
    font-size: .75rem;
    font-weight: 600;
    flex-shrink: 0;
    text-align: right;
    color: var(--bs-gray-600);
}

/* ── Widget Cards ──────────────────────────────────────────── */
.dash-card {
    background: #fff;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-card-border-radius);
    box-shadow: var(--pc-card-box-shadow, 0 1px 4px rgba(0,0,0,.06));
    overflow: hidden;
}
.dash-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .9rem 1.125rem .75rem;
    border-bottom: 1px solid var(--bs-border-color);
}
.dash-card-title { font-weight: 700; font-size: .875rem; color: var(--bs-gray-900); }
.dash-card-sub   { font-size: .7rem; color: var(--bs-gray-500); margin-top: .1rem; }
.dash-card-body  { padding: 1rem 1.125rem; }

/* ── Legend ────────────────────────────────────────────────── */
.dash-legend { display: flex; flex-direction: column; gap: .35rem; }
.dash-legend-item { display: flex; align-items: center; gap: .5rem; font-size: .78rem; }
.dash-legend-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dash-legend-label { flex: 1; color: var(--bs-gray-600); }
.dash-legend-count { font-weight: 700; color: var(--bs-gray-900); }

/* ── Table ─────────────────────────────────────────────────── */
.dash-table { font-size: .82rem; }
.dash-table th {
    font-size: .67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--bs-gray-500);
    border-bottom-width: 1px;
    padding: .6rem 1rem;
    background: var(--bs-gray-100);
}
.dash-table td { padding: .6rem 1rem; vertical-align: middle; }
.btn-xs { padding: .2rem .55rem; font-size: .72rem; border-radius: var(--bs-border-radius-sm); }

/* ── Payment List ──────────────────────────────────────────── */
.dash-pay-list { list-style: none; margin: 0; padding: 0; }
.dash-pay-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.125rem;
    border-bottom: 1px solid var(--bs-border-color);
}
.dash-pay-item:last-child { border-bottom: none; }
.dash-pay-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--maarif-green-light);
    color: var(--maarif-green-dark);
    font-weight: 700; font-size: .78rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.dash-pay-info  { flex: 1; min-width: 0; }
.dash-pay-name  { font-weight: 600; font-size: .82rem; color: var(--bs-gray-900); }
.dash-pay-meta  { font-size: .72rem; color: var(--bs-gray-500); }

/* ── Pembukaan List ────────────────────────────────────────── */
.dash-pembukaan-list { list-style: none; margin: 0; padding: 0; }
.dash-pembukaan-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1.125rem;
    border-bottom: 1px solid var(--bs-border-color);
}
.dash-pembukaan-item:last-child { border-bottom: none; }
.dash-pembukaan-info  { flex: 1; min-width: 0; }
.dash-pembukaan-nama  { font-weight: 600; font-size: .82rem; color: var(--bs-gray-900); }
.dash-pembukaan-meta  { font-size: .72rem; color: var(--bs-gray-500); }
.dash-buka-badge {
    padding: .2rem .6rem;
    border-radius: 999px;
    background: var(--maarif-green-light);
    color: var(--maarif-green-dark);
    font-size: .62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    flex-shrink: 0;
}

/* ── Coming-soon Module Tiles ──────────────────────────────── */
.dash-cs-tile {
    background: var(--bs-gray-100);
    border: 1px dashed var(--bs-gray-300);
    border-radius: var(--bs-card-border-radius);
    padding: 1.75rem 1.5rem;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .5rem;
}
.dash-cs-icon  { font-size: 1.75rem; opacity: .3; display: block; color: var(--bs-gray-600); }
.dash-cs-title { font-weight: 700; font-size: .875rem; color: var(--bs-gray-700); }
.dash-cs-sub   { font-size: .75rem; color: var(--bs-gray-500); max-width: 18rem; margin: 0 auto; }

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 575.98px) {
    .dash-greeting-name { font-size: 1.3rem; }
    .dash-module-nav    { gap: .375rem; }
    .dash-module-btn    { font-size: .75rem; padding: .45rem .75rem; }
}
</style>
@endpush

@section('content')

{{-- ══ Greeting ═════════════════════════════════════════════ --}}
<div class="dash-greeting">
    <div>
        <div class="dash-greeting-eyebrow">
            <span class="dash-greeting-dot"></span>
            {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}
        </div>
        <h1 class="dash-greeting-name">
            Selamat Datang, {{ explode(' ', auth()->user()->name)[0] }}
        </h1>
        <p class="dash-greeting-sub">
            Sistem Informasi Manajemen Terpadu — LP Ma'arif NU Kraksaan
        </p>
    </div>
    <div class="dash-lembaga-pill">
        <i class="bi bi-buildings"></i>
        {{ $lembagaAktifNama }}
    </div>
</div>

{{-- ══ Alert: Ada yang Perlu Ditindak ═══════════════════════ --}}
@if($needsAction > 0)
<div class="dash-alert-action">
    <i class="bi bi-exclamation-circle-fill"></i>
    <div>
        <b>{{ $needsAction }} pendaftaran</b> menunggu verifikasi atau tindakan lebih lanjut.
    </div>
    @can('admin.pendaftaran.index')
    <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-warning text-dark fw-bold ms-auto">
        Tinjau Sekarang <i class="bi bi-arrow-right ms-1"></i>
    </a>
    @endcan
</div>
@endif

{{-- ══ Module Navigation ══════════════════════════════════════ --}}
<div class="dash-module-nav">
    <div class="dash-module-btn is-active">
        <i class="bi bi-person-plus-fill"></i>
        PPDB
    </div>
    <div class="dash-module-btn">
        <i class="bi bi-journal-text"></i>
        Akademik
        <span class="dash-module-cs">Segera</span>
    </div>
    <div class="dash-module-btn">
        <i class="bi bi-bank"></i>
        Keuangan
        <span class="dash-module-cs">Segera</span>
    </div>
    <div class="dash-module-btn">
        <i class="bi bi-emoji-smile-fill"></i>
        Kesiswaan
        <span class="dash-module-cs">Segera</span>
    </div>
</div>

{{-- ══ Quick Actions ══════════════════════════════════════════ --}}
<div class="dash-qa-row">
    @can('admin.pendaftaran.index')
    <a href="{{ route('admin.pendaftaran.index') }}" class="dash-qa-btn">
        <i class="bi bi-file-earmark-person"></i> Data Pendaftar
    </a>
    @endcan
    @can('admin.pembayaran.index')
    <a href="{{ route('admin.pembayaran.index') }}" class="dash-qa-btn">
        <i class="bi bi-wallet2"></i> Pembayaran PPDB
    </a>
    @endcan
    @can('admin.peserta.index')
    <a href="{{ route('admin.peserta.index') }}" class="dash-qa-btn">
        <i class="bi bi-people"></i> Data Peserta
    </a>
    @endcan
    @can('admin.ppdb.pembukaan.index')
    <a href="{{ route('admin.ppdb.pembukaan.index') }}" class="dash-qa-btn">
        <i class="bi bi-megaphone"></i> Pembukaan PPDB
    </a>
    @endcan
</div>

{{-- ══ PPDB Widgets ═══════════════════════════════════════════ --}}
<div class="dash-section-hd">
    <i class="bi bi-person-plus-fill"></i>
    Modul PPDB
</div>

<div class="row g-4">
    @forelse($widgets as $widget)
        <div class="{{ $widget->size() }}" id="widget-{{ str_replace('.', '-', $widget->id()) }}">
            @if($widget->lazy())
                <div class="d-flex align-items-center justify-content-center"
                     style="min-height:120px;background:#fafafa;border-radius:10px;border:1.5px solid #e5e7eb"
                     data-widget-lazy="{{ $widget->id() }}">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                </div>
            @else
                @include($widget->view(), $widget->getData())
            @endif
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-grid fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-0">Tidak ada widget yang tersedia untuk role Anda.</p>
            </div>
        </div>
    @endforelse
</div>

{{-- ══ Coming-soon Modules ════════════════════════════════════ --}}
<div class="dash-section-hd mt-5">
    <i class="bi bi-clock-history"></i>
    Modul Dalam Pengembangan
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="dash-cs-tile">
            <i class="bi bi-journal-text dash-cs-icon"></i>
            <div class="dash-cs-title">Modul Akademik</div>
            <div class="dash-cs-sub">Penjadwalan KBM, presensi siswa & guru, nilai, dan e-rapor</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dash-cs-tile">
            <i class="bi bi-bank dash-cs-icon"></i>
            <div class="dash-cs-title">Modul Keuangan</div>
            <div class="dash-cs-sub">Tagihan SPP, transaksi pembayaran, tunggakan, dan arus kas</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dash-cs-tile">
            <i class="bi bi-emoji-smile-fill dash-cs-icon"></i>
            <div class="dash-cs-title">Modul Kesiswaan</div>
            <div class="dash-cs-sub">Kedisiplinan, prestasi, bimbingan konseling, dan organisasi</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-widget-lazy]').forEach(function (el) {
    var id = el.dataset.widgetLazy;
    fetch('/admin/dashboard/widget/' + id)
        .then(function (r) { return r.json(); })
        .then(function (d) {
            var wrapper = document.getElementById('widget-' + id.replace(/\./g, '-'));
            if (wrapper) wrapper.innerHTML = d.html;
        });
});
</script>
@endpush
