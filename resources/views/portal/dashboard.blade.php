{{-- ════════════════════════════════════════════════════
     dashboard.blade.php — Halaman Dashboard Peserta
     Layout: layouts.portal (portal.blade.php)
     ════════════════════════════════════════════════════ --}}
@extends('layouts.portal')

@section('title', 'Dashboard Peserta')

{{-- ════════════════════════════════════════════════════
     STYLES
     [DIUBAH] Dipindah ke @push('styles') agar masuk
     ke <head> via layout, sesuai standar yang sama
     dengan index.blade.php. Semua variabel mengacu
     ke design tokens dari portal.blade.php.
     ════════════════════════════════════════════════════ --}}
@push('styles')
    <style>

    /* ══════════════════════════════════════════════════════
       GREETING CARD
       [DIUBAH] Gradient lebih dalam, ditambah pseudo-element
       dekoratif berupa lingkaran besar di kanan agar ada
       kedalaman visual tanpa menggunakan aset gambar.
       ══════════════════════════════════════════════════════ */
    .greeting-card {
        background: linear-gradient(135deg, var(--c-primary-dark) 0%, var(--c-accent-teal) 100%);
        border-radius: var(--r-2xl);
        padding: 26px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: var(--shadow-primary);
        position: relative;
        overflow: hidden;
    }
    /* [BARU] Lingkaran dekoratif sebagai elemen background */
    .greeting-card::before,
    .greeting-card::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        opacity: .1;
        pointer-events: none;
    }
    .greeting-card::before {
        width: 220px; height: 220px;
        background: #fff;
        top: -80px; right: 60px;
    }
    .greeting-card::after {
        width: 140px; height: 140px;
        background: #fff;
        bottom: -60px; right: 20px;
    }

    .greeting-left {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        position: relative; /* naik di atas pseudo-element */
        z-index: 1;
    }
    .greeting-right { position: relative; z-index: 1; }

    /* Avatar inisial di greeting */
    .greeting-avatar {
        width: 58px;
        height: 58px;
        /* [DIUBAH] Konsisten dengan .user-avatar di navbar */
        background: linear-gradient(135deg, rgba(255,255,255,.3) 0%, rgba(255,255,255,.15) 100%);
        border: 2px solid rgba(255,255,255,.45);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }

    .greeting-time {
        font-size: .72rem;
        color: rgba(255,255,255,.78);
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 2px;
    }
    .greeting-name {
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 6px;
        /* [BARU] Slight text-shadow agar terbaca di atas bg pattern */
        text-shadow: 0 1px 3px rgba(0,0,0,.1);
    }

    /* Badge status di greeting */
    .status-badge {
        display: inline-flex;
        align-items: center;
        font-size: .72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 99px;
        gap: 4px;
    }
    .status-active  { background: rgba(255,255,255,.2);  color: #fff; }
    .status-info    { background: rgba(255,255,255,.15); color: #d1fae5; }
    .status-warning { background: rgba(251,191,36,.25);  color: #fef3c7; }

    /* Tombol di sisi kanan greeting */
    .greeting-right .btn {
        border-color: rgba(255,255,255,.5);
        color: #fff;
        font-size: .84rem;
    }
    .greeting-right .btn:hover {
        background: rgba(255,255,255,.18);
        color: #fff;
        border-color: rgba(255,255,255,.7);
    }

    /* ══════════════════════════════════════════════════════
       PROFILE ALERT (kelengkapan profil)
       [DIUBAH] Border-left diganti dengan border penuh +
       ikon lebih besar. Rasio warna sedikit diperhalus.
       ══════════════════════════════════════════════════════ */
    .profile-alert {
        background: #fffbeb;
        border: 1.5px solid #fcd34d;
        border-left: 5px solid #f59e0b;
        border-radius: var(--r-lg);
        padding: 20px 24px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .profile-alert-icon {
        font-size: 1.75rem;
        color: #f59e0b;
        flex-shrink: 0;
        line-height: 1;
        margin-top: 2px;
    }
    .profile-alert-body { flex: 1; min-width: 0; }
    .profile-alert-title {
        font-weight: 700;
        color: #92400e;
        font-size: .9375rem;
        margin-bottom: 4px;
    }
    .profile-alert-desc {
        font-size: .8125rem;
        color: #78350f;
        margin-bottom: 12px;
        line-height: 1.55;
    }

    /* Progress bar alert kelengkapan */
    .profile-progress-track {
        height: 8px;
        background: #fef3c7;
        border-radius: 99px;
        overflow: hidden;
    }
    .profile-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        border-radius: 99px;
        transition: width 1s cubic-bezier(.4,0,.2,1);
    }

    /* Dots aspek kelengkapan */
    .profile-aspek-dots {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }
    .aspek-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #fef9c3;
        border: 1.5px solid #fcd34d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        color: #d97706;
        flex-shrink: 0;
        transition: background var(--t-normal), border-color var(--t-normal);
    }
    .aspek-dot.done {
        background: var(--c-primary);
        border-color: var(--c-primary-dark);
        color: #fff;
    }

    /* ══════════════════════════════════════════════════════
       PORTAL CARD (override untuk dashboard — menghapus
       duplikasi dengan .portal-card global di layout)
       [DIUBAH] Menggunakan token yang sama dari layout,
       hanya menambahkan .card-header-portal khas dashboard
       ══════════════════════════════════════════════════════ */
    .portal-card {
        background: var(--c-surface);
        border-radius: var(--r-lg);
        border: 1px solid var(--c-border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .card-header-portal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        background: var(--c-primary-50);
        border-bottom: 1px solid var(--c-primary-light);
        font-weight: 700;
        color: var(--c-primary-dark);
        font-size: .855rem;
        gap: 8px;
    }

    /* ══════════════════════════════════════════════════════
       INFO PPDB AKTIF
       ══════════════════════════════════════════════════════ */
    .ppdb-info-grid  { display: flex; flex-direction: column; gap: 10px; }
    .ppdb-info-item  { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; }
    .ppdb-info-label {
        font-size: .73rem;
        color: var(--c-text-subtle);
        text-transform: uppercase;
        letter-spacing: .5px;
        flex-shrink: 0;
    }
    .ppdb-info-val {
        font-size: .855rem;
        color: var(--c-text);
        text-align: right;
    }

    /* Countdown deadline */
    .countdown-box {
        background: linear-gradient(135deg, var(--c-primary-50), var(--c-primary-light));
        border: 1px solid #bbf7d0;
        border-radius: var(--r-md);
        padding: 12px 16px;
        margin-top: 14px;
    }
    .countdown-label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--c-text-muted);
        margin-bottom: 6px;
    }
    .countdown-timer {
        display: flex;
        gap: 14px;
        align-items: baseline;
    }
    .countdown-timer span {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--c-primary-dark);
        font-variant-numeric: tabular-nums;
        line-height: 1;
    }
    .countdown-timer small {
        font-size: .6rem;
        color: var(--c-text-muted);
        margin-left: 2px;
    }

    /* Jalur pendaftaran mini list */
    .jalur-mini-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 0;
        border-bottom: 1px solid var(--c-border-light);
        font-size: .82rem;
        color: var(--c-text);
    }
    .jalur-mini-item:last-child { border-bottom: none; }
    .jalur-mini-kode {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        background: var(--c-primary-light);
        color: var(--c-primary-dark);
        padding: 2px 7px;
        border-radius: var(--r-sm);
        flex-shrink: 0;
        letter-spacing: .3px;
    }

    /* ══════════════════════════════════════════════════════
       QUICK ACTIONS
       [DIUBAH] Efek hover dibuat lebih halus dan konsisten
       dengan tombol di index.blade.php (translateX + shadow)
       ══════════════════════════════════════════════════════ */
    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .55rem .9rem;
        border-radius: var(--r-sm);
        font-size: .84rem;
        font-weight: 500;
        text-decoration: none;
        transition: transform var(--t-fast), box-shadow var(--t-fast), background var(--t-fast);
    }
    .quick-action-btn:hover {
        transform: translateX(3px);
        box-shadow: var(--shadow-sm);
    }
    .quick-action-btn i { font-size: 1.1rem; flex-shrink: 0; }

    /* ══════════════════════════════════════════════════════
       EMPTY STATE
       [DIUBAH] Padding distandarisasi, font-size konsisten
       ══════════════════════════════════════════════════════ */
    .empty-state { text-align: center; padding: 44px 24px; }
    .empty-state-icon { margin-bottom: 16px; }
    .empty-state-title {
        font-weight: 700;
        color: var(--c-text);
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
    .empty-state-desc {
        color: var(--c-text-muted);
        font-size: .875rem;
        max-width: 360px;
        margin: 0 auto 22px;
        line-height: 1.6;
    }

    /* ══════════════════════════════════════════════════════
       PENDAFTARAN LIST
       [DIUBAH] Radius & border diseragamkan dengan
       .profil-card dari index.blade.php. Hover effect
       menggunakan border + background konsisten.
       ══════════════════════════════════════════════════════ */
    .pendaftaran-list { display: flex; flex-direction: column; gap: 10px; }

    .pendaftaran-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: var(--c-bg);
        border: 1.5px solid var(--c-border);
        border-radius: var(--r-md);
        padding: 15px 18px;
        transition: border-color var(--t-fast), background var(--t-fast), box-shadow var(--t-fast);
    }
    .pendaftaran-card:hover {
        border-color: #86efac;
        background: var(--c-primary-50);
        box-shadow: var(--shadow-sm);
    }
    .pendaftaran-card-left  { flex: 1; min-width: 0; }
    .pendaftaran-card-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }

    /* Nomor pendaftaran */
    .no-label {
        display: block;
        font-size: .68rem;
        color: var(--c-text-subtle);
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 1px;
    }
    .no-value {
        font-size: .975rem;
        font-weight: 700;
        color: var(--c-text);
        /* [DIUBAH] Menggunakan Plus Jakarta Sans mono-numeric daripada
           Courier New agar selaras dengan brand font halaman ini */
        font-variant-numeric: tabular-nums;
        letter-spacing: .5px;
    }

    /* Meta info (jalur, tanggal) */
    .meta-jalur,
    .meta-tgl {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .79rem;
        color: var(--c-text-muted);
        margin-right: 10px;
        margin-top: 5px;
    }

    /* ══════════════════════════════════════════════════════
       RESPONSIF
       ══════════════════════════════════════════════════════ */
    @media (max-width: 575px) {
        .greeting-card  { padding: 18px 20px; }
        .greeting-name  { font-size: 1.05rem; }
        .greeting-avatar { width: 48px; height: 48px; font-size: 1.25rem; }

        /* [BARU] Pendaftaran card stack vertikal di layar sangat kecil */
        .pendaftaran-card { flex-direction: column; align-items: flex-start; }
        .pendaftaran-card-right { flex-direction: row; align-items: center; width: 100%; justify-content: space-between; }

        .profile-alert { padding: 16px; gap: 12px; }
        .profile-alert-icon { font-size: 1.4rem; }
    }
    </style>
@endpush

@section('content')

    {{-- ════════════════════════════════════════════════════
         GREETING CARD
         ════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="greeting-card">

                <div class="greeting-left">
                    {{-- Avatar inisial --}}
                    <div class="greeting-avatar" aria-hidden="true">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    <div>
                        {{-- Sapaan waktu diisi oleh JS --}}
                        <div class="greeting-time" id="greeting-time" aria-live="polite"></div>
                        <h2 class="greeting-name">{{ $user->name }}</h2>

                        {{-- Status badges --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                            <span class="status-badge status-active">
                                <i class="bi bi-check-circle" aria-hidden="true"></i>Akun Aktif
                            </span>
                            @if($peserta)
                                <span class="status-badge status-info">
                                    <i class="bi bi-person-badge" aria-hidden="true"></i>Data Peserta Tersedia
                                </span>
                            @else
                                <span class="status-badge status-warning">
                                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>Profil Belum Dilengkapi
                                </span>
                            @endif
                            <span class="status-badge" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.85)">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                {{ now()->translatedFormat('l, d F Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tombol Beranda PPDB — disembunyikan di mobile --}}
                <div class="greeting-right d-none d-md-block">
                    <a href="{{ route('ppdb.beranda') }}" class="btn btn-sm">
                        <i class="bi bi-house me-1" aria-hidden="true"></i>Beranda PPDB
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
         ALERT KELENGKAPAN PROFIL
         Hanya muncul jika profil belum 100% terisi.
         ════════════════════════════════════════════════════ --}}
    @if($progress_profil['persen'] < 100)
        <div class="row mb-4">
            <div class="col-12">
                <div class="profile-alert" role="alert">
                    <div class="profile-alert-icon" aria-hidden="true">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div class="profile-alert-body">
                        {{-- Judul + counter aspek --}}
                        <div class="profile-alert-title">
                            Profil Anda {{ $progress_profil['persen'] }}% Lengkap
                            <span class="ms-2 fw-normal text-muted" style="font-size:.78rem">
                                ({{ $progress_profil['lengkap'] }}/{{ $progress_profil['total'] }} aspek terisi)
                            </span>
                        </div>

                        {{-- Deskripsi item yang kurang --}}
                        <p class="profile-alert-desc">
                            Lengkapi data profil untuk dapat melanjutkan proses pendaftaran PPDB.
                            Data yang belum lengkap:
                            <strong>{{ implode(', ', array_keys(array_filter($progress_profil['aspek'], fn($v) => !$v))) }}</strong>.
                        </p>

                        {{-- Progress bar — animasi awal dari 0 via JS --}}
                        <div class="profile-progress-track mb-2">
                            <div class="profile-progress-fill"
                                 id="profile-progress-bar"
                                 style="width: {{ $progress_profil['persen'] }}%"
                                 role="progressbar"
                                 aria-valuenow="{{ $progress_profil['persen'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>

                        {{-- Dots aspek + tombol CTA --}}
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div class="profile-aspek-dots">
                                @foreach($progress_profil['aspek'] as $aspek => $done)
                                    <div class="aspek-dot {{ $done ? 'done' : '' }}"
                                         title="{{ ucfirst($aspek) }}"
                                         aria-label="{{ ucfirst($aspek) }}: {{ $done ? 'Lengkap' : 'Belum diisi' }}">
                                        @if($done)
                                            <i class="bi bi-check" aria-hidden="true"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('ppdb.profil.index') }}" class="btn btn-warning btn-sm fw-semibold">
                                <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Lengkapi Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════
         ROW UTAMA: Kolom Info PPDB + Kolom Pendaftaran Saya
         ════════════════════════════════════════════════════ --}}
    <div class="row g-4">

        {{-- ══════════════════════════════
             KOLOM KIRI — Info & Quick Actions
             ══════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- ── Card Info PPDB Aktif ── --}}
            @if($pembukaan)
                <div class="portal-card mb-4">
                    <div class="card-header-portal">
                        <span><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Info PPDB Aktif</span>
                        <span class="badge bg-success" style="font-size:.68rem">
                            <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle" aria-hidden="true"></i>Buka
                        </span>
                    </div>
                    <div class="card-body p-4">

                        {{-- Grid informasi dasar --}}
                        <div class="ppdb-info-grid">
                            <div class="ppdb-info-item">
                                <span class="ppdb-info-label">Nama Pembukaan</span>
                                <span class="ppdb-info-val fw-semibold">{{ $pembukaan->nama }}</span>
                            </div>
                            <div class="ppdb-info-item">
                                <span class="ppdb-info-label">Tahun Pelajaran</span>
                                <span class="ppdb-info-val">{{ $pembukaan->tahunPelajaran?->nama ?? '—' }}</span>
                            </div>
                            <div class="ppdb-info-item">
                                <span class="ppdb-info-label">Periode</span>
                                <span class="ppdb-info-val">
                                    {{ $pembukaan->mulai?->format('d M') }} – {{ $pembukaan->selesai?->format('d M Y') }}
                                </span>
                            </div>
                            <div class="ppdb-info-item">
                                <span class="ppdb-info-label">Jalur Tersedia</span>
                                <span class="ppdb-info-val">{{ $pembukaan->jalurPendaftaran->count() }} jalur</span>
                            </div>
                        </div>

                        {{-- Countdown deadline --}}
                        @if($pembukaan->selesai?->isFuture())
                            <div class="countdown-box">
                                <div class="countdown-label">Tutup dalam:</div>
                                <div class="countdown-timer"
                                     id="countdown-timer"
                                     data-deadline="{{ $pembukaan->selesai?->toIso8601String() }}"
                                     aria-live="off">
                                    <span id="cd-days">--</span><small>hari</small>
                                    <span id="cd-hours">--</span><small>jam</small>
                                    <span id="cd-mins">--</span><small>mnt</small>
                                </div>
                            </div>
                        @endif

                        {{-- Daftar jalur pendaftaran --}}
                        @if($pembukaan->jalurPendaftaran->isNotEmpty())
                            <div class="mt-3">
                                @foreach($pembukaan->jalurPendaftaran as $jalur)
                                    <div class="jalur-mini-item">
                                        <span class="jalur-mini-kode">{{ $jalur->kode_jalur }}</span>
                                        <span class="flex-grow-1">{{ $jalur->nama }}</span>
                                        <span style="font-size:.73rem;color:var(--c-text-muted)">{{ $jalur->kuota }} siswa</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            @else
                {{-- Tidak ada PPDB aktif --}}
                <div class="portal-card mb-4">
                    <div class="card-header-portal">
                        <span><i class="bi bi-info-circle me-2" aria-hidden="true"></i>Info PPDB</span>
                    </div>
                    <div class="card-body p-4 text-center py-5">
                        <div style="font-size:2.5rem;margin-bottom:12px" aria-hidden="true">📋</div>
                        <p class="text-muted mb-0" style="font-size:.875rem">Belum ada pembukaan PPDB yang aktif saat ini.</p>
                    </div>
                </div>
            @endif

            {{-- ── Card Quick Actions ── --}}
            <div class="portal-card">
                <div class="card-header-portal">
                    <span><i class="bi bi-lightning me-2" aria-hidden="true"></i>Akses Cepat</span>
                </div>
                <div class="card-body p-3">
                    <nav class="d-grid gap-2" aria-label="Akses cepat">
                        <a href="{{ route('ppdb.pendaftaran.index') }}"
                           class="quick-action-btn btn btn-outline-success">
                            <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                            <span>Daftar Sekarang</span>
                        </a>
                        <a href="{{ route('ppdb.pengumuman.index') }}"
                           class="quick-action-btn btn btn-outline-primary">
                            <i class="bi bi-megaphone" aria-hidden="true"></i>
                            <span>Lihat Pengumuman</span>
                        </a>
                        <a href="{{ route('ppdb.profil.index') }}"
                           class="quick-action-btn btn btn-outline-secondary">
                            <i class="bi bi-person-circle" aria-hidden="true"></i>
                            <span>Profil Saya</span>
                        </a>
                        <a href="{{ route('ppdb.beranda') }}"
                           class="quick-action-btn btn btn-outline-info">
                            <i class="bi bi-house" aria-hidden="true"></i>
                            <span>Beranda PPDB</span>
                        </a>
                    </nav>
                </div>
            </div>

        </div>{{-- /col-lg-4 --}}

        {{-- ══════════════════════════════
             KOLOM KANAN — Pendaftaran Saya
             ══════════════════════════════ --}}
        <div class="col-lg-8">
            <div class="portal-card h-100">
                <div class="card-header-portal">
                    <span><i class="bi bi-file-earmark-text me-2" aria-hidden="true"></i>Pendaftaran Saya</span>
                    @if($pendaftaran_list->isNotEmpty())
                        <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus me-1" aria-hidden="true"></i>Daftar Baru
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">

                    @if($pendaftaran_list->isEmpty())
                        {{-- ── Empty State ── --}}
                        <div class="empty-state">
                            <div class="empty-state-icon" aria-hidden="true">
                                {{-- Ilustrasi SVG inline — tidak berubah dari aslinya --}}
                                <svg width="120" height="100" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect x="20" y="15" width="80" height="70" rx="8" fill="#f0fdf4" stroke="#bbf7d0" stroke-width="2"/>
                                    <rect x="34" y="28" width="52" height="6" rx="3" fill="#bbf7d0"/>
                                    <rect x="34" y="40" width="40" height="5" rx="2.5" fill="#d1fae5"/>
                                    <rect x="34" y="51" width="45" height="5" rx="2.5" fill="#d1fae5"/>
                                    <rect x="34" y="62" width="30" height="5" rx="2.5" fill="#d1fae5"/>
                                    <circle cx="90" cy="72" r="18" fill="#16a34a"/>
                                    <line x1="84" y1="72" x2="96" y2="72" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                                    <line x1="90" y1="66" x2="90" y2="78" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h5 class="empty-state-title">Belum Ada Pendaftaran</h5>
                            <p class="empty-state-desc">
                                Anda belum memiliki pendaftaran PPDB.
                                @if($pembukaan)
                                    Mulai daftar sekarang untuk tahun ajaran <strong>{{ $pembukaan->tahunPelajaran?->nama }}</strong>.
                                @else
                                    Pantau terus untuk info pembukaan pendaftaran.
                                @endif
                            </p>
                            @if($pembukaan)
                                <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-success fw-semibold px-4">
                                    <i class="bi bi-file-earmark-plus me-2" aria-hidden="true"></i>Mulai Pendaftaran
                                </a>
                            @endif
                        </div>

                    @else
                        {{-- ── List Pendaftaran ── --}}
                        <div class="pendaftaran-list">
                            @foreach($pendaftaran_list as $daftar)
                                @php
                                    // Map status ke label, warna badge, dan ikon Bootstrap Icons
                                    $statusMap = [
                                        'draft' => ['label' => 'Draft', 'color' => 'secondary', 'icon' => 'bi-file'],
                                        'submitted' => ['label' => 'Dikirim', 'color' => 'primary', 'icon' => 'bi-send'],
                                        'verifikasi' => ['label' => 'Diverifikasi', 'color' => 'info', 'icon' => 'bi-search'],
                                        'lulus' => ['label' => 'Lulus', 'color' => 'success', 'icon' => 'bi-trophy'],
                                        'tidak_lulus' => ['label' => 'Tidak Lulus', 'color' => 'danger', 'icon' => 'bi-x-circle'],
                                        'daftar_ulang' => ['label' => 'Daftar Ulang', 'color' => 'warning', 'icon' => 'bi-arrow-repeat'],
                                        'siswa_tetap' => ['label' => 'Siswa Tetap', 'color' => 'success', 'icon' => 'bi-mortarboard'],
                                    ];
                                    $st = $statusMap[$daftar->status]
                                        ?? ['label' => $daftar->status, 'color' => 'secondary', 'icon' => 'bi-circle'];
                                @endphp

                                <div class="pendaftaran-card">

                                    {{-- Info kiri: nomor + meta --}}
                                    <div class="pendaftaran-card-left">
                                        <div class="pendaftaran-no">
                                            <span class="no-label">No. Pendaftaran</span>
                                            <span class="no-value">{{ $daftar->no_pendaftaran ?? '—' }}</span>
                                        </div>
                                        <div class="pendaftaran-meta">
                                            <span class="meta-jalur">
                                                <i class="bi bi-diagram-3" aria-hidden="true"></i>
                                                {{ $daftar->jalurPendaftaran?->nama ?? '—' }}
                                            </span>
                                            <span class="meta-tgl">
                                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                                {{ $daftar->created_at?->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Info kanan: badge status + tombol aksi --}}
                                    <div class="pendaftaran-card-right">
                                        <span class="badge bg-{{ $st['color'] }}">
                                            <i class="bi {{ $st['icon'] }} me-1" aria-hidden="true"></i>{{ $st['label'] }}
                                        </span>
                                        <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                                           class="btn btn-sm btn-outline-success">
                                            @if(in_array($daftar->status, ['draft', 'submitted']))
                                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>Lanjutkan
                                            @else
                                                <i class="bi bi-eye me-1" aria-hidden="true"></i>Detail
                                            @endif
                                        </a>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>{{-- /col-lg-8 --}}

    </div>{{-- /.row --}}

@endsection

{{-- ════════════════════════════════════════════════════
     SCRIPTS
     [DIUBAH] Logika tidak berubah sama sekali.
     Hanya ditambahkan komentar dan dirapikan
     formatnya agar selaras dengan index.blade.php.
     ════════════════════════════════════════════════════ --}}
@push('scripts')
    <script>
    (function () {
        'use strict';

        /* ── Sapaan berdasarkan jam ───────────────────────────── */
        const hour  = new Date().getHours();
        const greet = hour < 11 ? '☀️ Selamat Pagi'
                    : hour < 15 ? '🌤️ Selamat Siang'
                    : hour < 18 ? '🌇 Selamat Sore'
                    :              '🌙 Selamat Malam';
        const greetEl = document.getElementById('greeting-time');
        if (greetEl) greetEl.textContent = greet;

        /* ── Countdown deadline PPDB ─────────────────────────── */
        const timerEl = document.getElementById('countdown-timer');
        if (timerEl) {
            const deadline = new Date(timerEl.dataset.deadline);
            const cdDays   = document.getElementById('cd-days');
            const cdHours  = document.getElementById('cd-hours');
            const cdMins   = document.getElementById('cd-mins');

            function updateCountdown() {
                const diff = deadline - new Date();
                if (diff <= 0) {
                    cdDays.textContent = cdHours.textContent = cdMins.textContent = '00';
                    return;
                }
                cdDays.textContent  = String(Math.floor(diff / 86400000)).padStart(2, '0');
                cdHours.textContent = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                cdMins.textContent  = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
            }

            updateCountdown();
            setInterval(updateCountdown, 60000);
        }

        /* ── Animasi progress bar (masuk dari 0) ─────────────── */
        const bar = document.getElementById('profile-progress-bar');
        if (bar) {
            const target = bar.style.width;
            bar.style.width = '0';
            // Tunda sedikit agar transisi CSS terlihat
            setTimeout(() => { bar.style.width = target; }, 300);
        }

    })();
    </script>
@endpush