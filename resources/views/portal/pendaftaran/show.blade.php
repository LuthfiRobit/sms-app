@extends('layouts.portal-stitch')

@php
    $pend    = $detail['data']['pendaftaran'] ?? null;
    $peserta = $detail['data']['peserta'] ?? null;
    $jalur   = $detail['data']['jalur'] ?? null;
    $fvMap   = collect($detail['data']['field_values'] ?? [])->keyBy('formulir_field_id');
    $dokMap  = collect($detail['data']['dokumen'] ?? [])->keyBy('syarat_id');
    $prog    = $progress['data'] ?? ['formulir'=>['persen'=>0,'terisi'=>0,'total'=>0,'kurang'=>[]],'dokumen'=>['persen'=>0,'uploaded'=>0,'total'=>0,'kurang'=>[]],'siap_submit'=>false];
    $status     = $pend?->status ?? 'draft';
    $isDraft    = $status === 'draft';
    $totalPersen = round(($prog['formulir']['persen'] + $prog['dokumen']['persen']) / 2, 1);

    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $fmtTgl = fn($d) => $d ? ($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->day . ' ' . $bulanIndo[($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->month] . ' ' . ($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->year : '—';

    $statusCfg = [
        'draft'       => ['icon'=>'draft',         'label'=>'Draft',        'bg'=>'bg-surface-container-high', 'text'=>'text-on-surface-variant'],
        'submit'      => ['icon'=>'hourglass_empty','label'=>'Menunggu',    'bg'=>'bg-amber-50',               'text'=>'text-amber-700'],
        'verifikasi'  => ['icon'=>'manage_search',  'label'=>'Verifikasi',  'bg'=>'bg-blue-50',                'text'=>'text-blue-700'],
        'lulus'       => ['icon'=>'emoji_events',   'label'=>'Lulus',       'bg'=>'bg-green-50',               'text'=>'text-green-700'],
        'tidak_lulus' => ['icon'=>'cancel',         'label'=>'Tidak Lulus', 'bg'=>'bg-red-50',                 'text'=>'text-red-700'],
        'daftar_ulang'=> ['icon'=>'autorenew',      'label'=>'Daftar Ulang','bg'=>'bg-blue-50',                'text'=>'text-blue-700'],
        'siswa_tetap' => ['icon'=>'school',         'label'=>'Siswa Tetap', 'bg'=>'bg-green-50',               'text'=>'text-green-700'],
    ];
    $st = $statusCfg[$status] ?? ['icon'=>'circle','label'=>$status,'bg'=>'bg-surface-container-high','text'=>'text-on-surface-variant'];

    $urlSave   = route('ppdb.pendaftaran.formulir', $pend?->id);
    $urlSubmit = route('ppdb.pendaftaran.submit', $pend?->id);
@endphp

@section('title', 'Pendaftaran #' . ($pend?->no_pendaftaran) . ' — PPDB')

@section('navbar')
<nav class="bg-gradient-to-r from-primary to-primary-container text-on-primary sticky top-0 border-b-4 border-tertiary-container shadow-md z-50">
    <div class="flex justify-between items-center w-full px-4 md:px-8 h-20 max-w-[1440px] mx-auto">
        <div class="flex items-center gap-4">
            <div class="bg-surface text-primary p-2 rounded-lg font-headline-sm text-headline-sm font-bold shadow-sm select-none">LP</div>
            <span class="font-headline-md text-headline-md text-on-primary hidden sm:block">Marifat</span>
        </div>

        <div class="hidden md:flex gap-8 items-center">
            <a href="{{ route('ppdb.beranda') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Beranda</a>
            <a href="{{ route('ppdb.pendaftaran.index') }}"
               class="font-label-md text-label-md text-on-primary border-b-2 border-tertiary-fixed-dim pb-1">Pendaftaran</a>
            <a href="{{ route('ppdb.profil.index') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Profil</a>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('ppdb.profil.index') }}"
               class="bg-surface text-primary px-4 md:px-6 py-2 rounded-lg font-label-md text-label-md shadow-sm hover:bg-surface-container-low transition-colors duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">account_circle</span>
                <span class="hidden sm:inline">Akun Saya</span>
            </a>
            <form action="{{ route('ppdb.logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="bg-white/10 hover:bg-white/20 text-on-primary px-3 py-2 rounded-lg text-body-sm transition-colors flex items-center gap-1.5"
                        title="Keluar">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span class="hidden md:inline">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</nav>
@endsection

@section('content')

{{-- Toast --}}
<div id="ppdb-toast"
     class="fixed top-5 right-5 z-[9999] hidden min-w-[280px] max-w-sm flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-on-primary text-body-sm font-semibold transition-all duration-300"
     role="alert" aria-live="assertive">
    <span id="ppdb-toast-icon" class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
    <span id="ppdb-toast-msg"></span>
</div>

<div class="w-full max-w-[1440px] mx-auto px-4 md:px-8 py-6">

    @include('portal.pendaftaran.partials._show_header')

    <div class="flex flex-col lg:flex-row gap-6 mt-6">

        {{-- SIDEBAR NAVIGATION --}}
        <aside class="lg:w-64 flex-shrink-0">
            <div class="bg-surface-container-lowest rounded-2xl soft-shadow overflow-hidden sticky top-24">
                <div class="px-5 py-4 bg-surface-container-low border-b border-outline-variant">
                    <span class="text-label-md font-bold text-on-surface-variant uppercase tracking-wide">Navigasi</span>
                </div>
                <nav class="p-3 flex flex-col gap-1" aria-label="Navigasi Pendaftaran">
                    <a href="#status"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container transition-colors text-body-md font-medium text-on-surface">
                        <span class="material-symbols-outlined text-primary text-[20px]">info</span>
                        <span class="flex-1">Status & Informasi</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-secondary flex-shrink-0"></span>
                    </a>
                    <a href="#formulir"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container transition-colors text-body-md font-medium text-on-surface">
                        <span class="material-symbols-outlined text-primary text-[20px]">assignment</span>
                        <span class="flex-1">Formulir Peserta</span>
                        <span class="w-2.5 h-2.5 rounded-full {{ $prog['formulir']['persen'] >= 100 ? 'bg-secondary' : 'bg-error/40' }} flex-shrink-0" id="dot-formulir"></span>
                    </a>
                    <a href="#dokumen"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container transition-colors text-body-md font-medium text-on-surface">
                        <span class="material-symbols-outlined text-primary text-[20px]">upload_file</span>
                        <span class="flex-1">Berkas Dokumen</span>
                        <span class="w-2.5 h-2.5 rounded-full {{ $prog['dokumen']['persen'] >= 100 ? 'bg-secondary' : 'bg-error/40' }} flex-shrink-0" id="dot-dokumen"></span>
                    </a>
                </nav>

                <div class="p-4 border-t border-outline-variant">
                    <a href="{{ route('ppdb.pendaftaran.index') }}"
                       class="w-full flex items-center justify-center gap-2 bg-surface-container text-on-surface-variant text-label-md font-semibold py-2.5 px-4 rounded-xl hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Kembali ke Daftar
                    </a>
                </div>
            </div>
        </aside>

        {{-- CONTENT AREA --}}
        <div class="flex-1 min-w-0 flex flex-col gap-6">
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

</div>

@include('portal.pendaftaran.partials._show_modals')

@endsection

@push('scripts')
    @include('portal.pendaftaran.partials._show_scripts')
@endpush
