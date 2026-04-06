@extends('layouts.portal')

@section('title', 'Pendaftaran Saya — PPDB')

@section('content')

@php
    $pendaftaranList = $data['data'] ?? [];
    $hasPendaftaran  = !empty($pendaftaranList) && count($pendaftaranList) > 0;

    /**
     * Map status ke warna Badge Bootstrap + label + ikon
     */
    $statusConfig = [
        'draft'        => ['color' => 'secondary', 'label' => 'Draft',        'icon' => 'bi-file-earmark',    'desc' => 'Sedang dilengkapi'],
        'submit'       => ['color' => 'warning',   'label' => 'Menunggu',     'icon' => 'bi-hourglass-split', 'desc' => 'Menunggu verifikasi'],
        'verifikasi'   => ['color' => 'info',      'label' => 'Verifikasi',   'icon' => 'bi-search',          'desc' => 'Sedang diverifikasi admin'],
        'lulus'        => ['color' => 'success',   'label' => 'Lulus',        'icon' => 'bi-trophy-fill',     'desc' => 'Selamat, Anda lulus!'],
        'tidak_lulus'  => ['color' => 'danger',    'label' => 'Tidak Lulus',  'icon' => 'bi-x-circle-fill',   'desc' => 'Maaf, Anda tidak lulus'],
        'daftar_ulang' => ['color' => 'primary',   'label' => 'Daftar Ulang', 'icon' => 'bi-arrow-repeat',    'desc' => 'Segera lakukan daftar ulang'],
        'siswa_tetap'  => ['color' => 'success',   'label' => 'Siswa Tetap',  'icon' => 'bi-mortarboard-fill','desc' => 'Selamat bergabung!'],
    ];

    $bulanIndo = [
        1  => 'Januari',  2  => 'Februari', 3  => 'Maret',    4  => 'April',
        5  => 'Mei',      6  => 'Juni',     7  => 'Juli',      8  => 'Agustus',
        9  => 'September',10 => 'Oktober',  11 => 'November',  12 => 'Desember',
    ];

    $formatTgl = function($tgl) use ($bulanIndo) {
        if (!$tgl) return '—';
        $dt = $tgl instanceof \Carbon\Carbon ? $tgl : \Carbon\Carbon::parse($tgl);
        return $dt->day . ' ' . $bulanIndo[$dt->month] . ' ' . $dt->year;
    };
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════════ --}}
<div class="pend-page-header mb-4">
    <div class="pend-header-left">
        <div class="pend-header-icon">
            <i class="bi bi-file-earmark-text-fill"></i>
        </div>
        <div>
            <h1 class="pend-page-title">Pendaftaran Saya</h1>
            <p class="pend-page-subtitle">
                Kelola dan pantau status pendaftaran PPDB Anda
            </p>
        </div>
    </div>
    <div class="pend-header-actions">
        <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-success btn-daftar-baru">
            <i class="bi bi-plus-circle me-2"></i>Daftar Jalur Baru
        </a>
        <a href="{{ route('ppdb.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     EMPTY STATE
═══════════════════════════════════════════════════════════════════ --}}
@if(!$hasPendaftaran)
<div class="empty-state-wrapper">
    <div class="empty-state-card">
        {{-- Ilustrasi SVG --}}
        <div class="empty-illustration">
            <svg width="160" height="140" viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Dokumen background -->
                <rect x="25" y="20" width="110" height="95" rx="12" fill="#f0fdf4" stroke="#bbf7d0" stroke-width="2"/>
                <rect x="25" y="20" width="110" height="24" rx="12" fill="#dcfce7" stroke="#bbf7d0" stroke-width="2"/>
                <rect x="25" y="32" width="110" height="12" fill="#dcfce7"/>
                <!-- Lines -->
                <rect x="44" y="58" width="72" height="7" rx="3.5" fill="#bbf7d0"/>
                <rect x="44" y="72" width="55" height="6" rx="3" fill="#d1fae5"/>
                <rect x="44" y="85" width="62" height="6" rx="3" fill="#d1fae5"/>
                <rect x="44" y="98" width="40" height="6" rx="3" fill="#d1fae5"/>
                <!-- Icon di header -->
                <circle cx="44" cy="32" r="6" fill="#16a34a"/>
                <line x1="41" y1="32" x2="47" y2="32" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="44" y1="29" x2="44" y2="35" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
                <!-- Plus circle bawah kanan -->
                <circle cx="122" cy="108" r="22" fill="#16a34a" opacity="0.15"/>
                <circle cx="122" cy="108" r="16" fill="#16a34a"/>
                <line x1="115" y1="108" x2="129" y2="108" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="122" y1="101" x2="122" y2="115" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </div>

        <h4 class="empty-title">Belum Ada Pendaftaran</h4>
        <p class="empty-desc">
            Anda belum memiliki pendaftaran PPDB aktif.<br>
            Mulai daftar sekarang sebelum kuota habis!
        </p>

        <div class="empty-actions">
            <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-success btn-lg px-5 fw-semibold">
                <i class="bi bi-file-earmark-plus me-2"></i>Mulai Daftar Sekarang
            </a>
            <a href="{{ route('ppdb.dashboard') }}" class="btn btn-light px-4">
                <i class="bi bi-house me-2"></i>Dashboard
            </a>
        </div>

        <div class="empty-hint">
            <i class="bi bi-lightbulb text-warning me-1"></i>
            <span>Tip: Pastikan profil Anda sudah lengkap sebelum mendaftar.</span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     LIST PENDAFTARAN
═══════════════════════════════════════════════════════════════════ --}}
@else

{{-- Summary bar --}}
<div class="pend-summary-bar mb-4">
    <div class="summary-stat">
        <span class="summary-num">{{ count($pendaftaranList) }}</span>
        <span class="summary-lbl">Total Pendaftaran</span>
    </div>
    @php
        $countDraft   = collect($pendaftaranList)->where('status', 'draft')->count();
        $countSubmit  = collect($pendaftaranList)->whereIn('status', ['submit', 'verifikasi'])->count();
        $countLulus   = collect($pendaftaranList)->where('status', 'lulus')->count();
    @endphp
    @if($countDraft)
    <div class="summary-stat">
        <span class="summary-num text-secondary">{{ $countDraft }}</span>
        <span class="summary-lbl">Draft</span>
    </div>
    @endif
    @if($countSubmit)
    <div class="summary-stat">
        <span class="summary-num text-warning">{{ $countSubmit }}</span>
        <span class="summary-lbl">Proses</span>
    </div>
    @endif
    @if($countLulus)
    <div class="summary-stat">
        <span class="summary-num text-success">{{ $countLulus }}</span>
        <span class="summary-lbl">Lulus</span>
    </div>
    @endif
</div>

<div class="pendaftaran-grid">
    @foreach($pendaftaranList as $daftar)
    @php
        $st     = $statusConfig[$daftar->status] ?? ['color' => 'secondary', 'label' => $daftar->status, 'icon' => 'bi-circle', 'desc' => ''];
        $isDraft = $daftar->status === 'draft';
        $isSubmit = $daftar->status === 'submit';
        $tglDaftar = $formatTgl($daftar->tanggal_daftar ?? $daftar->created_at);
    @endphp

    <div class="pend-card {{ $isDraft ? 'pend-card--draft' : '' }} {{ $daftar->status === 'lulus' ? 'pend-card--lulus' : '' }}">

        {{-- Card Header --}}
        <div class="pend-card-header">
            <div class="pend-card-no-wrapper">
                <span class="pend-no-label">No. Pendaftaran</span>
                <span class="pend-no-value">{{ $daftar->no_pendaftaran }}</span>
            </div>
            <span class="badge bg-{{ $st['color'] }} pend-status-badge">
                <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] }}
            </span>
        </div>

        {{-- Card Body --}}
        <div class="pend-card-body">

            {{-- Jalur & Gelombang --}}
            <div class="pend-jalur-row">
                <div class="pend-jalur-icon">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
                <div>
                    <div class="pend-jalur-nama">{{ $daftar->jalurPendaftaran?->nama ?? '—' }}</div>
                    <div class="pend-jalur-meta">
                        {{ $daftar->tahunPelajaran?->nama ?? ($daftar->jalurPendaftaran?->pembukaanPpdb?->nama ?? '—') }}
                    </div>
                </div>
            </div>

            {{-- Info Row --}}
            <div class="pend-info-row">
                <span class="pend-info-item">
                    <i class="bi bi-calendar3 me-1 text-muted"></i>
                    <span>{{ $tglDaftar }}</span>
                </span>
                <span class="pend-info-item">
                    <i class="bi bi-info-circle me-1 text-muted"></i>
                    <span class="text-muted">{{ $st['desc'] }}</span>
                </span>
            </div>

            {{-- Progress Bar (hanya jika draft) --}}
            @if($isDraft)
            <div class="pend-progress-section">
                <div class="pend-progress-label">
                    <span><i class="bi bi-clipboard-check me-1"></i>Kelengkapan</span>
                    <span class="pend-progress-hint">Lengkapi formulir & dokumen untuk submit</span>
                </div>
                {{-- Progress bar placeholder — nilai aktual dari getProgressDetail di halaman show --}}
                <div class="pend-progress-track">
                    <div class="pend-progress-bar-fill pend-progress-formulir" style="width: 0%"
                         data-pendaftaran-id="{{ $daftar->id }}"
                         title="Formulir"></div>
                </div>
                <div class="pend-progress-labels-row">
                    <small class="text-muted">Formulir & Dokumen</small>
                    <small class="text-muted"><i class="bi bi-arrow-right-circle me-1"></i>Lihat detail untuk progress penuh</small>
                </div>
            </div>
            @endif

        </div>

        {{-- Card Footer --}}
        <div class="pend-card-footer">
            {{-- Tombol aksi utama --}}
            @if($isDraft)
                <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                   class="btn btn-success btn-sm fw-semibold pend-btn-main">
                    <i class="bi bi-pencil-square me-1"></i>Lanjutkan Pengisian
                </a>
            @else
                <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                   class="btn btn-outline-primary btn-sm fw-semibold pend-btn-main">
                    <i class="bi bi-eye me-1"></i>Lihat Detail
                </a>
            @endif

            {{-- Tombol Bayar (hanya jika submit — reminder pembayaran) --}}
            @if($isSubmit)
                <a href="/ppdb/pembayaran/{{ $daftar->id }}"
                   class="btn btn-warning btn-sm fw-semibold">
                    <i class="bi bi-credit-card me-1"></i>Bayar
                </a>
            @endif

            {{-- Tombol jalur baru (opsional, jika lulus atau tidak_lulus) --}}
            @if(in_array($daftar->status, ['lulus', 'siswa_tetap']))
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-circle me-1"></i>Selesai
                </span>
            @endif
        </div>

    </div>
    @endforeach
</div>

{{-- CTA bawah --}}
<div class="text-center mt-5">
    <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-outline-success px-5 py-2 fw-semibold">
        <i class="bi bi-plus-circle me-2"></i>Daftar di Jalur Lain
    </a>
</div>

@endif

@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   PAGE HEADER
═══════════════════════════════════════════════════════════════════ */
.pend-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #15803d 0%, #059669 50%, #0d9488 100%);
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 6px 24px rgba(22,163,74,0.18);
}
.pend-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.pend-header-icon {
    width: 54px; height: 54px;
    border-radius: 14px;
    background: rgba(255,255,255,0.18);
    border: 2px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: #fff;
    flex-shrink: 0;
}
.pend-page-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 2px;
    line-height: 1.2;
}
.pend-page-subtitle {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.78);
    margin: 0;
}
.pend-header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.btn-daftar-baru {
    background: rgba(255,255,255,0.2);
    border: 1.5px solid rgba(255,255,255,0.5);
    color: #fff;
    font-weight: 700;
    transition: background 0.2s, transform 0.15s;
}
.btn-daftar-baru:hover {
    background: rgba(255,255,255,0.32);
    color: #fff;
    transform: translateY(-1px);
}
.pend-header-actions .btn-outline-secondary {
    border-color: rgba(255,255,255,0.4);
    color: rgba(255,255,255,0.85);
}
.pend-header-actions .btn-outline-secondary:hover {
    background: rgba(255,255,255,0.12);
    color: #fff;
}

/* ═══════════════════════════════════════════════════════════════
   EMPTY STATE
═══════════════════════════════════════════════════════════════════ */
.empty-state-wrapper {
    display: flex;
    justify-content: center;
    padding: 32px 0 48px;
}
.empty-state-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 48px 40px;
    text-align: center;
    max-width: 520px;
    width: 100%;
}
.empty-illustration { margin-bottom: 20px; }
.empty-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #111827;
    margin-bottom: 10px;
}
.empty-desc {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 28px;
}
.empty-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
}
.empty-actions .btn-success {
    background: linear-gradient(135deg, #16a34a, #059669);
    border: none;
    box-shadow: 0 4px 14px rgba(22,163,74,0.3);
    transition: transform 0.2s, box-shadow 0.2s;
}
.empty-actions .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22,163,74,0.4);
}
.empty-hint {
    font-size: 0.78rem;
    color: #9ca3af;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

/* ═══════════════════════════════════════════════════════════════
   SUMMARY BAR
═══════════════════════════════════════════════════════════════════ */
.pend-summary-bar {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 14px 22px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.summary-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 12px;
    border-right: 1px solid #f3f4f6;
}
.summary-stat:last-child { border-right: none; }
.summary-num {
    font-size: 1.35rem;
    font-weight: 800;
    color: #111827;
    line-height: 1.2;
}
.summary-lbl {
    font-size: 0.7rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

/* ═══════════════════════════════════════════════════════════════
   PENDAFTARAN GRID & CARDS
═══════════════════════════════════════════════════════════════════ */
.pendaftaran-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.pend-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
}
.pend-card:hover {
    border-color: #86efac;
    box-shadow: 0 6px 20px rgba(22,163,74,0.1);
    transform: translateY(-1px);
}
.pend-card--draft { border-left: 4px solid #9ca3af; }
.pend-card--lulus { border-left: 4px solid #16a34a; }

/* Card Header */
.pend-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: #f9fafb;
    border-bottom: 1px solid #f3f4f6;
    gap: 12px;
}
.pend-no-label {
    display: block;
    font-size: 0.67rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.pend-no-value {
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    letter-spacing: 1.5px;
}
.pend-status-badge {
    font-size: 0.75rem;
    padding: 6px 12px;
    border-radius: 20px;
    white-space: nowrap;
}

/* Card Body */
.pend-card-body {
    padding: 18px 20px;
}
.pend-jalur-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}
.pend-jalur-icon {
    width: 36px; height: 36px;
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #16a34a;
    font-size: 1rem;
    flex-shrink: 0;
}
.pend-jalur-nama {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
}
.pend-jalur-meta {
    font-size: 0.775rem;
    color: #6b7280;
    margin-top: 2px;
}
.pend-info-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 4px;
}
.pend-info-item {
    display: inline-flex;
    align-items: center;
    font-size: 0.8125rem;
    color: #374151;
}

/* Progress Section */
.pend-progress-section {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px dashed #e5e7eb;
}
.pend-progress-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.pend-progress-hint {
    font-size: 0.7rem;
    font-weight: 400;
    color: #9ca3af;
}
.pend-progress-track {
    height: 6px;
    background: #f3f4f6;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 4px;
}
.pend-progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #6b7280, #9ca3af);
    border-radius: 3px;
    transition: width 0.8s ease;
    min-width: 4px;
}
.pend-progress-labels-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Card Footer */
.pend-card-footer {
    padding: 12px 20px;
    background: #fafafa;
    border-top: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.pend-btn-main {
    font-size: 0.8125rem;
}

@media (max-width: 576px) {
    .pend-page-header { padding: 18px 20px; }
    .pend-page-title  { font-size: 1.1rem; }
    .pend-header-actions .btn-outline-secondary { display: none; }
    .empty-state-card { padding: 32px 24px; }
    .pend-card-header { flex-direction: column; align-items: flex-start; }
    .pend-status-badge { align-self: flex-start; }
}
</style>
@endpush

@push('scripts')
<script>
// Tidak ada JS kompleks di halaman ini — progress penuh ada di halaman show
</script>
@endpush
