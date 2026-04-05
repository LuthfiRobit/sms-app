@extends('layouts.portal')

@section('title', 'Dashboard Peserta')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     GREETING ROW
═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="greeting-card">
            <div class="greeting-left">
                <div class="greeting-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="greeting-time" id="greeting-time"></div>
                    <h2 class="greeting-name">{{ $user->name }}</h2>
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                        <span class="status-badge status-active">
                            <i class="bi bi-check-circle me-1"></i>Akun Aktif
                        </span>
                        @if($peserta)
                            <span class="status-badge status-info">
                                <i class="bi bi-person-badge me-1"></i>Data Peserta Tersedia
                            </span>
                        @else
                            <span class="status-badge status-warning">
                                <i class="bi bi-exclamation-circle me-1"></i>Profil Belum Dilengkapi
                            </span>
                        @endif
                        <span class="text-muted" style="font-size:0.8rem">
                            <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="greeting-right d-none d-md-block">
                <a href="{{ route('ppdb.beranda') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-house me-1"></i>Beranda PPDB
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ALERT KELENGKAPAN PROFIL (muncul jika < 100%)
═══════════════════════════════════════════════════════════════════ --}}
@if($progress_profil['persen'] < 100)
<div class="row mb-4">
    <div class="col-12">
        <div class="profile-alert">
            <div class="profile-alert-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="profile-alert-body">
                <div class="profile-alert-title">
                    Profil Anda {{ $progress_profil['persen'] }}% Lengkap
                    <span class="ms-2 fw-normal text-muted" style="font-size:0.8rem">
                        ({{ $progress_profil['lengkap'] }}/{{ $progress_profil['total'] }} aspek terisi)
                    </span>
                </div>
                <p class="profile-alert-desc">
                    Lengkapi data profil Anda untuk dapat melanjutkan proses pendaftaran PPDB.
                    Data yang belum lengkap:
                    <strong>{{ implode(', ', array_keys(array_filter($progress_profil['aspek'], fn($v) => !$v))) }}</strong>.
                </p>
                <div class="profile-progress-bar-wrapper">
                    <div class="profile-progress-bar"
                         style="width: {{ $progress_profil['persen'] }}%"
                         id="profile-progress-bar">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <div class="profile-aspek-dots">
                        @foreach($progress_profil['aspek'] as $aspek => $done)
                            <div class="aspek-dot {{ $done ? 'done' : '' }}" title="{{ ucfirst($aspek) }}">
                                @if($done) <i class="bi bi-check"></i> @endif
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('ppdb.profil.index') }}" class="btn btn-success btn-sm fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i>Lengkapi Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     ROW UTAMA: Info PPDB + Pendaftaran Saya
═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4">

    {{-- ────── Kolom Kiri: Info PPDB Aktif + Quick Actions ────── --}}
    <div class="col-lg-4">

        {{-- Card Info PPDB Aktif --}}
        @if($pembukaan)
        <div class="portal-card mb-4">
            <div class="card-header-portal">
                <span><i class="bi bi-info-circle me-2"></i>Info PPDB Aktif</span>
                <span class="badge bg-success">Buka</span>
            </div>
            <div class="card-body p-4">
                <div class="ppdb-info-grid">
                    <div class="ppdb-info-item">
                        <div class="ppdb-info-label">Nama Pembukaan</div>
                        <div class="ppdb-info-val fw-semibold">{{ $pembukaan->nama }}</div>
                    </div>
                    <div class="ppdb-info-item">
                        <div class="ppdb-info-label">Tahun Pelajaran</div>
                        <div class="ppdb-info-val">{{ $pembukaan->tahunPelajaran?->nama ?? '—' }}</div>
                    </div>
                    <div class="ppdb-info-item">
                        <div class="ppdb-info-label">Periode</div>
                        <div class="ppdb-info-val">
                            {{ $pembukaan->mulai?->format('d M') }} – {{ $pembukaan->selesai?->format('d M Y') }}
                        </div>
                    </div>
                    <div class="ppdb-info-item">
                        <div class="ppdb-info-label">Jalur Tersedia</div>
                        <div class="ppdb-info-val">{{ $pembukaan->jalurPendaftaran->count() }} jalur</div>
                    </div>
                </div>

                {{-- Countdown deadline --}}
                @if($pembukaan->selesai?->isFuture())
                <div class="countdown-box mt-3">
                    <div class="countdown-label">Tutup dalam:</div>
                    <div class="countdown-timer" id="countdown-timer"
                         data-deadline="{{ $pembukaan->selesai?->toIso8601String() }}">
                        <span id="cd-days">--</span><small>hari</small>
                        <span id="cd-hours">--</span><small>jam</small>
                        <span id="cd-mins">--</span><small>mnt</small>
                    </div>
                </div>
                @endif

                <div class="mt-3">
                    @foreach($pembukaan->jalurPendaftaran as $jalur)
                    <div class="jalur-mini-item">
                        <span class="jalur-mini-kode">{{ $jalur->kode_jalur }}</span>
                        <span class="flex-grow-1">{{ $jalur->nama }}</span>
                        <span class="text-muted" style="font-size:0.75rem">{{ $jalur->kuota }} siswa</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="portal-card mb-4">
            <div class="card-header-portal">
                <span><i class="bi bi-info-circle me-2"></i>Info PPDB</span>
            </div>
            <div class="card-body p-4 text-center">
                <div class="text-muted" style="font-size:2rem">📋</div>
                <p class="text-muted mt-2 mb-0">Belum ada pembukaan PPDB yang aktif saat ini.</p>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="portal-card">
            <div class="card-header-portal">
                <span><i class="bi bi-lightning me-2"></i>Akses Cepat</span>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-2">
                    <a href="{{ route('ppdb.pendaftaran.index') }}"
                       class="btn btn-outline-success d-flex align-items-center gap-2 justify-content-start">
                        <i class="bi bi-file-earmark-plus fs-5"></i>
                        <span>Daftar Sekarang</span>
                    </a>
                    <a href="{{ route('ppdb.pengumuman.index') }}"
                       class="btn btn-outline-primary d-flex align-items-center gap-2 justify-content-start">
                        <i class="bi bi-megaphone fs-5"></i>
                        <span>Lihat Pengumuman</span>
                    </a>
                    <a href="{{ route('ppdb.profil.index') }}"
                       class="btn btn-outline-secondary d-flex align-items-center gap-2 justify-content-start">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span>Profil Saya</span>
                    </a>
                    <a href="{{ route('ppdb.beranda') }}"
                       class="btn btn-outline-info d-flex align-items-center gap-2 justify-content-start">
                        <i class="bi bi-house fs-5"></i>
                        <span>Beranda PPDB</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ────── Kolom Kanan: Pendaftaran Saya ────── --}}
    <div class="col-lg-8">
        <div class="portal-card h-100">
            <div class="card-header-portal">
                <span><i class="bi bi-file-earmark-text me-2"></i>Pendaftaran Saya</span>
                @if($pendaftaran_list->isNotEmpty())
                    <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus me-1"></i>Daftar Baru
                    </a>
                @endif
            </div>
            <div class="card-body p-4">

                @if($pendaftaran_list->isEmpty())
                    {{-- Empty State --}}
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg width="120" height="100" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                                Mulai daftar sekarang untuk tahun ajaran {{ $pembukaan->tahunPelajaran?->nama }}.
                            @else
                                Pantau terus untuk info pembukaan pendaftaran.
                            @endif
                        </p>
                        @if($pembukaan)
                        <a href="{{ route('ppdb.pendaftaran.pilih') }}" class="btn btn-success fw-semibold px-4">
                            <i class="bi bi-file-earmark-plus me-2"></i>Mulai Pendaftaran
                        </a>
                        @endif
                    </div>

                @else
                    {{-- List Pendaftaran --}}
                    <div class="pendaftaran-list">
                        @foreach($pendaftaran_list as $daftar)
                        @php
                            $statusMap = [
                                'draft'        => ['label' => 'Draft',        'color' => 'secondary', 'icon' => 'bi-file'],
                                'submitted'    => ['label' => 'Dikirim',      'color' => 'primary',   'icon' => 'bi-send'],
                                'verifikasi'   => ['label' => 'Diverifikasi', 'color' => 'info',      'icon' => 'bi-search'],
                                'lulus'        => ['label' => 'Lulus',        'color' => 'success',   'icon' => 'bi-trophy'],
                                'tidak_lulus'  => ['label' => 'Tidak Lulus',  'color' => 'danger',    'icon' => 'bi-x-circle'],
                                'daftar_ulang' => ['label' => 'Daftar Ulang', 'color' => 'warning',   'icon' => 'bi-arrow-repeat'],
                                'siswa_tetap'  => ['label' => 'Siswa Tetap',  'color' => 'success',   'icon' => 'bi-mortarboard'],
                            ];
                            $st = $statusMap[$daftar->status] ?? ['label' => $daftar->status, 'color' => 'secondary', 'icon' => 'bi-circle'];
                        @endphp
                        <div class="pendaftaran-card">
                            <div class="pendaftaran-card-left">
                                <div class="pendaftaran-no">
                                    <span class="no-label">No. Pendaftaran</span>
                                    <span class="no-value">{{ $daftar->no_pendaftaran ?? '—' }}</span>
                                </div>
                                <div class="pendaftaran-meta">
                                    <span class="meta-jalur">
                                        <i class="bi bi-diagram-3 me-1"></i>
                                        {{ $daftar->jalurPendaftaran?->nama ?? '—' }}
                                    </span>
                                    <span class="meta-tgl">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $daftar->created_at?->format('d M Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="pendaftaran-card-right">
                                <span class="badge bg-{{ $st['color'] }} text-white mb-2">
                                    <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] }}
                                </span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                                       class="btn btn-sm btn-outline-success">
                                        @if(in_array($daftar->status, ['draft', 'submitted']))
                                            <i class="bi bi-pencil me-1"></i>Lanjutkan
                                        @else
                                            <i class="bi bi-eye me-1"></i>Detail
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
/* ── Greeting Card ───────────────────────────────────────────── */
.greeting-card {
    background: linear-gradient(135deg, #15803d 0%, #059669 100%);
    border-radius: 16px;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 6px 24px rgba(22,163,74,0.2);
}
.greeting-left    { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.greeting-avatar  {
    width: 56px; height: 56px;
    background: rgba(255,255,255,0.22);
    border: 2px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.greeting-time    { font-size: 0.75rem; color: rgba(255,255,255,0.75); text-transform: uppercase; letter-spacing: 0.5px; }
.greeting-name    { font-size: 1.25rem; font-weight: 800; color: #fff; margin: 0; }
.greeting-right .btn { border-color: rgba(255,255,255,0.5); color: #fff; }
.greeting-right .btn:hover { background: rgba(255,255,255,0.15); color: #fff; }

/* Status Badges */
.status-badge {
    display: inline-flex; align-items: center;
    font-size: 0.75rem; font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
}
.status-active  { background: rgba(255,255,255,0.2); color: #fff; }
.status-info    { background: rgba(255,255,255,0.15); color: #d1fae5; }
.status-warning { background: rgba(251,191,36,0.25); color: #fef3c7; }

/* ── Profile Alert ─────────────────────────────────────────────── */
.profile-alert {
    background: #fffbeb;
    border: 1.5px solid #fcd34d;
    border-left: 5px solid #f59e0b;
    border-radius: 14px;
    padding: 20px 22px;
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
.profile-alert-body { flex: 1; }
.profile-alert-title { font-weight: 700; color: #92400e; font-size: 0.9375rem; margin-bottom: 4px; }
.profile-alert-desc  { font-size: 0.8125rem; color: #78350f; margin-bottom: 10px; }
.profile-progress-bar-wrapper {
    height: 8px;
    background: #fef3c7;
    border-radius: 4px;
    overflow: hidden;
}
.profile-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
    border-radius: 4px;
    transition: width 1s ease;
}
.profile-aspek-dots {
    display: flex;
    gap: 6px;
    align-items: center;
}
.aspek-dot {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: #fef3c7;
    border: 1.5px solid #fcd34d;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem;
    color: #d97706;
}
.aspek-dot.done {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}

/* ── Portal Card ─────────────────────────────────────────────── */
.portal-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    overflow: hidden;
}
.card-header-portal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 700;
    color: #374151;
    font-size: 0.875rem;
}

/* ── PPDB Info Grid ──────────────────────────────────────────── */
.ppdb-info-grid      { display: flex; flex-direction: column; gap: 10px; }
.ppdb-info-item      { display: flex; justify-content: space-between; align-items: center; }
.ppdb-info-label     { font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.4px; }
.ppdb-info-val       { font-size: 0.875rem; color: #111827; text-align: right; }

/* Countdown */
.countdown-box {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 12px 16px;
}
.countdown-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 6px; }
.countdown-timer {
    display: flex;
    gap: 12px;
    align-items: baseline;
}
.countdown-timer span {
    font-size: 1.4rem;
    font-weight: 800;
    color: #15803d;
    font-variant-numeric: tabular-nums;
}
.countdown-timer small { font-size: 0.65rem; color: #9ca3af; }

/* Jalur Mini */
.jalur-mini-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.8125rem;
}
.jalur-mini-item:last-child { border-bottom: none; }
.jalur-mini-kode {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    background: #dcfce7;
    color: #15803d;
    padding: 2px 6px;
    border-radius: 4px;
    flex-shrink: 0;
}

/* ── Empty State ─────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}
.empty-state-icon   { margin-bottom: 16px; }
.empty-state-title  { font-weight: 700; color: #111827; margin-bottom: 8px; }
.empty-state-desc   { color: #6b7280; font-size: 0.875rem; max-width: 360px; margin: 0 auto 20px; line-height: 1.6; }

/* ── Pendaftaran Cards ────────────────────────────────────────── */
.pendaftaran-list    { display: flex; flex-direction: column; gap: 12px; }
.pendaftaran-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 20px;
    transition: border-color 0.2s, background 0.2s;
}
.pendaftaran-card:hover { border-color: #86efac; background: #f0fdf4; }
.pendaftaran-card-left { flex: 1; min-width: 0; }
.pendaftaran-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}
.no-label { display: block; font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.4px; }
.no-value { font-size: 1rem; font-weight: 700; color: #111827; font-family: 'Courier New', monospace; letter-spacing: 1px; }
.meta-jalur, .meta-tgl {
    display: inline-flex;
    align-items: center;
    font-size: 0.8rem;
    color: #6b7280;
    margin-right: 12px;
    margin-top: 4px;
}

/* ── Quick Action Buttons ─────────────────────────────────────── */
.btn-outline-success:hover,
.btn-outline-primary:hover,
.btn-outline-secondary:hover,
.btn-outline-info:hover {
    transform: translateX(4px);
    transition: transform 0.2s;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    // ── Greeting berdasarkan waktu ─────────────────────────────
    const now  = new Date();
    const hour = now.getHours();
    let greet  = hour < 11 ? '☀️ Selamat Pagi' : hour < 15 ? '🌤️ Selamat Siang' : hour < 18 ? '🌇 Selamat Sore' : '🌙 Selamat Malam';
    const el   = document.getElementById('greeting-time');
    if (el) el.textContent = greet;

    // ── Countdown Timer ────────────────────────────────────────
    const timerEl = document.getElementById('countdown-timer');
    if (timerEl) {
        const deadline = new Date(timerEl.dataset.deadline);
        const cdDays  = document.getElementById('cd-days');
        const cdHours = document.getElementById('cd-hours');
        const cdMins  = document.getElementById('cd-mins');

        function updateCountdown() {
            const diff = deadline - new Date();
            if (diff <= 0) {
                cdDays.textContent = cdHours.textContent = cdMins.textContent = '00';
                return;
            }
            const days  = Math.floor(diff / 86400000);
            const hours = Math.floor((diff % 86400000) / 3600000);
            const mins  = Math.floor((diff % 3600000) / 60000);
            cdDays.textContent  = String(days).padStart(2, '0');
            cdHours.textContent = String(hours).padStart(2, '0');
            cdMins.textContent  = String(mins).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 60000);
    }

    // ── Animate profile progress bar ──────────────────────────
    const bar = document.getElementById('profile-progress-bar');
    if (bar) {
        const target = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => { bar.style.width = target; }, 300);
    }
})();
</script>
@endpush
