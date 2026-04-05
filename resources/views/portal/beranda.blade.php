@extends('layouts.portal')

@section('title', 'Beranda PPDB 2026/2027')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════════════════════════════════ --}}
<div class="hero-section">
    <div class="hero-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    <div class="container position-relative">
        <div class="row align-items-center min-vh-hero">
            <div class="col-lg-7">
                {{-- Status Badge --}}
                @if($pembukaan)
                    <div class="hero-badge mb-3">
                        <span class="status-dot status-buka"></span>
                        Pendaftaran Dibuka · {{ $pembukaan->tahunPelajaran?->nama ?? 'TA 2026/2027' }}
                    </div>
                @else
                    <div class="hero-badge mb-3">
                        <span class="status-dot status-tutup"></span>
                        Pendaftaran Belum Dibuka
                    </div>
                @endif

                <h1 class="hero-title">
                    Daftar ke <span class="text-gradient">SMK Terbaik</span><br>
                    Tahun Ajaran 2026/2027
                </h1>
                <p class="hero-subtitle">
                    Portal PPDB Online memudahkan Anda mendaftar dari mana saja.
                    Proses cepat, transparan, dan terpercaya.
                    @if($jadwal_terdekat)
                        Pendaftaran ditutup <strong>{{ $jadwal_terdekat->selesai?->format('d F Y') }}</strong>.
                    @endif
                </p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('ppdb.dashboard') }}" class="btn-hero-primary">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard Saya
                        </a>
                    @else
                        <a href="{{ route('ppdb.register') }}" class="btn-hero-primary">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="{{ route('ppdb.login') }}" class="btn-hero-secondary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sudah Daftar? Login
                        </a>
                    @endauth
                    <a href="{{ route('ppdb.info') }}" class="btn-hero-outline">
                        <i class="bi bi-info-circle me-2"></i>Informasi PPDB
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <div class="hero-illustration">
                    <svg width="360" height="300" viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Graduation cap base -->
                        <ellipse cx="180" cy="130" rx="100" ry="18" fill="#dcfce7" opacity="0.9"/>
                        <polygon points="180,50 280,130 180,148 80,130" fill="#16a34a"/>
                        <polygon points="180,50 280,130 180,120 80,130" fill="#15803d"/>
                        <!-- Tassel -->
                        <line x1="280" y1="130" x2="280" y2="180" stroke="#16a34a" stroke-width="3"/>
                        <circle cx="280" cy="183" r="6" fill="#f59e0b"/>
                        <!-- Documents floating -->
                        <rect x="60" y="140" width="60" height="80" rx="6" fill="#fff" stroke="#bbf7d0" stroke-width="2"/>
                        <line x1="72" y1="158" x2="108" y2="158" stroke="#86efac" stroke-width="2"/>
                        <line x1="72" y1="168" x2="108" y2="168" stroke="#86efac" stroke-width="2"/>
                        <line x1="72" y1="178" x2="95" y2="178" stroke="#86efac" stroke-width="2"/>
                        <rect x="240" y="155" width="60" height="80" rx="6" fill="#fff" stroke="#bbf7d0" stroke-width="2"/>
                        <line x1="252" y1="173" x2="288" y2="173" stroke="#86efac" stroke-width="2"/>
                        <line x1="252" y1="183" x2="288" y2="183" stroke="#86efac" stroke-width="2"/>
                        <line x1="252" y1="193" x2="275" y2="193" stroke="#86efac" stroke-width="2"/>
                        <!-- Checkmark badge -->
                        <circle cx="305" cy="90" r="24" fill="#16a34a"/>
                        <polyline points="295,90 302,97 315,83" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- Stars -->
                        <text x="48" y="115" font-size="18" fill="#f59e0b">★</text>
                        <text x="298" y="145" font-size="14" fill="#f59e0b">★</text>
                        <text x="155" y="270" font-size="12" fill="#86efac">★</text>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     STATUS PPDB
═══════════════════════════════════════════════════════════════════ --}}
<section class="section-status py-5">
    <div class="container">
        <div class="row g-4">
            {{-- Card Status Pembukaan --}}
            <div class="col-md-4">
                <div class="info-card info-card-primary h-100">
                    <div class="info-card-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="info-card-label">Status PPDB</div>
                        @if($pembukaan)
                            <div class="info-card-value text-success">
                                <span class="status-dot status-buka me-1"></span> Dibuka
                            </div>
                            <div class="info-card-sub">{{ $pembukaan->nama }}</div>
                        @else
                            <div class="info-card-value text-muted">
                                <span class="status-dot status-tutup me-1"></span> Belum Dibuka
                            </div>
                            <div class="info-card-sub">Pantau terus portal ini</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Periode Pendaftaran --}}
            <div class="col-md-4">
                <div class="info-card info-card-warning h-100">
                    <div class="info-card-icon text-warning">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <div class="info-card-label">Periode Pendaftaran</div>
                        @if($pembukaan)
                            <div class="info-card-value">
                                {{ $pembukaan->mulai?->format('d M') }} – {{ $pembukaan->selesai?->format('d M Y') }}
                            </div>
                            <div class="info-card-sub">Sisa {{ now()->diffInDays($pembukaan->selesai, false) }} hari lagi</div>
                        @else
                            <div class="info-card-value text-muted">—</div>
                            <div class="info-card-sub">Belum ada jadwal</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Jalur Tersedia --}}
            <div class="col-md-4">
                <div class="info-card info-card-info h-100">
                    <div class="info-card-icon text-info">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div>
                        <div class="info-card-label">Jalur Tersedia</div>
                        <div class="info-card-value">
                            {{ $pembukaan?->jalurPendaftaran->count() ?? 0 }} Jalur
                        </div>
                        <div class="info-card-sub">
                            @if($pembukaan && $pembukaan->jalurPendaftaran->isNotEmpty())
                                {{ $pembukaan->jalurPendaftaran->pluck('nama')->implode(', ') }}
                            @else
                                Belum ada jalur
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     JALUR PENDAFTARAN
═══════════════════════════════════════════════════════════════════ --}}
@if($pembukaan && $pembukaan->jalurPendaftaran->isNotEmpty())
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge">Jalur Penerimaan</span>
            <h2 class="section-title">Pilih Jalur Pendaftaran</h2>
            <p class="section-subtitle">Pilih jalur yang sesuai dengan prestasi dan kondisi Anda</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($pembukaan->jalurPendaftaran as $jalur)
            <div class="col-md-6 col-lg-4">
                <div class="jalur-card h-100">
                    <div class="jalur-card-header">
                        <div class="jalur-kode">{{ $jalur->kode_jalur ?? '--' }}</div>
                        <span class="badge bg-light-success text-success">Aktif</span>
                    </div>
                    <h4 class="jalur-title">{{ $jalur->nama }}</h4>
                    @if($jalur->deskripsi)
                        <p class="jalur-desc">{{ Str::limit($jalur->deskripsi, 100) }}</p>
                    @endif
                    <div class="jalur-meta">
                        <div class="jalur-meta-item">
                            <i class="bi bi-people text-success"></i>
                            <span>Kuota: <strong>{{ $jalur->kuota ?? '—' }} siswa</strong></span>
                        </div>
                        @if($jalur->biayaRegistrasi->isNotEmpty())
                        <div class="jalur-meta-item">
                            <i class="bi bi-cash text-success"></i>
                            <span>Biaya: <strong>Rp {{ number_format($jalur->biayaRegistrasi->first()->jumlah ?? 0, 0, ',', '.') }}</strong></span>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('ppdb.register') }}"
                       class="btn btn-success w-100 mt-3 fw-semibold"
                       style="border-radius:8px">
                        Daftar via Jalur Ini <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     CARA DAFTAR — 4 LANGKAH
═══════════════════════════════════════════════════════════════════ --}}
<section class="py-5 section-steps">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge">Mudah & Cepat</span>
            <h2 class="section-title">Cara Mendaftar</h2>
            <p class="section-subtitle">Selesaikan pendaftaran dalam 4 langkah mudah</p>
        </div>

        <div class="row g-4">
            @foreach([
                ['icon' => 'bi-person-plus', 'color' => 'success', 'num' => '01', 'title' => 'Buat Akun', 'desc' => 'Daftar dengan email aktif. OTP verifikasi dikirim ke email Anda.'],
                ['icon' => 'bi-file-earmark-text', 'color' => 'primary', 'num' => '02', 'title' => 'Isi Formulir', 'desc' => 'Lengkapi data diri sesuai standar Dapodik dan pilih jalur pendaftaran.'],
                ['icon' => 'bi-cloud-upload', 'color' => 'warning', 'num' => '03', 'title' => 'Upload Dokumen', 'desc' => 'Unggah dokumen persyaratan yang dibutuhkan sesuai jalur yang dipilih.'],
                ['icon' => 'bi-credit-card', 'color' => 'info', 'num' => '04', 'title' => 'Bayar & Submit', 'desc' => 'Selesaikan pembayaran biaya registrasi dan submit pendaftaran Anda.'],
            ] as $step)
            <div class="col-sm-6 col-lg-3">
                <div class="step-card text-center">
                    <div class="step-num">{{ $step['num'] }}</div>
                    <div class="step-icon bg-light-{{ $step['color'] }}">
                        <i class="bi {{ $step['icon'] }} text-{{ $step['color'] }}"></i>
                    </div>
                    <h5 class="step-title">{{ $step['title'] }}</h5>
                    <p class="step-desc">{{ $step['desc'] }}</p>
                </div>
                @if(!$loop->last)
                    <div class="step-arrow d-none d-lg-block">→</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     JADWAL PPDB — TIMELINE
═══════════════════════════════════════════════════════════════════ --}}
@if($semua_jadwal->isNotEmpty())
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge">Agenda</span>
            <h2 class="section-title">Jadwal Kegiatan PPDB</h2>
            <p class="section-subtitle">Pastikan Anda tidak melewatkan satu pun tahapan penting</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline">
                    @foreach($semua_jadwal as $jadwal)
                    @php
                        $isSelesai = $jadwal->selesai?->isPast();
                        $isAktif   = $jadwal->mulai?->isPast() && !$isSelesai;
                    @endphp
                    <div class="timeline-item {{ $isAktif ? 'timeline-aktif' : ($isSelesai ? 'timeline-selesai' : '') }}">
                        <div class="timeline-dot">
                            @if($isAktif)
                                <i class="bi bi-play-circle-fill"></i>
                            @elseif($isSelesai)
                                <i class="bi bi-check-circle-fill"></i>
                            @else
                                <i class="bi bi-circle"></i>
                            @endif
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-date">
                                {{ $jadwal->mulai?->format('d M Y') }}
                                @if($jadwal->selesai && $jadwal->mulai?->format('d M Y') !== $jadwal->selesai?->format('d M Y'))
                                    – {{ $jadwal->selesai?->format('d M Y') }}
                                @endif
                            </div>
                            <div class="timeline-title">{{ $jadwal->nama }}</div>
                            @if($jadwal->jalurPendaftaran)
                                <span class="timeline-jalur">{{ $jadwal->jalurPendaftaran->nama }}</span>
                            @endif
                            @if($jadwal->keterangan)
                                <p class="timeline-keterangan">{{ $jadwal->keterangan }}</p>
                            @endif
                            @if($isAktif)
                                <span class="badge bg-success text-white">Sedang Berlangsung</span>
                            @elseif($isSelesai)
                                <span class="badge bg-secondary text-white">Selesai</span>
                            @else
                                <span class="badge bg-light-primary text-primary">Akan Datang</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     CTA BOTTOM
═══════════════════════════════════════════════════════════════════ --}}
<section class="cta-section py-5">
    <div class="container text-center">
        <div class="cta-card">
            <h2 class="cta-title">Siap Memulai Pendaftaran?</h2>
            <p class="cta-subtitle">
                Bergabunglah dengan ribuan calon siswa yang telah mendaftar secara online.
                Proses mudah, cepat, dan transparan.
            </p>
            <div class="cta-actions">
                @auth
                    <a href="{{ route('ppdb.dashboard') }}" class="btn-hero-primary">
                        <i class="bi bi-speedometer2 me-2"></i>Ke Dashboard Saya
                    </a>
                @else
                    <a href="{{ route('ppdb.register') }}" class="btn-hero-primary">
                        <i class="bi bi-person-plus me-2"></i>Mulai Pendaftaran
                    </a>
                    <a href="{{ route('ppdb.login') }}" class="btn-hero-secondary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
/* ── CSS Variables ───────────────────────────────────────────── */
:root {
    --green-50:  #f0fdf4;
    --green-100: #dcfce7;
    --green-200: #bbf7d0;
    --green-400: #4ade80;
    --green-600: #16a34a;
    --green-700: #15803d;
    --green-800: #166534;
}

/* ── Hero ─────────────────────────────────────────────────────── */
.hero-section {
    background: linear-gradient(135deg, #0d4f2a 0%, #15803d 50%, #059669 100%);
    padding: 80px 0 60px;
    position: relative;
    overflow: hidden;
}
.hero-bg-shapes .shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
.shape-2 { width: 250px; height: 250px; bottom: -80px; left: -50px; }
.shape-3 { width: 150px; height: 150px; top: 50%; left: 30%; }
.min-vh-hero { min-height: 420px; }

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.25);
    letter-spacing: 0.5px;
}
.status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.status-buka  { background: #4ade80; box-shadow: 0 0 6px #4ade80; animation: pulse-dot 2s infinite; }
.status-tutup { background: #f87171; }
@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}

.hero-title {
    font-size: clamp(1.8rem, 4vw, 2.75rem);
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 16px;
}
.text-gradient {
    background: linear-gradient(90deg, #86efac, #4ade80);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-subtitle {
    font-size: 1rem;
    color: rgba(255,255,255,0.82);
    line-height: 1.7;
    max-width: 520px;
    margin-bottom: 32px;
}
.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

/* Hero Buttons */
.btn-hero-primary {
    background: #fff;
    color: #15803d;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}
.btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); color: #15803d; }

.btn-hero-secondary {
    background: rgba(255,255,255,0.15);
    color: #fff;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.35);
    backdrop-filter: blur(6px);
    transition: all 0.2s;
}
.btn-hero-secondary:hover { background: rgba(255,255,255,0.25); color: #fff; }

.btn-hero-outline {
    color: rgba(255,255,255,0.75);
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: color 0.2s;
}
.btn-hero-outline:hover { color: #fff; }

/* Hero Illustration */
.hero-illustration {
    animation: float-anim 4s ease-in-out infinite;
}
@keyframes float-anim {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-12px); }
}

/* ── Info Cards (Status Section) ──────────────────────────────── */
.section-status { background: var(--green-50); }
.info-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 22px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    border-left: 4px solid transparent;
    transition: box-shadow 0.2s, transform 0.2s;
}
.info-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
.info-card-primary { border-left-color: #16a34a; }
.info-card-warning  { border-left-color: #f59e0b; }
.info-card-info     { border-left-color: #0ea5e9; }
.info-card-icon {
    font-size: 1.75rem;
    color: #16a34a;
    line-height: 1;
    flex-shrink: 0;
    margin-top: 2px;
}
.info-card-label { font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.info-card-value { font-size: 1.1rem; font-weight: 700; color: #111827; line-height: 1.3; }
.info-card-sub   { font-size: 0.8rem; color: #6b7280; margin-top: 3px; }

/* ── Section Headers ──────────────────────────────────────────── */
.section-badge {
    display: inline-block;
    background: var(--green-100);
    color: var(--green-700);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 10px;
}
.section-title    { font-size: clamp(1.4rem, 3vw, 2rem); font-weight: 800; color: #111827; margin-bottom: 8px; }
.section-subtitle { color: #6b7280; max-width: 480px; margin: 0 auto; font-size: 0.9375rem; }

/* ── Jalur Cards ──────────────────────────────────────────────── */
.jalur-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 24px;
    transition: all 0.25s;
    position: relative;
    overflow: hidden;
}
.jalur-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #16a34a, #059669);
}
.jalur-card:hover {
    border-color: #86efac;
    box-shadow: 0 8px 30px rgba(22,163,74,0.12);
    transform: translateY(-4px);
}
.jalur-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.jalur-kode {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #9ca3af;
    background: #f3f4f6;
    padding: 3px 8px;
    border-radius: 4px;
}
.jalur-title { font-size: 1.1rem; font-weight: 700; color: #111827; margin-bottom: 8px; }
.jalur-desc  { font-size: 0.875rem; color: #6b7280; margin-bottom: 12px; line-height: 1.5; }
.jalur-meta  { display: flex; flex-direction: column; gap: 6px; }
.jalur-meta-item { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; color: #374151; }
.bg-light-success { background: #dcfce7 !important; }

/* ── Steps ──────────────────────────────────────────────────────── */
.section-steps { background: var(--green-50); }
.step-card {
    background: #fff;
    border-radius: 14px;
    padding: 28px 20px;
    position: relative;
    transition: box-shadow 0.2s, transform 0.2s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.step-card:hover { box-shadow: 0 8px 25px rgba(22,163,74,0.1); transform: translateY(-3px); }
.step-num {
    position: absolute;
    top: 16px; right: 16px;
    font-size: 2rem;
    font-weight: 900;
    color: #f0fdf4;
    line-height: 1;
}
.step-icon {
    width: 56px; height: 56px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 16px;
}
.step-title  { font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: 8px; }
.step-desc   { font-size: 0.8125rem; color: #6b7280; line-height: 1.5; margin: 0; }
.bg-light-primary { background: #dbeafe !important; }
.bg-light-warning { background: #fef9c3 !important; }
.bg-light-info    { background: #e0f2fe !important; }

/* ── Timeline ─────────────────────────────────────────────────── */
.timeline { position: relative; padding-left: 32px; }
.timeline::before {
    content: '';
    position: absolute;
    left: 8px; top: 0; bottom: 0;
    width: 2px;
    background: #e5e7eb;
}
.timeline-item {
    position: relative;
    margin-bottom: 28px;
    padding-left: 20px;
}
.timeline-dot {
    position: absolute;
    left: -28px; top: 2px;
    width: 18px; height: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    color: #d1d5db;
    background: #fff;
}
.timeline-aktif   .timeline-dot { color: #16a34a; }
.timeline-selesai .timeline-dot { color: #9ca3af; }
.timeline-content {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 18px;
    transition: border-color 0.2s;
}
.timeline-aktif .timeline-content { border-color: #86efac; background: #f0fdf4; }
.timeline-selesai .timeline-content { opacity: 0.7; }
.timeline-date  { font-size: 0.75rem; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.timeline-title { font-weight: 700; color: #111827; margin-bottom: 4px; }
.timeline-jalur { font-size: 0.75rem; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 10px; display: inline-block; margin-bottom: 6px; }
.timeline-keterangan { font-size: 0.8125rem; color: #6b7280; margin-bottom: 6px; }
.bg-light-primary { background: #eff6ff !important; }

/* ── CTA Bottom ──────────────────────────────────────────────── */
.cta-section { background: linear-gradient(135deg, #0d4f2a 0%, #15803d 100%); }
.cta-card {
    max-width: 620px;
    margin: 0 auto;
}
.cta-title    { font-size: clamp(1.4rem, 3vw, 2rem); font-weight: 800; color: #fff; margin-bottom: 12px; }
.cta-subtitle { color: rgba(255,255,255,0.8); font-size: 0.9375rem; margin-bottom: 28px; line-height: 1.6; }
.cta-actions  { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
</style>
@endpush
