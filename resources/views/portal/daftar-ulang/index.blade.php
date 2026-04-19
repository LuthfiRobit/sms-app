@extends('layouts.portal')
@section('title', 'Daftar Ulang Peserta')

@php
    $pendaftaran      = $info['pendaftaran']       ?? null;
    $peserta          = $info['peserta']            ?? null;
    $hasilSeleksi     = $info['hasil_seleksi']      ?? null;
    $jalur            = $info['jalur']              ?? null;
    $tahunPelajaran   = $info['tahun_pelajaran']    ?? null;
    $deadline         = $info['deadline']           ?? null;
    $sisaHari         = $info['sisa_hari']          ?? 0;
    $sisaJam          = $info['sisa_jam']           ?? 0;
    $sisaMenit        = $info['sisa_menit']         ?? 0;
    $sudahDaftarUlang = $info['sudah_daftar_ulang'] ?? false;
    $terlambat        = $info['terlambat']          ?? true;
    $statusKelulusan  = $info['status_kelulusan']   ?? null;
    $statusPendaftaran = $info['status_pendaftaran'] ?? null;

    $namaPeserta   = optional($peserta?->user)->name ?? 'Peserta';
    $namaJalur     = optional($jalur)->nama ?? 'Reguler';
    $namaSekolah   = config('ppdb.nama_sekolah', 'SMK Negeri');
    $noPendaftaran = optional($pendaftaran)->no_pendaftaran ?? ('#' . optional($pendaftaran)->id);
    $namaTahun     = optional($tahunPelajaran)->nama ?? '2026/2027';
@endphp

@push('styles')
<style>
    /* ============================================================
       DAFTAR ULANG — Premium CSS Design System
       ============================================================ */

    /* Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    :root {
        --du-success:   #10b981;
        --du-warning:   #f59e0b;
        --du-danger:    #ef4444;
        --du-primary:   #3b82f6;
        --du-info:      #0ea5e9;
        --du-dark:      #0f172a;
        --du-radius:    20px;
        --du-shadow:    0 8px 32px rgba(0,0,0,0.08);
        --du-shadow-lg: 0 20px 60px rgba(0,0,0,0.12);
    }

    /* ─── Base Card ─────────────────────────────────────────── */
    .du-card {
        border: none;
        border-radius: var(--du-radius);
        overflow: hidden;
        background: #fff;
        box-shadow: var(--du-shadow);
        font-family: 'Inter', sans-serif;
    }

    /* ─── Confetti ──────────────────────────────────────────── */
    .confetti-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
        z-index: 10;
    }
    .confetti-piece {
        position: absolute;
        width: 9px;
        height: 9px;
        border-radius: 2px;
        animation: confetti-rain 3s ease-out infinite;
        opacity: 0;
    }
    @keyframes confetti-rain {
        0%   { transform: translateY(-30px) rotate(0deg);   opacity: 1; }
        100% { transform: translateY(600px) rotate(720deg); opacity: 0; }
    }

    /* ─── Celebration Header ────────────────────────────────── */
    .du-celebration-header {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #047857 100%);
        padding: 3rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .du-celebration-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    /* ─── Info Tiles ────────────────────────────────────────── */
    .du-tile {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 12px;
        padding: 0.85rem 1.2rem;
        text-align: center;
        backdrop-filter: blur(8px);
        transition: transform .2s;
    }
    .du-tile:hover { transform: translateY(-2px); }
    .du-tile-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: .7; color: #fff; font-weight: 700; }
    .du-tile-value { font-size: 1.1rem; font-weight: 800; color: #fff; margin-top: 2px; }

    /* ─── Info Strip ────────────────────────────────────────── */
    .du-info-strip {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .du-info-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .du-info-item:last-child { border-bottom: none; }
    .du-info-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .du-info-label { font-size: .75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .du-info-value { font-size: .92rem; color: #0f172a; font-weight: 700; }

    /* ─── Countdown ─────────────────────────────────────────── */
    .du-countdown-wrap {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #bfdbfe;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .du-countdown-label {
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #2563eb;
        margin-bottom: .75rem;
    }
    .du-countdown-timer {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    .du-countdown-unit {
        text-align: center;
    }
    .du-countdown-number {
        font-size: 2rem;
        font-weight: 900;
        color: #1d4ed8;
        line-height: 1;
        display: block;
    }
    .du-countdown-sep {
        font-size: 2rem;
        font-weight: 900;
        color: #93c5fd;
        margin-top: -6px;
    }
    .du-countdown-unit-label {
        font-size: .65rem;
        font-weight: 700;
        color: #3b82f6;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-top: 4px;
    }

    /* ─── Konfirmasi Section ────────────────────────────────── */
    .du-confirm-section {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: #fafafa;
    }
    .du-checkbox-wrap {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 1rem;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        margin-bottom: 1.25rem;
    }
    .du-checkbox-wrap:has(input:checked) {
        border-color: var(--du-success);
        box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
    }
    .du-checkbox-wrap input[type="checkbox"] {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        flex-shrink: 0;
        margin-top: 1px;
        accent-color: var(--du-success);
        cursor: pointer;
    }
    .du-checkbox-label {
        font-size: .9rem;
        color: #374151;
        line-height: 1.5;
        user-select: none;
        cursor: pointer;
    }
    .du-btn-confirm {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: none;
        border-radius: 12px;
        padding: .875rem 2rem;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        width: 100%;
        cursor: pointer;
        transition: opacity .2s, transform .15s, box-shadow .2s;
        box-shadow: 0 4px 20px rgba(5,150,105,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .6rem;
        position: relative;
        overflow: hidden;
    }
    .du-btn-confirm:disabled {
        opacity: .4;
        cursor: not-allowed;
        box-shadow: none;
        transform: none !important;
    }
    .du-btn-confirm:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(5,150,105,0.45);
    }
    .du-btn-confirm:not(:disabled):active {
        transform: translateY(0);
    }

    /* ─── Alert Cards ───────────────────────────────────────── */
    .du-alert-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #86efac;
        border-radius: 16px;
        padding: 1.5rem;
    }
    .du-alert-warning {
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
        border: 2px solid #fde047;
        border-radius: 16px;
        padding: 1.5rem;
    }
    .du-alert-danger {
        background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%);
        border: 2px solid #fca5a5;
        border-radius: 16px;
        padding: 1.5rem;
    }
    .du-alert-error {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border: 2px solid #fdba74;
        border-radius: 16px;
        padding: 1.5rem;
    }

    /* ─── Pulse dot ─────────────────────────────────────────── */
    .du-pulse-dot {
        display: inline-block;
        width: 10px; height: 10px;
        border-radius: 50%;
        background: var(--du-success);
        animation: pulse-dot 1.5s ease-in-out infinite;
    }
    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,.5); }
        50%       { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
    }

    /* ─── Spinner in button ─────────────────────────────────── */
    .du-btn-spinner {
        width: 18px; height: 18px;
        border: 2.5px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
        display: none;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ─── Toast ─────────────────────────────────────────────── */
    .du-toast-wrap {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: .75rem;
        pointer-events: none;
    }
    .du-toast {
        background: #1e293b;
        color: #fff;
        padding: .85rem 1.25rem;
        border-radius: 14px;
        font-size: .88rem;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        transform: translateX(120%);
        transition: transform .35s cubic-bezier(.4,0,.2,1);
        pointer-events: auto;
        max-width: 360px;
    }
    .du-toast.show { transform: translateX(0); }
    .du-toast.du-toast-success { background: #065f46; }
    .du-toast.du-toast-error   { background: #7f1d1d; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <div class="text-muted small mb-1">
                    <i class="bi bi-arrow-left-circle me-1 text-primary"></i>
                    <a href="{{ route('ppdb.pengumuman.index') }}" class="text-primary text-decoration-none fw-semibold">
                        Kembali ke Pengumuman
                    </a>
                </div>
                <h2 class="fw-800 text-dark mb-0 d-flex align-items-center gap-2">
                    <span>Daftar Ulang</span>
                    @if($statusPendaftaran === 'siswa_tetap')
                        <span class="badge bg-success">Selesai</span>
                    @elseif($statusPendaftaran === 'daftar_ulang')
                        <span class="badge bg-info">Menunggu</span>
                    @elseif($statusKelulusan === 'lulus')
                        <span class="badge bg-warning text-dark">Diperlukan</span>
                    @endif
                </h2>
            </div>
            <div class="bg-white p-2 rounded-3 shadow-sm border">
                <span class="text-muted small px-2">Peserta: <strong class="text-primary">{{ $namaPeserta }}</strong></span>
            </div>
        </div>

        {{-- ================================================================
             STATE 1: SISWA TETAP — Sudah dikonfirmasi admin
             ================================================================ --}}
        @if($statusPendaftaran === 'siswa_tetap')
            <div class="du-card mb-4">
                {{-- Celebration Header --}}
                <div class="du-celebration-header">
                    <div class="confetti-overlay" id="confettiContainer"></div>
                    <div class="display-3 mb-3" style="position: relative; z-index: 2;">🎓</div>
                    <h1 class="fw-900 text-white mb-2" style="position: relative; z-index: 2;">Selamat!</h1>
                    <p class="text-white opacity-75 mb-4 fs-5" style="position: relative; z-index: 2;">
                        Anda resmi menjadi <strong>Siswa Baru</strong>!
                    </p>

                    {{-- Info Tiles --}}
                    <div class="row g-2 justify-content-center" style="position: relative; z-index: 2; max-width: 480px; margin: 0 auto;">
                        <div class="col-6 col-sm-4">
                            <div class="du-tile">
                                <div class="du-tile-label">Nama</div>
                                <div class="du-tile-value" style="font-size: .85rem;">{{ \Illuminate\Support\Str::limit($namaPeserta, 15) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="du-tile">
                                <div class="du-tile-label">Tahun Pelajaran</div>
                                <div class="du-tile-value" style="font-size: .85rem;">{{ $namaTahun }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="du-tile">
                                <div class="du-tile-label">No. Pendaftaran</div>
                                <div class="du-tile-value" style="font-size: .85rem;">{{ $noPendaftaran }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-4">
                    <div class="du-alert-success mb-4 text-center">
                        <div class="fs-1 mb-2">🎉</div>
                        <h5 class="fw-bold text-success mb-1">Selamat Datang di {{ $namaSekolah }}!</h5>
                        <p class="text-success opacity-75 mb-0">
                            Anda telah terdaftar sebagai siswa resmi angkatan tahun pelajaran
                            <strong>{{ $namaTahun }}</strong>. Pantau informasi lebih lanjut melalui portal.
                        </p>
                    </div>
                    <a href="{{ route('ppdb.dashboard') }}" class="du-btn-confirm text-decoration-none">
                        <i class="bi bi-house"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

        {{-- ================================================================
             STATE 2: DAFTAR ULANG — Menunggu konfirmasi admin
             ================================================================ --}}
        @elseif($statusPendaftaran === 'daftar_ulang')
            <div class="du-card mb-4">
                {{-- Success Header --}}
                <div style="background: linear-gradient(135deg, #0c4a6e 0%, #075985 100%); padding: 2.5rem 2rem; text-align: center; position: relative;">
                    <div style="font-size: 3rem; margin-bottom: .5rem;">✅</div>
                    <h2 class="text-white fw-800 mb-2">Daftar Ulang Terkirim!</h2>
                    <p class="text-white opacity-75 mb-0">Konfirmasi Anda telah berhasil dikirim ke sistem.</p>
                </div>

                {{-- Body --}}
                <div class="p-4">
                    {{-- Waiting Card --}}
                    <div class="du-alert-success mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div style="width:44px;height:44px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span class="du-pulse-dot"></span>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-1">Menunggu Konfirmasi Admin</h6>
                                <p class="text-success opacity-75 mb-0 small">
                                    Data daftar ulang Anda sedang divalidasi oleh panitia PPDB.
                                    Notifikasi konfirmasi akan dikirim melalui email dan portal peserta ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Info Items --}}
                    <div class="du-info-strip rounded-3 mb-4">
                        <div class="du-info-item">
                            <div class="du-info-icon" style="background:#dbeafe;">📋</div>
                            <div>
                                <div class="du-info-label">No. Pendaftaran</div>
                                <div class="du-info-value">{{ $noPendaftaran }}</div>
                            </div>
                        </div>
                        <div class="du-info-item">
                            <div class="du-info-icon" style="background:#dcfce7;">🎓</div>
                            <div>
                                <div class="du-info-label">Jalur Pendaftaran</div>
                                <div class="du-info-value">{{ $namaJalur }}</div>
                            </div>
                        </div>
                        <div class="du-info-item">
                            <div class="du-info-icon" style="background:#fef9c3;">📅</div>
                            <div>
                                <div class="du-info-label">Tahun Pelajaran</div>
                                <div class="du-info-value">{{ $namaTahun }}</div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('ppdb.dashboard') }}" class="du-btn-confirm text-decoration-none" style="background: linear-gradient(135deg, #0c4a6e, #075985);">
                        <i class="bi bi-house"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

        {{-- ================================================================
             STATE 3: LULUS + TERLAMBAT — Deadline terlewat
             ================================================================ --}}
        @elseif($statusKelulusan === 'lulus' && $terlambat)
            <div class="du-card mb-4">
                {{-- Danger Header --}}
                <div style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); padding: 2.5rem 2rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: .5rem;">⏰</div>
                    <h2 class="text-white fw-800 mb-2">Batas Waktu Terlewat</h2>
                    <p class="text-white opacity-75 mb-0">
                        Deadline daftar ulang telah melewati batas yang ditentukan.
                    </p>
                </div>

                <div class="p-4">
                    {{-- Warning Detail --}}
                    <div class="du-alert-danger mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div style="font-size:2rem;line-height:1;">🚫</div>
                            <div>
                                <h6 class="fw-bold text-danger mb-1">Batas Waktu Daftar Ulang Telah Lewat</h6>
                                @if($deadline)
                                    <p class="text-danger opacity-75 mb-1 small">
                                        Deadline:
                                        <strong>{{ \Carbon\Carbon::parse($deadline)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong>
                                        pukul <strong>{{ \Carbon\Carbon::parse($deadline)->format('H:i') }} WIB</strong>
                                    </p>
                                @endif
                                <p class="text-danger opacity-75 mb-0 small">
                                    Waktu pendaftaran Anda sudah tidak bisa diproses secara otomatis.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Admin --}}
                    <div class="du-alert-warning mb-4">
                        <h6 class="fw-bold text-warning-emphasis mb-2">
                            <i class="bi bi-telephone me-2"></i>Hubungi Admin Sekolah
                        </h6>
                        <p class="mb-2 small text-muted">
                            Segera hubungi panitia PPDB {{ $namaSekolah }} untuk mendapatkan penanganan lebih lanjut.
                        </p>
                        @php $kontak = config('ppdb.kontak', null); @endphp
                        @if($kontak)
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @if(isset($kontak['whatsapp']))
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $kontak['whatsapp']) }}" target="_blank"
                                       class="btn btn-sm btn-success rounded-pill fw-semibold">
                                        <i class="bi bi-whatsapp me-1"></i>{{ $kontak['whatsapp'] }}
                                    </a>
                                @endif
                                @if(isset($kontak['email']))
                                    <a href="mailto:{{ $kontak['email'] }}"
                                       class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold">
                                        <i class="bi bi-envelope me-1"></i>{{ $kontak['email'] }}
                                    </a>
                                @endif
                                @if(isset($kontak['telepon']))
                                    <a href="tel:{{ preg_replace('/\D/', '', $kontak['telepon']) }}"
                                       class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold">
                                        <i class="bi bi-telephone me-1"></i>{{ $kontak['telepon'] }}
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="mb-0 small fw-semibold text-dark">
                                Datang langsung ke kantor {{ $namaSekolah }} pada jam kerja (07.00 – 14.00 WIB).
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('ppdb.dashboard') }}" class="du-btn-confirm text-decoration-none" style="background: linear-gradient(135deg, #b45309, #92400e);">
                        <i class="bi bi-house"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

        {{-- ================================================================
             STATE 4: LULUS + BELUM TERLAMBAT — Form konfirmasi
             ================================================================ --}}
        @elseif($statusKelulusan === 'lulus' && !$terlambat)
            <div class="du-card mb-4">
                {{-- Success Header --}}
                <div class="du-celebration-header" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);">
                    <div class="confetti-overlay" id="confettiContainer"></div>
                    <div style="position: relative; z-index: 2;">
                        <div class="display-4 mb-2">🏆</div>
                        <h2 class="fw-900 text-white mb-2">Selamat, Anda Lulus!</h2>
                        <p class="text-white opacity-75 mb-4">
                            Segera lakukan daftar ulang sebelum batas waktu.
                        </p>

                        {{-- Tiles --}}
                        <div class="row g-2 justify-content-center" style="max-width: 480px; margin: 0 auto;">
                            <div class="col-6 col-sm-3">
                                <div class="du-tile">
                                    <div class="du-tile-label">Jalur</div>
                                    <div class="du-tile-value" style="font-size: .8rem;">{{ \Illuminate\Support\Str::limit($namaJalur, 12) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="du-tile">
                                    <div class="du-tile-label">Total Nilai</div>
                                    <div class="du-tile-value">{{ $hasilSeleksi?->total_nilai ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="du-tile">
                                    <div class="du-tile-label">Peringkat</div>
                                    <div class="du-tile-value">{{ $hasilSeleksi?->peringkat ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="du-tile">
                                    <div class="du-tile-label">No. Daftar</div>
                                    <div class="du-tile-value" style="font-size: .8rem;">{{ \Illuminate\Support\Str::limit($noPendaftaran, 12) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-4">
                    {{-- Countdown --}}
                    @if($deadline)
                        <div class="du-countdown-wrap">
                            <div class="du-countdown-label">
                                <i class="bi bi-alarm me-2"></i>Sisa Waktu Daftar Ulang
                            </div>
                            <div class="du-countdown-timer">
                                <div class="du-countdown-unit">
                                    <span class="du-countdown-number" id="cd-hari">{{ $sisaHari }}</span>
                                    <div class="du-countdown-unit-label">Hari</div>
                                </div>
                                <div class="du-countdown-sep">:</div>
                                <div class="du-countdown-unit">
                                    <span class="du-countdown-number" id="cd-jam">{{ str_pad($sisaJam, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="du-countdown-unit-label">Jam</div>
                                </div>
                                <div class="du-countdown-sep">:</div>
                                <div class="du-countdown-unit">
                                    <span class="du-countdown-number" id="cd-menit">{{ str_pad($sisaMenit, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="du-countdown-unit-label">Menit</div>
                                </div>
                                <div class="du-countdown-sep">:</div>
                                <div class="du-countdown-unit">
                                    <span class="du-countdown-number" id="cd-detik">00</span>
                                    <div class="du-countdown-unit-label">Detik</div>
                                </div>
                            </div>
                            <div class="mt-2 small text-primary opacity-75">
                                <i class="bi bi-calendar-event me-1"></i>
                                Batas akhir:
                                <strong>{{ \Carbon\Carbon::parse($deadline)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</strong>
                                pukul <strong>{{ \Carbon\Carbon::parse($deadline)->format('H:i') }} WIB</strong>
                            </div>
                        </div>
                    @endif

                    {{-- Form Konfirmasi --}}
                    <div class="du-confirm-section">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                            Pernyataan Daftar Ulang
                        </h6>

                        <form id="formDaftarUlang">
                            @csrf
                            {{-- Pernyataan --}}
                            <p class="text-muted small mb-3">
                                Dengan melakukan daftar ulang, saya menyatakan siap mengikuti proses penerimaan
                                peserta didik baru <strong>{{ $namaSekolah }}</strong>
                                tahun pelajaran <strong>{{ $namaTahun }}</strong>.
                            </p>

                            {{-- Checkbox --}}
                            <label class="du-checkbox-wrap" for="chk-hadir">
                                <input type="checkbox" id="chk-hadir" name="pernyataan_hadir"
                                    value="1" onchange="toggleConfirmBtn()">
                                <span class="du-checkbox-label">
                                    Saya menyatakan <strong>hadir dan siap mengikuti proses selanjutnya</strong>
                                    dalam penerimaan peserta didik baru {{ $namaSekolah }}.
                                </span>
                            </label>

                            {{-- Submit Button --}}
                            <button type="button" id="btnDaftarUlang" class="du-btn-confirm" disabled
                                onclick="showModalKonfirmasi()">
                                <div class="du-btn-spinner" id="btnSpinner"></div>
                                <i class="bi bi-check-circle-fill" id="btnIcon"></i>
                                <span id="btnText">Konfirmasi Daftar Ulang</span>
                            </button>
                        </form>
                    </div>

                    {{-- Info Tambahan --}}
                    <div class="small text-muted text-center">
                        <i class="bi bi-info-circle me-1"></i>
                        Setelah konfirmasi, status Anda akan berubah menjadi "Menunggu Konfirmasi Admin".
                        Admin akan memverifikasi daftar ulang Anda dalam 1×24 jam kerja.
                    </div>
                </div>
            </div>

        {{-- ================================================================
             STATE 5: Bukan peserta lulus — Akses tidak valid
             ================================================================ --}}
        @else
            <div class="du-card mb-4">
                <div style="background: linear-gradient(135deg, #78350f 0%, #92400e 100%); padding: 2.5rem 2rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: .5rem;">🔒</div>
                    <h2 class="text-white fw-800 mb-2">Akses Tidak Valid</h2>
                    <p class="text-white opacity-75 mb-0">Halaman ini hanya tersedia untuk peserta tertentu.</p>
                </div>

                <div class="p-4">
                    <div class="du-alert-error mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div style="font-size: 2rem;">⚠️</div>
                            <div>
                                <h6 class="fw-bold text-danger-emphasis mb-1">Akses Ditolak</h6>
                                <p class="text-muted mb-0 small">
                                    Halaman daftar ulang <strong>hanya untuk peserta yang dinyatakan lulus</strong>
                                    berdasarkan hasil seleksi PPDB. Status pendaftaran Anda saat ini tidak memenuhi
                                    syarat untuk mengakses halaman ini.
                                </p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('ppdb.pengumuman.index') }}" class="du-btn-confirm text-decoration-none" style="background: linear-gradient(135deg, #78350f, #92400e);">
                        <i class="bi bi-arrow-left"></i>
                        Kembali ke Pengumuman
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>

{{-- ── MODAL KONFIRMASI AKHIR ──────────────────────────────────────────── --}}
<div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-labelledby="modalKonfirmasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 p-4 pb-2">
                <h5 class="modal-title fw-800" id="modalKonfirmasiLabel">
                    Konfirmasi Daftar Ulang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-2">
                <div class="text-center mb-3">
                    <div style="font-size: 3rem;">🎯</div>
                </div>
                <p class="text-muted small text-center mb-3">
                    Apakah Anda yakin ingin melakukan daftar ulang?
                    Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                </p>
                <div style="background:#f0fdf4;border-radius:10px;padding:.75rem 1rem;border:1px solid #bbf7d0;" class="small text-success fw-semibold text-center">
                    ✅ {{ $namaPeserta }} — {{ $namaJalur }}
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2 gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3"
                    data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnModalSubmit" class="btn btn-success rounded-pill px-4 fw-bold flex-grow-1"
                    onclick="submitDaftarUlang()">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="modalSpinner" role="status"></span>
                    <span id="modalBtnText">Ya, Konfirmasi!</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── TOAST ───────────────────────────────────────────────────────────── --}}
<div class="du-toast-wrap" id="toastWrap"></div>

@endsection

@push('scripts')
<script>
/**
 * ── CONSTANTS ──────────────────────────────────────────────────────────
 */
const STORE_URL      = "{{ route('ppdb.daftar-ulang.store', optional($pendaftaran)->id ?? 0) }}";
const CSRF_TOKEN     = document.querySelector('meta[name="csrf-token"]')?.content;
@if($deadline)
const DEADLINE_TS    = {{ \Carbon\Carbon::parse($deadline)->timestamp }};
@else
const DEADLINE_TS    = null;
@endif

/**
 * ── CHECKBOX TOGGLE ────────────────────────────────────────────────────
 */
function toggleConfirmBtn() {
    const chk = document.getElementById('chk-hadir');
    const btn = document.getElementById('btnDaftarUlang');
    if (chk && btn) {
        btn.disabled = !chk.checked;
    }
}

/**
 * ── SHOW MODAL ─────────────────────────────────────────────────────────
 */
function showModalKonfirmasi() {
    const chk = document.getElementById('chk-hadir');
    if (!chk || !chk.checked) return;
    const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasi'));
    modal.show();
}

/**
 * ── SUBMIT DAFTAR ULANG (AJAX) ─────────────────────────────────────────
 */
function submitDaftarUlang() {
    const modalSpinner = document.getElementById('modalSpinner');
    const modalBtnText = document.getElementById('modalBtnText');
    const modalSubmit  = document.getElementById('btnModalSubmit');

    // Loading state
    if (modalSpinner) modalSpinner.classList.remove('d-none');
    if (modalBtnText) modalBtnText.textContent = 'Memproses...';
    if (modalSubmit)  modalSubmit.disabled = true;

    fetch(STORE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  CSRF_TOKEN,
        },
        body: JSON.stringify({
            pernyataan_hadir: true,
        }),
    })
    .then(res => res.json())
    .then(data => {
        // Tutup modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasi'));
        if (modal) modal.hide();

        if (data.success) {
            showToast('success', data.message || 'Daftar ulang berhasil dikonfirmasi!');
            // Reload halaman untuk tampilkan state "Menunggu konfirmasi admin"
            setTimeout(() => { location.reload(); }, 1800);
        } else {
            showToast('error', data.message || 'Gagal melakukan daftar ulang.');
            // Reset modal button
            if (modalSpinner) modalSpinner.classList.add('d-none');
            if (modalBtnText) modalBtnText.textContent = 'Ya, Konfirmasi!';
            if (modalSubmit)  modalSubmit.disabled = false;
        }
    })
    .catch(err => {
        console.error('[DaftarUlang] Error:', err);
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalKonfirmasi'));
        if (modal) modal.hide();
        showToast('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        if (modalSpinner) modalSpinner.classList.add('d-none');
        if (modalBtnText) modalBtnText.textContent = 'Ya, Konfirmasi!';
        if (modalSubmit)  modalSubmit.disabled = false;
    });
}

/**
 * ── TOAST ───────────────────────────────────────────────────────────────
 */
function showToast(type, message) {
    const wrap  = document.getElementById('toastWrap');
    if (!wrap) return;
    const toast = document.createElement('div');
    toast.className = `du-toast du-toast-${type}`;
    toast.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <span>${type === 'success' ? '✅' : '❌'}</span>
            <span>${message}</span>
        </div>`;
    wrap.appendChild(toast);
    requestAnimationFrame(() => { toast.classList.add('show'); });
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

/**
 * ── COUNTDOWN TIMER ─────────────────────────────────────────────────────
 */
(function initCountdown() {
    if (!DEADLINE_TS) return;
    const elHari  = document.getElementById('cd-hari');
    const elJam   = document.getElementById('cd-jam');
    const elMenit = document.getElementById('cd-menit');
    const elDetik = document.getElementById('cd-detik');
    if (!elHari || !elJam || !elMenit || !elDetik) return;

    function padTwo(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const now   = Math.floor(Date.now() / 1000);
        const diff  = DEADLINE_TS - now;

        if (diff <= 0) {
            elHari.textContent  = '0';
            elJam.textContent   = '00';
            elMenit.textContent = '00';
            elDetik.textContent = '00';
            // Reload agar tampilkan state terlambat
            location.reload();
            return;
        }

        const hari  = Math.floor(diff / 86400);
        const jam   = Math.floor((diff % 86400) / 3600);
        const menit = Math.floor((diff % 3600) / 60);
        const detik = diff % 60;

        elHari.textContent  = hari;
        elJam.textContent   = padTwo(jam);
        elMenit.textContent = padTwo(menit);
        elDetik.textContent = padTwo(detik);
    }

    tick();
    setInterval(tick, 1000);
})();

/**
 * ── CONFETTI ─────────────────────────────────────────────────────────────
 */
(function initConfetti() {
    const container = document.getElementById('confettiContainer');
    if (!container) return;

    const colors = ['#ff595e','#ffca3a','#8ac926','#1982c4','#6a4c93','#ff924c','#c77dff','#4cc9f0'];
    const count  = 18;

    for (let i = 0; i < count; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.cssText = `
            left: ${Math.random() * 100}%;
            top: -10px;
            background-color: ${colors[Math.floor(Math.random() * colors.length)]};
            animation-delay: ${(i * 0.18).toFixed(2)}s;
            animation-duration: ${(2.5 + Math.random() * 1.5).toFixed(2)}s;
            width: ${6 + Math.floor(Math.random() * 6)}px;
            height: ${6 + Math.floor(Math.random() * 6)}px;
            border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
        `;
        container.appendChild(piece);
    }
})();

/**
 * ── RESET modal state on hide ────────────────────────────────────────────
 */
document.getElementById('modalKonfirmasi')?.addEventListener('hidden.bs.modal', function () {
    const modalSpinner = document.getElementById('modalSpinner');
    const modalBtnText = document.getElementById('modalBtnText');
    const modalSubmit  = document.getElementById('btnModalSubmit');
    if (modalSpinner) modalSpinner.classList.add('d-none');
    if (modalBtnText) modalBtnText.textContent = 'Ya, Konfirmasi!';
    if (modalSubmit)  modalSubmit.disabled = false;
});
</script>
@endpush
