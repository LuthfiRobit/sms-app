@extends('layouts.portal')

@section('title', 'Pendaftaran Saya — PPDB')

@push('styles')
    <style>
        /* PENDAFTARAN PAGE STYLES Layout: Header card + Content area Menggunakan design tokens dari portal.blade.php */

        /* PAGE HEADER CARD - Adopsi style profile-greeting-card */
        .pendaftaran-greeting-card {
            background: linear-gradient(135deg, var(--color-primary-hover) 0%, var(--color-accent-teal) 100%);
            border-radius: var(--radius-xl);
            padding: clamp(1.5rem, 3vw, 2.5rem);
            box-shadow: var(--shadow-primary);
            position: relative;
            overflow: hidden;
            margin-bottom: clamp(1.5rem, 3vw, 2rem);
        }

        /* Decorative circles */
        .pendaftaran-greeting-card::before,
        .pendaftaran-greeting-card::after {
            content: '';
            position: absolute;
            border-radius: var(--radius-full);
            opacity: 0.08;
            pointer-events: none;
            background: #fff;
        }

        .pendaftaran-greeting-card::before {
            width: 320px;
            height: 320px;
            top: -120px;
            left: -60px;
        }

        .pendaftaran-greeting-card::after {
            width: 200px;
            height: 200px;
            bottom: -80px;
            left: 100px;
        }

        /* Inner container */
        .pendaftaran-greeting-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }

        /* LEFT SECTION - Info Pendaftaran (adopsi profile-completion style) */
        .pendaftaran-info-section {
            flex: 1;
            min-width: 280px;
        }

        .pendaftaran-header-content {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .pendaftaran-header-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.18);
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .pendaftaran-header-text {
            flex: 1;
        }

        .pendaftaran-page-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 900;
            color: #fff;
            margin: 0 0 0.5rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            line-height: 1.2;
        }

        .pendaftaran-page-subtitle {
            font-size: clamp(0.9rem, 2vw, 1rem);
            color: rgba(255, 255, 255, 0.88);
            margin: 0;
            line-height: 1.5;
        }

        .pendaftaran-actions {
            margin-top: 1.25rem;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-daftar-baru {
            background: rgba(255, 255, 255, 0.95);
            color: var(--color-primary) !important;
            border: 2px solid transparent;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-normal);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-daftar-baru:hover {
            background: #fff;
            color: var(--color-primary-hover) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-dashboard-link {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            color: #fff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .btn-dashboard-link:hover {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff !important;
        }

        /* RIGHT SECTION - User Profile Info */
        .user-profile-section {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            min-width: 280px;
        }

        .user-foto-wrapper {
            width: 72px;
            height: 72px;
            flex-shrink: 0;
        }

        .user-foto-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--radius-full);
            box-shadow: 0 0 0 3px #fff, 0 0 0 5px rgba(255, 255, 255, 0.3);
        }

        .user-info-text {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: #fff;
            margin: 0 0 0.375rem;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .user-email i {
            font-size: 0.9rem;
        }

        .user-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .user-status-badge i {
            font-size: 0.6rem;
        }

        /* Mobile: Stack sections */
        @media (max-width: 991.98px) {
            .pendaftaran-greeting-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .pendaftaran-info-section,
            .user-profile-section {
                min-width: 0;
                width: 100%;
            }

            .user-profile-section {
                justify-content: center;
            }
        }

        @media (max-width: 575.98px) {
            .pendaftaran-header-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .pendaftaran-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn-daftar-baru,
            .btn-dashboard-link {
                width: 100%;
                justify-content: center;
            }

            .user-profile-section {
                flex-direction: column;
                text-align: center;
            }

            .user-name,
            .user-email {
                overflow: visible;
                white-space: normal;
            }
        }

        /* EMPTY STATE */
        .empty-state-wrapper {
            display: flex;
            justify-content: center;
            padding: 2rem 0 3rem;
        }

        .empty-state-card {
            background: var(--color-surface);
            border: 2px solid var(--color-border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 540px;
            width: 100%;
        }

        .empty-illustration {
            margin-bottom: 1.5rem;
            animation: emptyFloat 3s ease-in-out infinite;
        }

        @keyframes emptyFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .empty-title {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--color-text);
            margin-bottom: 0.75rem;
        }

        .empty-desc {
            font-size: 0.95rem;
            color: var(--color-text-muted);
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .empty-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .empty-actions .btn-success {
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border: none;
            box-shadow: var(--shadow-primary);
            transition: all var(--transition-normal);
            font-weight: 700;
        }

        .empty-actions .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4);
        }

        .empty-hint {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            padding-top: 1.25rem;
            border-top: 1px solid var(--color-border-light);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        @media (max-width: 575.98px) {
            .empty-state-card {
                padding: 2rem 1.5rem;
            }
        }

        /* SUMMARY BAR - Statistics */
        .pend-summary-bar {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
            background: var(--color-surface);
            border: 2px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }

        .summary-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 1rem;
            border-right: 2px solid var(--color-border-light);
        }

        .summary-stat:last-child {
            border-right: none;
        }

        .summary-num {
            font-size: 1.75rem;
            font-weight: 900;
            color: var(--color-text);
            line-height: 1.2;
        }

        .summary-lbl {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        /* PENDAFTARAN GRID & CARDS */
        .pendaftaran-grid {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .pend-card {
            background: var(--color-surface);
            border: 2px solid var(--color-border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
        }

        .pend-card:hover {
            border-color: var(--color-primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .pend-card--draft {
            border-left-width: 5px;
            border-left-color: #9ca3af;
        }

        .pend-card--lulus {
            border-left-width: 5px;
            border-left-color: var(--color-primary);
        }

        /* Card Header */
        .pend-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.125rem 1.5rem;
            background: linear-gradient(135deg, var(--color-primary-50) 0%, #fff 100%);
            border-bottom: 2px solid var(--color-border-light);
            gap: 1rem;
        }

        .pend-card-no-wrapper {
            display: flex;
            flex-direction: column;
        }

        .pend-no-label {
            font-size: 0.7rem;
            color: var(--color-text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .pend-no-value {
            font-family: 'Courier New', monospace;
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--color-primary);
            letter-spacing: 1px;
        }

        .pend-status-badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            white-space: nowrap;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        /* Card Body */
        .pend-card-body {
            padding: 1.5rem;
        }

        .pend-jalur-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .pend-jalur-icon {
            width: 44px;
            height: 44px;
            background: var(--color-primary-light);
            border: 2px solid #bbf7d0;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-primary-hover);
            font-size: 1.15rem;
            flex-shrink: 0;
            transition: all var(--transition-fast);
        }

        .pend-card:hover .pend-jalur-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        .pend-jalur-nama {
            font-size: 1rem;
            font-weight: 700;
            color: var(--color-text);
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }

        .pend-jalur-meta {
            font-size: 0.825rem;
            color: var(--color-text-muted);
        }

        .pend-info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pend-info-item {
            display: inline-flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--color-text);
        }

        .pend-info-item i {
            color: var(--color-text-muted);
        }

        /* Progress Section */
        .pend-progress-section {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px dashed var(--color-border);
        }

        .pend-progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.625rem;
        }

        .pend-progress-hint {
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--color-text-muted);
        }

        .pend-progress-track {
            height: 8px;
            background: var(--color-border-light);
            border-radius: var(--radius-full);
            overflow: hidden;
            margin-bottom: 0.5rem;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pend-progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            border-radius: var(--radius-full);
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 4px;
        }

        .pend-progress-labels-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pend-progress-labels-row small {
            font-size: 0.75rem;
            color: var(--color-text-muted);
        }

        /* Card Footer */
        .pend-card-footer {
            padding: 1rem 1.5rem;
            background: var(--color-bg);
            border-top: 2px solid var(--color-border-light);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .pend-btn-main {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .pend-btn-main:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        /* Mobile adjustments */
        @media (max-width: 575.98px) {
            .pend-summary-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .summary-stat {
                border-right: none;
                border-bottom: 1px solid var(--color-border-light);
                padding: 0.75rem 0;
            }

            .summary-stat:last-child {
                border-bottom: none;
            }

            .pend-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .pend-status-badge {
                align-self: flex-start;
            }

            .pend-card-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .pend-btn-main {
                width: 100%;
                justify-content: center;
            }
        }

        /* UTILITIES */
        .text-monospace {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
@endpush

@section('content')

    {{-- DATA PREPARATION - TIDAK DIUBAH --}}
    @php
        $pendaftaranList = $data['data'] ?? [];
        $hasPendaftaran = !empty($pendaftaranList) && count($pendaftaranList) > 0;

        // Map status ke warna Badge Bootstrap + label + ikon
        $statusConfig = [
            'draft' => ['color' => 'secondary', 'label' => 'Draft', 'icon' => 'bi-file-earmark', 'desc' => 'Sedang dilengkapi'],
            'submit' => ['color' => 'warning', 'label' => 'Menunggu', 'icon' => 'bi-hourglass-split', 'desc' => 'Menunggu verifikasi'],
            'verifikasi' => ['color' => 'info', 'label' => 'Verifikasi', 'icon' => 'bi-search', 'desc' => 'Sedang diverifikasi admin'],
            'lulus' => ['color' => 'success', 'label' => 'Lulus', 'icon' => 'bi-trophy-fill', 'desc' => 'Selamat, Anda lulus!'],
            'tidak_lulus' => ['color' => 'danger', 'label' => 'Tidak Lulus', 'icon' => 'bi-x-circle-fill', 'desc' => 'Maaf, Anda tidak lulus'],
            'daftar_ulang' => ['color' => 'primary', 'label' => 'Daftar Ulang', 'icon' => 'bi-arrow-repeat', 'desc' => 'Segera lakukan daftar ulang'],
            'siswa_tetap' => ['color' => 'success', 'label' => 'Siswa Tetap', 'icon' => 'bi-mortarboard-fill', 'desc' => 'Selamat bergabung!'],
        ];

        $bulanIndo = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $formatTgl = function ($tgl) use ($bulanIndo) {
            if (!$tgl)
                return '—';
            $dt = $tgl instanceof \Carbon\Carbon ? $tgl : \Carbon\Carbon::parse($tgl);
            return $dt->day . ' ' . $bulanIndo[$dt->month] . ' ' . $dt->year;
        };

        $user = auth()->user();
    @endphp

    {{-- PAGE HEADER CARD Layout: Info Pendaftaran (kiri) + User Profile (kanan) --}}
    <div class="pendaftaran-greeting-card">
        <div class="pendaftaran-greeting-inner">

            {{-- LEFT SECTION: Pendaftaran Info --}}
            <div class="pendaftaran-info-section">
                <div class="pendaftaran-header-content">
                    <div class="pendaftaran-header-icon" aria-hidden="true">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>

                    <div class="pendaftaran-header-text">
                        <h1 class="pendaftaran-page-title">Pendaftaran Saya</h1>
                        <p class="pendaftaran-page-subtitle">
                            Kelola dan pantau status pendaftaran PPDB Anda
                        </p>
                    </div>
                </div>

                <div class="pendaftaran-actions">
                    <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-daftar-baru">
                        <i class="bi bi-plus-circle me-2"></i>
                        Daftar Jalur Baru
                    </a>
                    <a href="{{ route('ppdb.dashboard') }}" class="btn btn-dashboard-link">
                        <i class="bi bi-house me-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>

            {{-- RIGHT SECTION: User Profile --}}
            <div class="user-profile-section">
                <div class="user-foto-wrapper">
                    <img src="{{ $user->peserta?->foto
        ? asset('storage/' . $user->peserta->foto)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=80&background=16a34a&color=fff&bold=true&rounded=true' }}"
                        alt="Foto {{ $user->name }}">
                </div>

                <div class="user-info-text">
                    <div class="user-name">{{ $user->name }}</div>

                    <div class="user-email">
                        <i class="bi bi-envelope-fill"></i>
                        <span>{{ $user->email }}</span>
                    </div>

                    <span class="user-status-badge">
                        <i class="bi bi-circle-fill"></i>
                        Akun Aktif
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4"
            style="border-radius: var(--radius-lg); border: none; box-shadow: var(--shadow-sm);" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4"
            style="border-radius: var(--radius-lg); border: none; box-shadow: var(--shadow-sm);" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4"
            style="border-radius: var(--radius-lg); border: none; box-shadow: var(--shadow-sm);" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- EMPTY STATE atau LIST PENDAFTARAN --}}
    @if(!$hasPendaftaran)
        {{-- EMPTY STATE --}}
        <div class="empty-state-wrapper">
            <div class="empty-state-card">
                {{-- SVG Illustration --}}
                <div class="empty-illustration" aria-hidden="true">
                    <svg width="160" height="140" viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="25" y="20" width="110" height="95" rx="12" fill="#f0fdf4" stroke="#bbf7d0" stroke-width="2" />
                        <rect x="25" y="20" width="110" height="24" rx="12" fill="#dcfce7" stroke="#bbf7d0" stroke-width="2" />
                        <rect x="25" y="32" width="110" height="12" fill="#dcfce7" />
                        <rect x="44" y="58" width="72" height="7" rx="3.5" fill="#bbf7d0" />
                        <rect x="44" y="72" width="55" height="6" rx="3" fill="#d1fae5" />
                        <rect x="44" y="85" width="62" height="6" rx="3" fill="#d1fae5" />
                        <rect x="44" y="98" width="40" height="6" rx="3" fill="#d1fae5" />
                        <circle cx="44" cy="32" r="6" fill="#16a34a" />
                        <line x1="41" y1="32" x2="47" y2="32" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                        <line x1="44" y1="29" x2="44" y2="35" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                        <circle cx="122" cy="108" r="22" fill="#16a34a" opacity="0.15" />
                        <circle cx="122" cy="108" r="16" fill="#16a34a" />
                        <line x1="115" y1="108" x2="129" y2="108" stroke="#fff" stroke-width="2.5" stroke-linecap="round" />
                        <line x1="122" y1="101" x2="122" y2="115" stroke="#fff" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                </div>

                <h2 class="empty-title">Belum Ada Pendaftaran</h2>

                <p class="empty-desc">
                    Anda belum memiliki pendaftaran PPDB aktif.<br>
                    Mulai daftar sekarang sebelum kuota habis!
                </p>

                <div class="empty-actions">
                    <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-success btn-lg px-5 fw-semibold">
                        <i class="bi bi-file-earmark-plus me-2"></i>
                        Mulai Daftar Sekarang
                    </a>
                    <a href="{{ route('ppdb.dashboard') }}" class="btn btn-light px-4">
                        <i class="bi bi-house me-2"></i>
                        Dashboard
                    </a>
                </div>

                <div class="empty-hint">
                    <i class="bi bi-lightbulb text-warning"></i>
                    <span>Tip: Pastikan profil Anda sudah lengkap sebelum mendaftar.</span>
                </div>
            </div>
        </div>

    @else
        {{-- LIST PENDAFTARAN --}}

        {{-- Summary Bar --}}
        <div class="pend-summary-bar">
            <div class="summary-stat">
                <span class="summary-num">{{ count($pendaftaranList) }}</span>
                <span class="summary-lbl">Total Pendaftaran</span>
            </div>

            @php
                $countDraft = collect($pendaftaranList)->where('status', 'draft')->count();
                $countSubmit = collect($pendaftaranList)->whereIn('status', ['submit', 'verifikasi'])->count();
                $countLulus = collect($pendaftaranList)->where('status', 'lulus')->count();
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

        {{-- Pendaftaran Cards Grid --}}
        <div class="pendaftaran-grid">
            @foreach($pendaftaranList as $daftar)
                @php
                    $st = $statusConfig[$daftar->status] ?? [
                        'color' => 'secondary',
                        'label' => $daftar->status,
                        'icon' => 'bi-circle',
                        'desc' => ''
                    ];
                    $isDraft = $daftar->status === 'draft';
                    $isSubmit = $daftar->status === 'submit';
                    $tglDaftar = $formatTgl($daftar->tanggal_daftar ?? $daftar->created_at);
                @endphp

                <article
                    class="pend-card {{ $isDraft ? 'pend-card--draft' : '' }} {{ $daftar->status === 'lulus' ? 'pend-card--lulus' : '' }}">

                    {{-- Card Header --}}
                    <div class="pend-card-header">
                        <div class="pend-card-no-wrapper">
                            <span class="pend-no-label">No. Pendaftaran</span>
                            <span class="pend-no-value">{{ $daftar->no_pendaftaran }}</span>
                        </div>
                        <span class="badge bg-{{ $st['color'] }} pend-status-badge">
                            <i class="bi {{ $st['icon'] }}"></i>
                            {{ $st['label'] }}
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="pend-card-body">

                        {{-- Jalur & Gelombang --}}
                        <div class="pend-jalur-row">
                            <div class="pend-jalur-icon" aria-hidden="true">
                                <i class="bi bi-diagram-3-fill"></i>
                            </div>
                            <div>
                                <div class="pend-jalur-nama">
                                    {{ $daftar->jalurPendaftaran?->nama ?? '—' }}
                                </div>
                                <div class="pend-jalur-meta">
                                    {{ $daftar->tahunPelajaran?->nama ?? ($daftar->jalurPendaftaran?->pembukaanPpdb?->nama ?? '—') }}
                                </div>
                            </div>
                        </div>

                        {{-- Info Row --}}
                        <div class="pend-info-row">
                            <span class="pend-info-item">
                                <i class="bi bi-calendar3 me-1"></i>
                                <span>{{ $tglDaftar }}</span>
                            </span>
                            <span class="pend-info-item">
                                <i class="bi bi-info-circle me-1"></i>
                                <span>{{ $st['desc'] }}</span>
                            </span>
                        </div>

                        {{-- Progress Bar (hanya jika draft) --}}
                        @if($isDraft)
                            <div class="pend-progress-section">
                                <div class="pend-progress-label">
                                    <span>
                                        <i class="bi bi-clipboard-check me-1"></i>
                                        Kelengkapan
                                    </span>
                                    <span class="pend-progress-hint">
                                        Lengkapi formulir & dokumen untuk submit
                                    </span>
                                </div>
                                <div class="pend-progress-track">
                                    <div class="pend-progress-bar-fill pend-progress-formulir" style="width: 0%"
                                        data-pendaftaran-id="{{ $daftar->id }}" title="Formulir"></div>
                                </div>
                                <div class="pend-progress-labels-row">
                                    <small>Formulir & Dokumen</small>
                                    <small>
                                        <i class="bi bi-arrow-right-circle me-1"></i>
                                        Lihat detail untuk progress penuh
                                    </small>
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
                                <i class="bi bi-pencil-square me-1"></i>
                                Lanjutkan Pengisian
                            </a>
                        @else
                            <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                                class="btn btn-outline-primary btn-sm fw-semibold pend-btn-main">
                                <i class="bi bi-eye me-1"></i>
                                Lihat Detail
                            </a>
                        @endif

                        {{-- Tombol Bayar (jika submit) --}}
                        @if($isSubmit)
                            <a href="/ppdb/pembayaran/{{ $daftar->id }}" class="btn btn-warning btn-sm fw-semibold">
                                <i class="bi bi-credit-card me-1"></i>
                                Bayar
                            </a>
                        @endif

                        {{-- Badge untuk status selesai --}}
                        @if(in_array($daftar->status, ['lulus', 'siswa_tetap']))
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>
                                Selesai
                            </span>
                        @endif
                    </div>

                </article>
            @endforeach
        </div>

        {{-- CTA Bottom --}}
        <div class="text-center mt-5">
            <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-outline-success px-5 py-2 fw-semibold"
                style="border-radius: var(--radius-md); border-width: 2px;">
                <i class="bi bi-plus-circle me-2"></i>
                Daftar di Jalur Lain
            </a>
        </div>

    @endif

@endsection

@push('scripts')
    <script>
        // Tidak ada JS kompleks di halaman ini
        // Progress penuh ada di halaman show
    </script>
@endpush