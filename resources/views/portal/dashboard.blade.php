@extends('layouts.portal')

@section('title', 'Dashboard Peserta')

@push('styles')
    @include('portal.partials._dashboard_style')
@endpush

@section('content')

    {{-- GREETING SECTION
    Welcome card dengan nama user dan quick status --}}
    <div class="greeting-card mb-dash">
        {{-- Left: Avatar + Info --}}
        <div class="greeting-left">
            <div class="greeting-avatar" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div class="greeting-info">
                <span class="greeting-time" id="greeting-time">
                    Selamat Datang
                </span>
                <h1 class="greeting-name">
                    {{ auth()->user()->name }}
                </h1>

                {{-- Status badge berdasarkan status pendaftaran --}}
                @if(auth()->user()->status === 'active')
                    <span class="status-badge status-active">
                        <i class="bi bi-check-circle-fill"></i>
                        Akun Aktif
                    </span>
                @elseif(auth()->user()->status === 'pending')
                    <span class="status-badge status-warning">
                        <i class="bi bi-clock-fill"></i>
                        Menunggu Verifikasi
                    </span>
                @else
                    <span class="status-badge status-info">
                        <i class="bi bi-info-circle-fill"></i>
                        {{ ucfirst(auth()->user()->status ?? 'Terdaftar') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Right: Quick Action --}}
        <div class="greeting-right">
            <a href="{{ route('ppdb.profil.index') }}" class="btn btn-outline-light">
                <i class="bi bi-person-circle me-2"></i>
                Lihat Profil
            </a>
        </div>
    </div>

    {{-- PROFILE COMPLETION ALERT
    Muncul jika profil belum lengkap 100% --}}
    @if(isset($progress_profil) && $progress_profil['persen'] < 100)
        <div class="profile-alert mb-dash" role="alert">
            <div class="profile-alert-icon" aria-hidden="true">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>

            <div class="profile-alert-body">
                <div class="profile-alert-title">
                    Profil Belum Lengkap
                    <span class="ms-2 fw-normal text-muted" style="font-size:.78rem">
                        ({{ $progress_profil['lengkap'] }}/{{ $progress_profil['total'] }} aspek terisi)
                    </span>
                    <span class="ms-2 fw-normal text-muted" style="font-size:.78rem"> Kelengkapan
                        {{ $progress_profil['persen'] }}%
                    </span>
                </div>
                <p class="profile-alert-desc">
                    Lengkapi profil Anda untuk melanjutkan pendaftaran PPDB.
                    Data yang lengkap mempercepat proses verifikasi.
                </p>

                {{-- Progress bar --}}
                <div class="profile-progress-track">
                    <div class="profile-progress-fill" id="profile-progress-bar"
                        style="width: {{ $progress_profil['persen'] }}%" role="progressbar"
                        aria-valuenow="{{ $progress_profil['persen'] }}" aria-valuemin="0" aria-valuemax="100"
                        aria-label="Kelengkapan profil {{ $progress_profil['persen'] }}%">
                    </div>
                </div>

                {{-- Aspek kelengkapan dengan visual dots --}}
                <div class="profile-aspek-dots">
                    @foreach($progress_profil['aspek'] ?? [] as $aspek => $lengkap)
                        <div class="aspek-dot {{ $lengkap ? 'complete' : 'incomplete' }}" data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="{{ ucfirst(str_replace('_', ' ', $aspek)) }}: {{ $lengkap ? 'Lengkap' : 'Belum Lengkap' }}">
                            @if($lengkap)
                                <i class="bi bi-check-lg"></i>
                            @else
                                <i class="bi bi-dash"></i>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN CONTENT GRID
    Two column layout: Info Cards (left) + Pendaftaran (right) --}}
    <div class="row g-4">

        {{-- ══════════════════════════════════
        KOLOM KIRI — Info Cards & Quick Actions
        ══════════════════════════════════ --}}
        <div class="col-lg-4">

            {{-- Info Cards Grid --}}
            <div class="row g-3 mb-4">

                {{-- Card 1: Info Akun --}}
                <div class="col-12">
                    <div class="info-card-dash">
                        <div class="info-card-dash-header">
                            <div class="info-card-dash-icon bg-primary-light">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-card-dash-title">Akun Anda</div>
                            </div>
                        </div>

                        <div class="info-card-dash-value">
                            {{ auth()->user()->email }}
                        </div>

                        <div class="info-card-dash-desc">
                            Terdaftar sejak <strong>{{ auth()->user()->created_at?->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Status PPDB --}}
                <div class="col-12">
                    <div class="info-card-dash">
                        <div class="info-card-dash-header">
                            <div class="info-card-dash-icon bg-info-light">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-card-dash-title">Status PPDB</div>
                            </div>
                        </div>

                        @if($pembukaan)
                            <div class="info-card-dash-value text-success">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Dibuka
                            </div>
                            <div class="info-card-dash-desc">
                                <strong>{{ $pembukaan->nama }}</strong><br>
                                {{ $pembukaan->mulai?->format('d M') }} - {{ $pembukaan->selesai?->format('d M Y') }}
                            </div>
                        @else
                            <div class="info-card-dash-value text-muted">
                                <i class="bi bi-x-circle me-2"></i>
                                Belum Dibuka
                            </div>
                            <div class="info-card-dash-desc">
                                Pantau terus untuk info pembukaan
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card 3: Countdown --}}
                @if($pembukaan && $pembukaan->selesai)
                    <div class="col-12">
                        <div class="info-card-dash">
                            <div class="info-card-dash-header">
                                <div class="info-card-dash-icon bg-warning-light">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="info-card-dash-title">Waktu Tersisa</div>
                                </div>
                            </div>

                            <div class="info-card-dash-desc mb-0">
                                Pendaftaran ditutup pada:
                                <strong>{{ $pembukaan->selesai->format('d F Y') }}</strong>
                            </div>

                            {{-- Live Countdown --}}
                            <div class="countdown-timer" id="countdown-timer"
                                data-deadline="{{ $pembukaan->selesai->format('Y-m-d H:i:s') }}">
                                <div class="countdown-unit">
                                    <span class="countdown-value" id="cd-days">00</span>
                                    <span class="countdown-label">Hari</span>
                                </div>
                                <div class="countdown-unit">
                                    <span class="countdown-value" id="cd-hours">00</span>
                                    <span class="countdown-label">Jam</span>
                                </div>
                                <div class="countdown-unit">
                                    <span class="countdown-value" id="cd-mins">00</span>
                                    <span class="countdown-label">Menit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Quick Actions --}}
            <div class="portal-card">
                <div class="card-header-portal">
                    <span>
                        <i class="bi bi-lightning-charge-fill me-2"></i>
                        Aksi Cepat
                    </span>
                </div>
                <div class="card-body p-3">
                    <nav class="quick-actions" aria-label="Quick actions">
                        <a href="{{ route('ppdb.pendaftaran.index') }}" class="quick-action-btn btn btn-outline-success">
                            <i class="bi bi-file-earmark-plus"></i>
                            <span>Pendaftaran Baru</span>
                        </a>
                        <a href="{{ route('ppdb.profil.index') }}" class="quick-action-btn btn btn-outline-secondary">
                            <i class="bi bi-person-circle"></i>
                            <span>Profil Saya</span>
                        </a>
                        <a href="{{ route('ppdb.beranda') }}" class="quick-action-btn btn btn-outline-info">
                            <i class="bi bi-house-door"></i>
                            <span>Beranda PPDB</span>
                        </a>
                    </nav>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════
        KOLOM KANAN — Pendaftaran Saya
        ══════════════════════════════════ --}}
        <div class="col-lg-8">
            <div class="portal-card">
                <div class="card-header-portal">
                    <span>
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Pendaftaran Saya
                    </span>
                    @if($pendaftaran_list->isNotEmpty())
                        <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i>
                            Daftar Baru
                        </a>
                    @endif
                </div>

                <div class="card-body p-4">

                    @if($pendaftaran_list->isEmpty())
                        {{-- Empty State - No Registration Yet --}}
                        <div class="empty-state">
                            <div class="empty-state-icon" aria-hidden="true">
                                {{-- SVG Illustration --}}
                                <svg width="140" height="120" viewBox="0 0 140 120" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <!-- Document -->
                                    <rect x="30" y="20" width="80" height="80" rx="8" fill="#f0fdf4" stroke="#bbf7d0"
                                        stroke-width="2.5" />
                                    <!-- Lines -->
                                    <rect x="44" y="35" width="52" height="6" rx="3" fill="#bbf7d0" />
                                    <rect x="44" y="48" width="40" height="5" rx="2.5" fill="#d1fae5" />
                                    <rect x="44" y="59" width="45" height="5" rx="2.5" fill="#d1fae5" />
                                    <rect x="44" y="70" width="30" height="5" rx="2.5" fill="#d1fae5" />
                                    <!-- Plus circle -->
                                    <circle cx="100" cy="82" r="22" fill="#16a34a" />
                                    <line x1="100" y1="72" x2="100" y2="92" stroke="#fff" stroke-width="3"
                                        stroke-linecap="round" />
                                    <line x1="90" y1="82" x2="110" y2="82" stroke="#fff" stroke-width="3"
                                        stroke-linecap="round" />
                                </svg>
                            </div>

                            <h2 class="empty-state-title">Belum Ada Pendaftaran</h2>

                            <p class="empty-state-desc">
                                Anda belum memiliki pendaftaran PPDB.
                                @if($pembukaan)
                                    Mulai daftar sekarang untuk tahun ajaran
                                    <strong>{{ $pembukaan->tahunPelajaran?->nama ?? 'TA 2026/2027' }}</strong>.
                                @else
                                    Pantau terus untuk info pembukaan pendaftaran.
                                @endif
                            </p>

                            @if($pembukaan)
                                <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-success btn-lg fw-semibold px-4">
                                    <i class="bi bi-file-earmark-plus me-2"></i>
                                    Mulai Pendaftaran
                                </a>
                            @endif
                        </div>

                    @else
                        {{-- List Pendaftaran --}}
                        <div class="pendaftaran-list">
                            @foreach($pendaftaran_list as $daftar)
                                @php
                                    // Map status ke configuration
                                    $statusMap = [
                                        'draft' => [
                                            'label' => 'Draft',
                                            'color' => 'secondary',
                                            'icon' => 'bi-file-earmark'
                                        ],
                                        'submitted' => [
                                            'label' => 'Dikirim',
                                            'color' => 'primary',
                                            'icon' => 'bi-send-fill'
                                        ],
                                        'verifikasi' => [
                                            'label' => 'Diverifikasi',
                                            'color' => 'info',
                                            'icon' => 'bi-search'
                                        ],
                                        'lulus' => [
                                            'label' => 'Lulus',
                                            'color' => 'success',
                                            'icon' => 'bi-trophy-fill'
                                        ],
                                        'tidak_lulus' => [
                                            'label' => 'Tidak Lulus',
                                            'color' => 'danger',
                                            'icon' => 'bi-x-circle-fill'
                                        ],
                                        'daftar_ulang' => [
                                            'label' => 'Daftar Ulang',
                                            'color' => 'warning',
                                            'icon' => 'bi-arrow-repeat'
                                        ],
                                        'siswa_tetap' => [
                                            'label' => 'Siswa Tetap',
                                            'color' => 'success',
                                            'icon' => 'bi-mortarboard-fill'
                                        ],
                                    ];

                                    $statusConfig = $statusMap[$daftar->status] ?? [
                                        'label' => ucfirst($daftar->status),
                                        'color' => 'secondary',
                                        'icon' => 'bi-circle'
                                    ];
                                @endphp

                                <article class="pendaftaran-card">

                                    {{-- Left: Nomor + Metadata --}}
                                    <div class="pendaftaran-card-left">
                                        <div class="pendaftaran-no">
                                            <span class="no-label">No. Pendaftaran</span>
                                            <span class="no-value">
                                                {{ $daftar->no_pendaftaran ?? '—' }}
                                            </span>
                                        </div>

                                        <div class="pendaftaran-meta">
                                            <span class="meta-jalur">
                                                <i class="bi bi-diagram-3-fill"></i>
                                                {{ $daftar->jalurPendaftaran?->nama ?? 'Jalur tidak tersedia' }}
                                            </span>
                                            <span class="meta-tgl">
                                                <i class="bi bi-calendar-event"></i>
                                                {{ $daftar->created_at?->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Right: Status + Action --}}
                                    <div class="pendaftaran-card-right">
                                        <span class="badge bg-{{ $statusConfig['color'] }}">
                                            <i class="bi {{ $statusConfig['icon'] }}"></i>
                                            {{ $statusConfig['label'] }}
                                        </span>

                                        <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                                            class="btn btn-sm btn-outline-success">
                                            @if(in_array($daftar->status, ['draft', 'submitted']))
                                                <i class="bi bi-pencil-square me-1"></i>
                                                Lanjutkan
                                            @else
                                                <i class="bi bi-eye me-1"></i>
                                                Detail
                                            @endif
                                        </a>
                                    </div>

                                </article>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>

@endsection

{{-- SCRIPTS
Dynamic greeting, countdown timer, dan progress bar animation --}}
@push('scripts')
    <script>
        (function () {
            'use strict';

            // 1. Dynamic Greeting berdasarkan waktu
            const hour = new Date().getHours();
            const greetings = {
                morning: '☀️ Selamat Pagi',
                afternoon: '🌤️ Selamat Siang',
                evening: '🌇 Selamat Sore',
                night: '🌙 Selamat Malam'
            };

            const greetingText = hour < 11 ? greetings.morning
                : hour < 15 ? greetings.afternoon
                    : hour < 18 ? greetings.evening
                        : greetings.night;

            const greetingEl = document.getElementById('greeting-time');
            if (greetingEl) {
                greetingEl.textContent = greetingText;
            }

            // 2. Countdown Timer untuk deadline PPDB
            const timerEl = document.getElementById('countdown-timer');
            if (timerEl) {
                const deadline = new Date(timerEl.dataset.deadline);
                const cdDays = document.getElementById('cd-days');
                const cdHours = document.getElementById('cd-hours');
                const cdMins = document.getElementById('cd-mins');

                function updateCountdown() {
                    const now = new Date();
                    const diff = deadline - now;

                    if (diff <= 0) {
                        cdDays.textContent = '00';
                        cdHours.textContent = '00';
                        cdMins.textContent = '00';
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                    cdDays.textContent = String(days).padStart(2, '0');
                    cdHours.textContent = String(hours).padStart(2, '0');
                    cdMins.textContent = String(minutes).padStart(2, '0');
                }

                updateCountdown();
                setInterval(updateCountdown, 60000); // Update setiap menit
            }

            // 3. Progress bar animation
            const progressBar = document.getElementById('profile-progress-bar');
            if (progressBar) {
                const targetWidth = progressBar.style.width;
                progressBar.style.width = '0';

                // Delayed animation untuk smooth effect
                setTimeout(() => {
                    progressBar.style.width = targetWidth;
                }, 300);
            }

            // 4. Initialize Bootstrap tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el, {
                trigger: 'hover focus',
                delay: { show: 200, hide: 100 }
            }));

        })();
    </script>
@endpush