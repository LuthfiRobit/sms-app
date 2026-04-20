@extends('layouts.portal')

@section('title', 'Beranda PPDB 2026/2027')

@push('styles')
    @include('portal.partials._beranda_style')
@endpush

@section('content')

    {{-- HERO SECTION First impression dengan status PPDB dan CTA buttons --}}
    <div class="hero-section">
        {{-- Decorative background shapes --}}
        <div class="hero-bg-shapes" aria-hidden="true">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <div class="container position-relative">
            <div class="row align-items-center min-vh-hero">
                {{-- Left column: Content --}}
                <div class="col-lg-7 mb-4 mb-lg-0">
                    {{-- Status Badge - Sangat prominent untuk immediate attention --}}
                    @if($pembukaan)
                        <div class="hero-badge mb-3" role="status" aria-live="polite">
                            <span class="status-dot status-buka" aria-hidden="true"></span>
                            <span>
                                <strong>Pendaftaran Dibuka</strong> ·
                                {{ $pembukaan->tahunPelajaran?->nama ?? 'TA 2026/2027' }}
                            </span>
                        </div>
                    @else
                        <div class="hero-badge mb-3" role="status">
                            <span class="status-dot status-tutup" aria-hidden="true"></span>
                            <span>Pendaftaran Belum Dibuka</span>
                        </div>
                    @endif

                    {{-- Main heading dengan gradient accent --}}
                    <h1 class="hero-title">
                        Daftar ke <span class="text-gradient">SMK Terbaik</span><br>
                        Tahun Ajaran 2026/2027
                    </h1>

                    {{-- Subtitle dengan deadline info --}}
                    <p class="hero-subtitle">
                        Portal PPDB Online memudahkan Anda mendaftar dari mana saja.
                        Proses cepat, transparan, dan terpercaya.
                        @if($jadwal_terdekat)
                            <br>
                            <strong>Pendaftaran ditutup {{ $jadwal_terdekat->selesai?->format('d F Y') }}.</strong>
                        @endif
                    </p>

                    {{-- CTA Buttons - Berbeda untuk authenticated vs guest --}}
                    <div class="hero-actions">
                        @auth
                            <a href="{{ route('ppdb.dashboard') }}" class="btn-hero-primary">
                                <i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>
                                Dashboard Saya
                            </a>
                        @else
                            <a href="{{ route('ppdb.register') }}" class="btn-hero-primary">
                                <i class="bi bi-person-plus me-2" aria-hidden="true"></i>
                                Daftar Sekarang
                            </a>
                            <a href="{{ route('ppdb.login') }}" class="btn-hero-secondary">
                                <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>
                                Sudah Daftar? Login
                            </a>
                        @endauth
                        <a href="{{ route('ppdb.info') }}" class="btn-hero-outline">
                            <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                            Informasi PPDB
                        </a>
                    </div>
                </div>

                {{-- Right column: Illustration (Desktop only) --}}
                <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                    <div class="hero-illustration" aria-hidden="true">
                        {{-- SVG illustration untuk visual interest --}}
                        <svg width="360" height="300" viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Graduation cap base shadow -->
                            <ellipse cx="180" cy="130" rx="100" ry="18" fill="#dcfce7" opacity="0.9" />
                            <!-- Cap bottom -->
                            <polygon points="180,50 280,130 180,148 80,130" fill="#16a34a" />
                            <!-- Cap top with shading -->
                            <polygon points="180,50 280,130 180,120 80,130" fill="#15803d" />
                            <!-- Tassel line -->
                            <line x1="280" y1="130" x2="280" y2="180" stroke="#16a34a" stroke-width="3" />
                            <!-- Tassel end -->
                            <circle cx="280" cy="183" r="6" fill="#f59e0b" />
                            <!-- Document left -->
                            <rect x="60" y="140" width="60" height="80" rx="6" fill="#fff" stroke="#bbf7d0"
                                stroke-width="2" />
                            <line x1="72" y1="158" x2="108" y2="158" stroke="#86efac" stroke-width="2" />
                            <line x1="72" y1="168" x2="108" y2="168" stroke="#86efac" stroke-width="2" />
                            <line x1="72" y1="178" x2="95" y2="178" stroke="#86efac" stroke-width="2" />
                            <!-- Document right -->
                            <rect x="240" y="155" width="60" height="80" rx="6" fill="#fff" stroke="#bbf7d0"
                                stroke-width="2" />
                            <line x1="252" y1="173" x2="288" y2="173" stroke="#86efac" stroke-width="2" />
                            <line x1="252" y1="183" x2="288" y2="183" stroke="#86efac" stroke-width="2" />
                            <line x1="252" y1="193" x2="275" y2="193" stroke="#86efac" stroke-width="2" />
                            <!-- Success badge -->
                            <circle cx="305" cy="90" r="24" fill="#16a34a" />
                            <polyline points="295,90 302,97 315,83" stroke="#fff" stroke-width="3" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <!-- Decorative stars -->
                            <text x="48" y="115" font-size="18" fill="#f59e0b">★</text>
                            <text x="298" y="145" font-size="14" fill="#f59e0b">★</text>
                            <text x="155" y="270" font-size="12" fill="#86efac">★</text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS PPDB 3 Info Cards: Status, Periode, Jalur - Informasi paling krusial --}}
    <section class="section-status" aria-labelledby="status-heading">
        <div class="container">
            {{-- Screen reader heading --}}
            <h2 id="status-heading" class="visually-hidden">Status Penerimaan Peserta Didik Baru</h2>

            <div class="row g-4">
                {{-- Card 1: Status Pembukaan --}}
                <div class="col-md-4">
                    <div class="info-card info-card-primary h-100">
                        <div class="info-card-icon" aria-hidden="true">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="info-card-label">Status PPDB</div>
                            @if($pembukaan)
                                <div class="info-card-value text-success">
                                    <span class="status-dot status-buka" aria-hidden="true"></span>
                                    <span>Dibuka</span>
                                </div>
                                <div class="info-card-sub">{{ $pembukaan->nama }}</div>
                            @else
                                <div class="info-card-value text-muted">
                                    <span class="status-dot status-tutup" aria-hidden="true"></span>
                                    <span>Belum Dibuka</span>
                                </div>
                                <div class="info-card-sub">Pantau terus portal ini</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card 2: Periode Pendaftaran --}}
                <div class="col-md-4">
                    <div class="info-card info-card-warning h-100">
                        <div class="info-card-icon text-warning" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="info-card-label">Periode Pendaftaran</div>
                            @if($pembukaan)
                                <div class="info-card-value">
                                    {{ $pembukaan->mulai?->format('d M') }} – {{ $pembukaan->selesai?->format('d M Y') }}
                                </div>
                                @php
                                    $sisaHari = (int) now()->diffInDays($pembukaan->selesai, false);
                                @endphp
                                <div class="info-card-sub">
                                    @if($sisaHari > 0)
                                        <strong>Sisa {{ $sisaHari }} hari lagi</strong>
                                    @elseif($sisaHari == 0)
                                        <strong class="text-danger">Hari terakhir!</strong>
                                    @else
                                        Sudah ditutup
                                    @endif
                                </div>
                            @else
                                <div class="info-card-value text-muted">—</div>
                                <div class="info-card-sub">Belum ada jadwal</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card 3: Jalur Tersedia --}}
                <div class="col-md-4">
                    <div class="info-card info-card-info h-100">
                        <div class="info-card-icon text-info" aria-hidden="true">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="info-card-label">Jalur Tersedia</div>
                            <div class="info-card-value">
                                {{ $pembukaan?->jalurPendaftaran->count() ?? 0 }} Jalur
                            </div>
                            <div class="info-card-sub">
                                @if($pembukaan && $pembukaan->jalurPendaftaran->isNotEmpty())
                                    {{ $pembukaan->jalurPendaftaran->pluck('nama')->implode(', ') }}
                                @else
                                    Belum ada jalur tersedia
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- JALUR PENDAFTARAN Cards untuk setiap jalur yang tersedia --}}
    @if($pembukaan && $pembukaan->jalurPendaftaran->isNotEmpty())
        <section class="py-5 bg-white" aria-labelledby="jalur-heading">
            <div class="container">
                {{-- Section Header --}}
                <div class="section-header text-center mb-5">
                    <span class="section-badge">Jalur Penerimaan</span>
                    <h2 id="jalur-heading" class="section-title">Pilih Jalur Pendaftaran</h2>
                    <p class="section-subtitle">
                        Pilih jalur yang sesuai dengan prestasi dan kondisi Anda
                    </p>
                </div>

                {{-- Jalur Cards Grid --}}
                <div class="row g-4 justify-content-center">
                    @foreach($pembukaan->jalurPendaftaran as $jalur)
                        <div class="col-md-6 col-lg-4">
                            <article class="jalur-card">
                                {{-- Card Header --}}
                                <div class="jalur-card-header">
                                    <div class="jalur-kode">{{ $jalur->kode_jalur ?? '--' }}</div>
                                    <span class="badge bg-light-success">
                                        <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>
                                        Aktif
                                    </span>
                                </div>

                                {{-- Card Content --}}
                                <h3 class="jalur-title">{{ $jalur->nama }}</h3>

                                @if($jalur->deskripsi)
                                    <p class="jalur-desc">{{ Str::limit($jalur->deskripsi, 120) }}</p>
                                @endif

                                {{-- Metadata --}}
                                <div class="jalur-meta">
                                    @if($jalur->kuota)
                                        <div class="jalur-meta-item">
                                            <i class="bi bi-people-fill" aria-hidden="true"></i>
                                            <span><strong>Kuota:</strong> {{ $jalur->kuota }} peserta</span>
                                        </div>
                                    @endif

                                    @if($jalur->tanggal_mulai && $jalur->tanggal_selesai)
                                        <div class="jalur-meta-item">
                                            <i class="bi bi-calendar-range" aria-hidden="true"></i>
                                            <span>
                                                {{ $jalur->tanggal_mulai->format('d M') }} -
                                                {{ $jalur->tanggal_selesai->format('d M Y') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($jalur->biaya_pendaftaran)
                                        <div class="jalur-meta-item">
                                            <i class="bi bi-cash-coin" aria-hidden="true"></i>
                                            <span>
                                                <strong>Biaya:</strong>
                                                Rp {{ number_format($jalur->biaya_pendaftaran, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- LANGKAH PENDAFTARAN Step-by-step guide untuk membantu user memahami proses--}}
    <section class="section-steps" aria-labelledby="steps-heading">
        <div class="container">
            {{-- Section Header --}}
            <div class="section-header text-center mb-5">
                <span class="section-badge">Panduan</span>
                <h2 id="steps-heading" class="section-title">Langkah Pendaftaran</h2>
                <p class="section-subtitle">
                    Ikuti 4 langkah mudah untuk menyelesaikan pendaftaran Anda
                </p>
            </div>

            {{-- Steps Grid --}}
            <div class="row g-4">
                {{-- Step 1 --}}
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num" aria-hidden="true">1</div>
                        <div class="step-icon bg-light-primary">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h3 class="step-title">Buat Akun</h3>
                        <p class="step-desc">
                            Daftar dengan email dan password. Verifikasi email untuk aktivasi akun.
                        </p>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num" aria-hidden="true">2</div>
                        <div class="step-icon bg-light-success">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h3 class="step-title">Isi Formulir</h3>
                        <p class="step-desc">
                            Lengkapi data diri, data orang tua, dan pilih jalur pendaftaran.
                        </p>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num" aria-hidden="true">3</div>
                        <div class="step-icon bg-light-warning">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                        </div>
                        <h3 class="step-title">Upload Dokumen</h3>
                        <p class="step-desc">
                            Unggah foto, ijazah, rapor, dan dokumen persyaratan lainnya.
                        </p>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num" aria-hidden="true">4</div>
                        <div class="step-icon bg-light-info">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h3 class="step-title">Verifikasi</h3>
                        <p class="step-desc">
                            Tunggu verifikasi dari panitia. Pantau status di dashboard.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- TIMELINE AGENDA Menampilkan jadwal kegiatan PPDB dalam format timeline--}}
    @if($agenda && $agenda->isNotEmpty())
        @php
            $agendaByJalur = $agenda->groupBy('jalur_pendaftaran_id');
        @endphp
        <section class="py-5 bg-white" aria-labelledby="timeline-heading">
            <div class="container">
                <div class="row">
                    {{-- Left column: Header & Navigation --}}
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <div class="sticky-top" style="top: 100px;">
                            <span class="section-badge">Agenda</span>
                            <h2 id="timeline-heading" class="section-title">Timeline Kegiatan</h2>
                            <p class="section-subtitle text-start mb-4">
                                Pilih jalur pendaftaran untuk melihat detail jadwal kegiatan.
                            </p>

                            {{-- Vertical Navigation Tabs --}}
                            <nav class="agenda-nav" role="tablist">
                                @foreach($agendaByJalur as $jalurId => $items)
                                    @php
                                        $jalur = $items->first()->jalurPendaftaran;
                                        $active = $loop->first ? 'active' : '';
                                    @endphp
                                    <button class="agenda-nav-item {{ $active }}" data-target="pane-jalur-{{ $jalurId }}" role="tab"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <i class="bi bi-tag-fill nav-icon"></i>
                                        <span>{{ $jalur->nama }}</span>
                                    </button>
                                @endforeach
                            </nav>
                        </div>
                    </div>

                    {{-- Right column: Timeline Panes --}}
                    <div class="col-lg-8">
                        @foreach($agendaByJalur as $jalurId => $items)
                            <div class="agenda-pane {{ $loop->first ? 'active' : '' }}" id="pane-jalur-{{ $jalurId }}"
                                role="tabpanel">
                                <div class="timeline">
                                    @foreach($items as $item)
                                        @php
                                            $now = now();
                                            $mulai = $item->mulai;
                                            $selesai = $item->selesai;

                                            // Determine status
                                            $isAktif = $mulai && $selesai && $now->between($mulai, $selesai);
                                            $isSelesai = $selesai && $now->isAfter($selesai);
                                            $statusClass = $isAktif ? 'timeline-aktif' : ($isSelesai ? 'timeline-selesai' : '');
                                        @endphp

                                        <article class="timeline-item {{ $statusClass }}">
                                            {{-- Timeline dot/icon --}}
                                            <div class="timeline-dot" aria-hidden="true">
                                                @if($isAktif)
                                                    <i class="bi bi-circle-fill"></i>
                                                @elseif($isSelesai)
                                                    <i class="bi bi-check-lg"></i>
                                                @else
                                                    <i class="bi bi-circle"></i>
                                                @endif
                                            </div>

                                            {{-- Timeline content --}}
                                            <div class="timeline-content">
                                                {{-- Date range --}}
                                                <div class="timeline-date">
                                                    @if($mulai && $selesai)
                                                        {{ $mulai->format('d M') }} - {{ $selesai->format('d M Y') }}
                                                    @elseif($mulai)
                                                        {{ $mulai->format('d M Y') }}
                                                    @else
                                                        Tanggal belum ditentukan
                                                    @endif
                                                </div>

                                                {{-- Event title --}}
                                                <h3 class="timeline-title">{{ $item->nama }}</h3>

                                                {{-- Description --}}
                                                @if($item->keterangan)
                                                    <p class="timeline-keterangan">{{ $item->keterangan }}</p>
                                                @endif

                                                {{-- Active indicator --}}
                                                @if($isAktif)
                                                    <div class="mt-2">
                                                        <span class="badge" style="background: var(--color-primary); color: white;">
                                                            <i class="bi bi-clock-fill me-1"></i>
                                                            Sedang Berlangsung
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @push('scripts')
        {{-- Agenda Tab Switcher --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const navItems = document.querySelectorAll('.agenda-nav-item');
                const panes = document.querySelectorAll('.agenda-pane');

                navItems.forEach(item => {
                    item.addEventListener('click', function () {
                        const targetId = this.getAttribute('data-target');

                        // Update buttons
                        navItems.forEach(btn => {
                            btn.classList.remove('active');
                            btn.setAttribute('aria-selected', 'false');
                        });
                        this.classList.add('active');
                        this.setAttribute('aria-selected', 'true');

                        // Update panes
                        panes.forEach(pane => {
                            pane.classList.remove('active');
                        });
                        const targetPane = document.getElementById(targetId);
                        if (targetPane) {
                            targetPane.classList.add('active');
                        }
                    });
                });
            });
        </script>
    @endpush

    {{-- CTA BOTTOM Final call-to-action untuk encourage user mendaftar--}}
    <section class="cta-section" aria-labelledby="cta-heading">
        <div class="container">
            <div class="cta-card">
                <h2 id="cta-heading" class="cta-title">
                    Siap Bergabung dengan Kami?
                </h2>
                <p class="cta-subtitle">
                    Jangan sampai kehabisan kuota! Daftar sekarang dan raih kesempatan untuk
                    menjadi bagian dari SMK terbaik dengan fasilitas lengkap dan pembelajaran berkualitas.
                </p>
                <div class="cta-actions">
                    @guest
                        <a href="{{ route('ppdb.register') }}" class="btn-hero-primary">
                            <i class="bi bi-person-plus me-2" aria-hidden="true"></i>
                            Daftar Sekarang
                        </a>
                        <a href="{{ route('ppdb.login') }}" class="btn-hero-secondary">
                            <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>
                            Login
                        </a>
                    @else
                        <a href="{{ route('ppdb.dashboard') }}" class="btn-hero-primary">
                            <i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>
                            Ke Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

@endsection