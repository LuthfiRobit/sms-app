@extends('layouts.portal-stitch')

@section('title', 'Profil Saya')

@section('navbar')
<nav class="bg-gradient-to-r from-primary to-primary-container text-on-primary sticky top-0 border-b-4 border-tertiary-container shadow-md z-50">
    <div class="flex justify-between items-center w-full px-4 md:px-8 h-20 max-w-[1440px] mx-auto">
        <div class="flex items-center gap-4">
            <div class="bg-surface text-primary p-2 rounded-lg font-headline-sm text-headline-sm font-bold shadow-sm select-none">LP</div>
            <span class="font-headline-md text-headline-md text-on-primary hidden sm:block">LP Ma'arif NU Kraksaan</span>
            <span class="text-body-sm font-semibold text-on-primary sm:hidden">LP Ma'arif NU</span>
        </div>

        <div class="hidden md:flex gap-8 items-center">
            <a href="{{ route('ppdb.beranda') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Beranda</a>
            <a href="{{ route('ppdb.pendaftaran.index') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Pendaftaran</a>
            <a href="{{ route('ppdb.profil.index') }}"
               class="font-label-md text-label-md text-on-primary border-b-2 border-tertiary-fixed-dim pb-1">Profil</a>
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
@php
    $peserta = $profil['peserta'];
    $alamat  = $profil['alamat'];
    $ortu    = $profil['orang_tua'];
    $periodik = $profil['periodik'];
    $kontak  = $profil['kontak'];
    $dokumen = $profil['dokumen_pribadi'];
    $klp     = $profil['kelengkapan'];
    $persen  = $klp['persen'];
    $user    = auth()->user();

    $tabStatus = [
        'akun'    => !empty($user->name) && !empty($user->email),
        'pribadi' => !empty($peserta->nama_lengkap) && !empty($peserta->jenis_kelamin) && !empty($peserta->tanggal_lahir),
        'alamat'  => !empty($alamat?->alamat) && !empty($alamat?->kabupaten_kota),
        'ortu'    => !empty($ortu['ayah']?->nama) || !empty($ortu['ibu']?->nama),
        'periodik'=> !empty($periodik?->tinggi_badan) && !empty($periodik?->berat_badan),
        'kontak'  => !empty($kontak?->no_hp) || !empty($user->no_hp),
        'dokumen' => !empty($dokumen?->no_kip) || !empty($dokumen?->no_pkh),
    ];

    $tabs = [
        ['id' => 'akun',    'icon' => 'manage_accounts', 'label' => 'Akun'],
        ['id' => 'pribadi', 'icon' => 'badge',            'label' => 'Pribadi'],
        ['id' => 'alamat',  'icon' => 'location_on',      'label' => 'Alamat'],
        ['id' => 'ortu',    'icon' => 'family_restroom',  'label' => 'Orang Tua'],
        ['id' => 'periodik','icon' => 'monitor_heart',    'label' => 'Periodik'],
        ['id' => 'kontak',  'icon' => 'call',             'label' => 'Kontak'],
        ['id' => 'dokumen', 'icon' => 'folder_open',      'label' => 'Dokumen'],
    ];
@endphp

{{-- Toast Notification --}}
<div id="ppdb-toast"
     class="fixed top-5 right-5 z-[9999] hidden min-w-[280px] max-w-sm flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-on-primary text-body-sm font-semibold transition-all duration-300"
     role="alert" aria-live="assertive">
    <span id="ppdb-toast-icon" class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
    <span id="ppdb-toast-msg"></span>
</div>

<div class="w-full max-w-[1440px] mx-auto px-4 md:px-8 py-6 flex flex-col gap-6">

    {{-- Profile Banner --}}
    <div class="bg-gradient-to-r from-primary to-primary-container rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden shadow-lg border-b-4 border-tertiary-container">
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

        {{-- Avatar --}}
        <div class="relative flex-shrink-0">
            <img id="foto-preview"
                 src="{{ $peserta->foto ? asset('storage/' . $peserta->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=120&background=00843d&color=fff&bold=true&rounded=true' }}"
                 alt="Foto Profil"
                 class="w-24 h-24 md:w-28 md:h-28 rounded-2xl object-cover ring-4 ring-white/30 shadow-lg">
            <label for="input-foto"
                   class="absolute -bottom-2 -right-2 w-9 h-9 bg-tertiary-fixed-dim hover:bg-tertiary-fixed text-on-tertiary-fixed rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors"
                   title="Ganti Foto">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1">photo_camera</span>
            </label>
            <input type="file" id="input-foto" accept="image/*" class="hidden">
        </div>

        {{-- Info --}}
        <div class="relative z-10 flex-1 text-center md:text-left">
            <h1 class="text-headline-md text-on-primary font-bold mb-1">{{ $user->name }}</h1>
            <div class="flex items-center justify-center md:justify-start gap-2 text-on-primary/75 text-body-sm mb-3">
                <span class="material-symbols-outlined text-[16px]">mail</span>
                <span>{{ $user->email }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 bg-white/15 text-on-primary text-[12px] font-semibold px-3 py-1 rounded-full">
                <span class="w-2 h-2 rounded-full {{ $user->status === 'active' ? 'bg-secondary-fixed' : 'bg-tertiary-fixed-dim' }}"></span>
                {{ $user->status === 'active' ? 'Akun Aktif' : ucfirst($user->status) }}
            </span>
        </div>

        {{-- Progress --}}
        <div class="relative z-10 w-full md:w-64 bg-white/10 rounded-xl p-4 backdrop-blur-sm">
            <div class="flex justify-between items-center mb-2">
                <span class="text-on-primary/80 text-body-sm">Kelengkapan Profil</span>
                <span id="persen-label" class="text-on-primary font-extrabold text-headline-sm">{{ $persen }}%</span>
            </div>
            <div class="w-full bg-white/20 rounded-full h-2.5 mb-2">
                <div id="sidebar-progress-bar"
                     class="h-2.5 rounded-full bg-tertiary-fixed-dim transition-all duration-500"
                     style="width: {{ $persen }}%"
                     role="progressbar" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            @if(!empty($klp['item_kurang']))
            <p class="text-on-primary/60 text-[11px] flex items-start gap-1">
                <span class="material-symbols-outlined text-[14px] flex-shrink-0 mt-0.5">warning</span>
                Belum lengkap: {{ implode(', ', array_slice($klp['item_kurang'], 0, 3)) }}{{ count($klp['item_kurang']) > 3 ? '…' : '' }}
            </p>
            @endif
        </div>
    </div>

    {{-- Main: Sidebar + Content --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Sidebar Nav --}}
        <div class="lg:w-60 flex-shrink-0">
            <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
                <nav class="flex flex-row lg:flex-col overflow-x-auto" aria-label="Navigasi profil">
                    @foreach($tabs as $tab)
                    <button
                        class="profil-nav-item flex items-center gap-3 px-4 py-3 text-label-md text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors duration-150 flex-shrink-0 lg:w-full relative text-left group"
                        data-tab="{{ $tab['id'] }}"
                        id="nav-{{ $tab['id'] }}"
                        type="button"
                        aria-selected="false"
                        role="tab">
                        <span class="material-symbols-outlined text-[22px] flex-shrink-0 group-[.active]:text-primary">{{ $tab['icon'] }}</span>
                        <span class="hidden lg:inline nav-label flex-1">{{ $tab['label'] }}</span>
                        <span class="tab-dot ml-auto w-2 h-2 rounded-full flex-shrink-0 {{ $tabStatus[$tab['id']] ? 'bg-secondary dot-ok' : 'bg-error/40 dot-miss' }}"
                              id="dot-{{ $tab['id'] }}"
                              title="{{ $tabStatus[$tab['id']] ? 'Lengkap' : 'Belum diisi' }}"></span>
                    </button>
                    @endforeach
                </nav>

                <div class="p-4 border-t border-outline-variant hidden lg:block">
                    <a href="{{ route('ppdb.pendaftaran.index') }}"
                       class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-4 rounded-lg flex items-center justify-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                        <span class="material-symbols-outlined text-[20px]">edit_document</span>
                        Mulai Pendaftaran
                    </a>
                </div>
            </div>
        </div>

        {{-- Content Panes --}}
        <div class="flex-1 min-w-0" id="profil-content" role="tabpanel">
            @include('portal.profil.partials._tab_akun')
            @include('portal.profil.partials._tab_pribadi')
            @include('portal.profil.partials._tab_alamat')
            @include('portal.profil.partials._tab_ortu')
            @include('portal.profil.partials._tab_periodik')
            @include('portal.profil.partials._tab_kontak')
            @include('portal.profil.partials._tab_dokumen')
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .profil-nav-item.active {
        background-color: #eff5ec;
        color: #00682f;
        font-weight: 600;
        border-right: 3px solid #00682f;
    }
    .profil-nav-item.active .material-symbols-outlined { color: #00682f; }
    @media (max-width: 1023px) {
        .profil-nav-item.active { border-right: none; border-bottom: 3px solid #00682f; }
    }
</style>
@endpush

@push('scripts')
    @include('portal.profil.partials._scripts')
@endpush
