@extends('layouts.portal')

@php
    $pend = $detail['data']['pendaftaran'] ?? null;
    $peserta = $detail['data']['peserta'] ?? null;
    $jalur = $detail['data']['jalur'] ?? null;
    $fvMap = collect($detail['data']['field_values'] ?? [])->keyBy('formulir_field_id');
    $dokMap = collect($detail['data']['dokumen'] ?? [])->keyBy('syarat_id');
    $prog = $progress['data'] ?? ['formulir' => ['persen' => 0, 'terisi' => 0, 'total' => 0, 'kurang' => []], 'dokumen' => ['persen' => 0, 'uploaded' => 0, 'total' => 0, 'kurang' => []], 'siap_submit' => false];
    $status = $pend?->status ?? 'draft';
    $isDraft = $status === 'draft';
    $totalPersen = round(($prog['formulir']['persen'] + $prog['dokumen']['persen']) / 2, 1);

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
        12 => 'Desember'
    ];
    $fmtTgl = fn($d) => $d ? ($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->day . ' ' . $bulanIndo[($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->month] . ' ' . ($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->year : '—';

    $statusCfg = [
        'draft' => ['color' => 'secondary', 'label' => 'Draft', 'icon' => 'bi-file-earmark'],
        'submit' => ['color' => 'warning', 'label' => 'Menunggu', 'icon' => 'bi-hourglass-split'],
        'verifikasi' => ['color' => 'info', 'label' => 'Verifikasi', 'icon' => 'bi-search'],
        'lulus' => ['color' => 'success', 'label' => 'Lulus', 'icon' => 'bi-trophy-fill'],
        'tidak_lulus' => ['color' => 'danger', 'label' => 'Tidak Lulus', 'icon' => 'bi-x-circle-fill'],
        'daftar_ulang' => ['color' => 'primary', 'label' => 'Daftar Ulang', 'icon' => 'bi-arrow-repeat'],
        'siswa_tetap' => ['color' => 'success', 'label' => 'Siswa Tetap', 'icon' => 'bi-mortarboard-fill'],
    ];
    $st = $statusCfg[$status] ?? ['color' => 'secondary', 'label' => $status, 'icon' => 'bi-circle'];

    // URL routes untuk JS
    $urlSave = route('ppdb.pendaftaran.formulir', $pend?->id);
    $urlSubmit = route('ppdb.pendaftaran.submit', $pend?->id);
@endphp

@section('title', 'Pendaftaran #' . ($pend?->no_pendaftaran) . ' — PPDB')

@section('content')

    {{-- Breadcrumb & Header --}}
    @include('portal.pendaftaran.partials._show_header')

    <div class="row g-4 mb-5">
        {{-- SIDEBAR NAVIGATION --}}
        <div class="col-lg-3 col-md-4">
            <div class="profil-sidebar">
                <nav class="profil-nav" aria-label="Navigasi Pendaftaran">
                    <a href="#status" class="profil-nav-item ">
                        <i class="bi bi-info-circle-fill"></i>
                        <span class="nav-label">Status & Informasi</span>
                        <span class="tab-dot dot-ok"></span>
                    </a>
                    <a href="#formulir" class="profil-nav-item">
                        <i class="bi bi-ui-checks-grid"></i>
                        <span class="nav-label">Formulir Peserta</span>
                        <span class="tab-dot {{ $prog['formulir']['persen'] >= 100 ? 'dot-ok' : 'dot-miss' }}"></span>
                    </a>
                    <a href="#dokumen" class="profil-nav-item">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i>
                        <span class="nav-label">Berkas Dokumen</span>
                        <span class="tab-dot {{ $prog['dokumen']['persen'] >= 100 ? 'dot-ok' : 'dot-miss' }}"></span>
                    </a>
                </nav>

                {{-- Action Quick Link --}}
                <div class="mt-4 pt-4 border-top">
                    <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn-daftar">
                        <i class="bi bi-arrow-left"></i>
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        {{-- CONTENT AREA --}}
        <div class="col-lg-9 col-md-8">
            <div id="status">
                @include('portal.pendaftaran.partials._show_status')
            </div>

            <div id="formulir">
                @include('portal.pendaftaran.partials._show_formulir')
            </div>

            <div id="dokumen">
                @include('portal.pendaftaran.partials._show_dokumen')
            </div>

            @include('portal.pendaftaran.partials._show_actions')
        </div>
    </div>

    @include('portal.pendaftaran.partials._show_modals')

@endsection

@push('styles')
    @include('portal.pendaftaran.partials._show_styles')
    <style>
        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }

        /* Sticky sidebar offset */
        .profil-sidebar {
            top: 2rem;
            z-index: 10;
        }

        /* Active state for navigation items - handled via JS or CSS if using anchors */
        .profil-nav-item:target {
            background: linear-gradient(135deg, var(--color-primary-light), #bbf7d0);
            border-color: var(--color-primary);
        }
    </style>
@endpush

@push('scripts')
    @include('portal.pendaftaran.partials._show_scripts')
@endpush