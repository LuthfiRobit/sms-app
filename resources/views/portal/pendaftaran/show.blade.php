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

    @include('portal.pendaftaran.partials._show_header')
    @include('portal.pendaftaran.partials._show_status')
    @include('portal.pendaftaran.partials._show_formulir')
    @include('portal.pendaftaran.partials._show_dokumen')
    @include('portal.pendaftaran.partials._show_actions')
    @include('portal.pendaftaran.partials._show_modals')

@endsection

@push('styles')
    @include('portal.pendaftaran.partials._show_styles')
@endpush

@push('scripts')
    @include('portal.pendaftaran.partials._show_scripts')
@endpush