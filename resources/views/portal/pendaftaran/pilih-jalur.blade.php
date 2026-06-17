@extends('layouts.portal-stitch')

@section('title', 'Pilih Jalur Pendaftaran — PPDB')

@section('navbar')
<nav class="bg-gradient-to-r from-primary to-primary-container text-on-primary sticky top-0 border-b-4 border-tertiary-container shadow-md z-50">
    <div class="flex justify-between items-center w-full px-4 md:px-8 h-20 max-w-[1440px] mx-auto">
        <div class="flex items-center gap-4">
            <div class="bg-surface text-primary p-2 rounded-lg font-headline-sm text-headline-sm font-bold shadow-sm select-none">LP</div>
            <span class="font-headline-md text-headline-md text-on-primary hidden sm:block">LP Ma'arif NU Kraksaan</span>
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
@php
    $jalurList    = $jalur['data'] ?? [];
    $profilCukup  = $cekProfil['cukup'] ?? false;
    $kekurangan   = $cekProfil['kekurangan'] ?? [];

    $cardColors = [
        ['bg' => '#15803d', 'ring' => '#86efac'],
        ['bg' => '#0369a1', 'ring' => '#93c5fd'],
        ['bg' => '#7c3aed', 'ring' => '#c4b5fd'],
        ['bg' => '#b45309', 'ring' => '#fcd34d'],
        ['bg' => '#be185d', 'ring' => '#f9a8d4'],
        ['bg' => '#0f766e', 'ring' => '#5eead4'],
    ];

    $bulanIndo = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                  7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
    $fmtDate   = function ($d) use ($bulanIndo) {
        if (!$d) return '—';
        $dt = $d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d);
        return $dt->day . ' ' . $bulanIndo[$dt->month] . ' ' . $dt->year;
    };
    $fmtRupiah = fn($n) => 'Rp ' . number_format($n ?? 0, 0, ',', '.');
    $user      = auth()->user();
@endphp

<div class="w-full max-w-[1440px] mx-auto px-4 md:px-8 py-6 flex flex-col gap-6">

    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-primary to-primary-container rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden shadow-lg border-b-4 border-tertiary-container">
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

        <div class="relative z-10 flex items-start gap-5 flex-1 min-w-0">
            <div class="w-16 h-16 rounded-2xl bg-white/20 border-2 border-white/30 flex items-center justify-center flex-shrink-0 shadow">
                <span class="material-symbols-outlined text-[32px] text-on-primary" style="font-variation-settings:'FILL' 1">signpost</span>
            </div>
            <div>
                <h1 class="text-display-sm font-bold text-on-primary mb-1">Pilih Jalur Pendaftaran</h1>
                <p class="text-body-md text-on-primary/80">Pilih jalur yang sesuai dengan kelayakan Anda</p>
                <div class="mt-4">
                    <a href="{{ route('ppdb.pendaftaran.index') }}"
                       class="bg-white/15 hover:bg-white/25 text-on-primary px-5 py-2.5 rounded-xl font-label-md text-label-md transition flex items-center gap-2 w-fit">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Pendaftaran Saya
                    </a>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex items-center gap-4 flex-shrink-0">
            <img src="{{ $user->peserta?->foto
                ? asset('storage/' . $user->peserta->foto)
                : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=80&background=00843d&color=fff&bold=true&rounded=true' }}"
                 alt="Foto {{ $user->name }}"
                 class="w-16 h-16 rounded-2xl object-cover ring-4 ring-white/30 shadow">
            <div>
                <div class="text-headline-sm font-bold text-on-primary">{{ $user->name }}</div>
                <span class="inline-flex items-center gap-1.5 mt-2 bg-white/15 rounded-full px-3 py-1 text-body-sm text-on-primary">
                    <span class="w-2 h-2 rounded-full bg-tertiary-fixed-dim flex-shrink-0"></span>
                    Akun Aktif
                </span>
            </div>
        </div>
    </div>

    {{-- Flash message --}}
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-body-md">
            <span class="material-symbols-outlined text-[20px] text-red-500 flex-shrink-0">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Profil Warning --}}
    @if(!$profilCukup)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-start gap-5">
            <div class="w-14 h-14 rounded-2xl bg-amber-400 flex items-center justify-center flex-shrink-0 shadow">
                <span class="material-symbols-outlined text-[28px] text-white" style="font-variation-settings:'FILL' 1">shield_person</span>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-headline-sm font-bold text-amber-900 mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-amber-600">warning</span>
                    Lengkapi Data Profil Sebelum Mendaftar
                </h2>
                <p class="text-body-md text-amber-800 mb-3">
                    Sistem membutuhkan data profil yang lengkap. Harap lengkapi data berikut terlebih dahulu:
                </p>
                <ul class="flex flex-col gap-1.5 mb-4">
                    @foreach($kekurangan as $item)
                        <li class="flex items-center gap-2 text-body-sm text-amber-800">
                            <span class="material-symbols-outlined text-[16px] text-red-500">cancel</span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('ppdb.profil.index') }}"
                   class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-label-md text-label-md transition-colors shadow">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                    Lengkapi Profil Sekarang
                </a>
            </div>
        </div>
    @endif

    {{-- Empty state --}}
    @if(empty($jalurList))
        <div class="flex justify-center py-10">
            <div class="bg-surface-container-lowest rounded-2xl soft-shadow border-t-[3px] border-tertiary-fixed-dim p-10 flex flex-col items-center text-center max-w-md w-full">
                <div class="w-24 h-24 bg-primary/8 rounded-full flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-[48px] text-primary" style="font-variation-settings:'FILL' 1">assignment</span>
                </div>
                <h2 class="text-headline-md font-bold text-on-surface mb-2">Belum Ada Jalur Aktif</h2>
                <p class="text-body-md text-on-surface-variant mb-6">Saat ini belum ada jalur pendaftaran yang terbuka. Pantau terus informasi pembukaan PPDB.</p>
                <a href="{{ route('ppdb.dashboard') }}"
                   class="bg-gradient-to-r from-primary to-primary-container text-on-primary px-8 py-3 rounded-xl font-label-md text-label-md shadow flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">home</span>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

    @else
        {{-- Section heading --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-headline-md font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[24px]">grid_view</span>
                Pilih Jalur yang Sesuai
                <span class="bg-secondary-container text-on-secondary-container text-label-sm font-semibold px-3 py-1 rounded-full">{{ count($jalurList) }} Jalur</span>
            </h2>
            @if(!$profilCukup)
                <div class="flex items-center gap-2 text-body-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-4 py-2">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    Lengkapi profil untuk dapat mendaftar
                </div>
            @endif
        </div>

        {{-- Jalur Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($jalurList as $idx => $item)
            @php
                $jalurItem    = $item['jalur'];
                $pembukaan    = $item['pembukaan'];
                $tahun        = $item['tahun_pelajaran'];
                $jadwalList   = $item['jadwal'] ?? collect();
                $syaratList   = $item['syarat'] ?? collect();
                $kuotaJurusan = $item['kuota_jurusan'] ?? collect();
                $biayaList    = $item['biaya'] ?? collect();
                $kuotaTersedia = $item['kuota_tersedia'] ?? 0;
                $sudahDaftar  = $item['sudah_daftar'] ?? false;
                $bisaDaftar   = $item['bisa_daftar'] ?? false;
                $jadwalAktif  = $item['jadwal_aktif'] ?? null;
                $pendAktif    = $item['pendaftaran_aktif'] ?? null;

                $totalKuota   = $kuotaJurusan->sum('kuota');
                $totalTerisi  = $kuotaJurusan->sum('terisi');
                $pctTerisi    = $totalKuota > 0 ? round(($totalTerisi / $totalKuota) * 100) : 0;
                $hampirPenuh  = $kuotaTersedia > 0 && $kuotaTersedia < 10;

                $biayaUtama   = $biayaList->first();
                $biayaNominal = $biayaUtama?->nominal ?? 0;

                $color    = $cardColors[$idx % count($cardColors)];
                $canSelect = $bisaDaftar && $profilCukup;
                $isDisabled = !$bisaDaftar || !$profilCukup;
                $accId = 'acc-j' . $jalurItem->id;
            @endphp

            <article class="bg-surface-container-lowest rounded-2xl overflow-hidden soft-shadow flex flex-col {{ $isDisabled && !$sudahDaftar ? 'opacity-80' : '' }}">

                {{-- Card Header --}}
                <div class="p-5 relative overflow-hidden" style="background: {{ $color['bg'] }}">
                    <div class="absolute inset-0 opacity-[0.08]"
                         style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Ccircle cx='20' cy='20' r='16' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>
                    <div class="relative z-10">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <span class="inline-block bg-white/25 text-white text-label-sm font-bold px-3 py-1 rounded-full">{{ $jalurItem->kode_jalur }}</span>
                            {{-- Status chip --}}
                            @if($sudahDaftar)
                                <span class="inline-flex items-center gap-1 bg-white text-green-700 text-label-sm font-bold px-3 py-1 rounded-full shadow-sm">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>Sudah Daftar
                                </span>
                            @elseif($kuotaTersedia <= 0)
                                <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-label-sm font-bold px-3 py-1 rounded-full">
                                    <span class="material-symbols-outlined text-[14px]">cancel</span>Kuota Penuh
                                </span>
                            @elseif(!$jadwalAktif)
                                <span class="inline-flex items-center gap-1 bg-white/25 text-white text-label-sm font-bold px-3 py-1 rounded-full">
                                    <span class="material-symbols-outlined text-[14px]">schedule</span>Di Luar Jadwal
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-white/25 text-white text-label-sm font-bold px-3 py-1 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-green-300 animate-pulse"></span>Tersedia
                                </span>
                            @endif
                        </div>
                        <h3 class="text-headline-sm font-bold text-white mb-1">{{ $jalurItem->nama }}</h3>
                        <div class="flex items-center gap-1.5 text-white/80 text-body-sm">
                            <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                            {{ $tahun?->nama ?? $pembukaan?->nama }}
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col gap-4">

                    {{-- Deskripsi --}}
                    @if($jalurItem->deskripsi)
                        <p class="text-body-sm text-on-surface-variant leading-relaxed">{{ Str::limit($jalurItem->deskripsi, 120) }}</p>
                    @endif

                    {{-- Info Grid --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-surface-container-low rounded-xl p-3 flex flex-col gap-1 text-center">
                            <span class="text-body-xs text-on-surface-variant">Total Kuota</span>
                            <span class="text-label-md font-bold text-on-surface">{{ number_format($totalKuota) }}</span>
                            <span class="text-body-xs text-on-surface-variant">siswa</span>
                        </div>
                        <div class="bg-surface-container-low rounded-xl p-3 flex flex-col gap-1 text-center">
                            <span class="text-body-xs text-on-surface-variant">Sisa</span>
                            <span class="text-label-md font-bold {{ $kuotaTersedia <= 0 ? 'text-red-600' : ($hampirPenuh ? 'text-amber-600' : 'text-green-700') }}">{{ number_format($kuotaTersedia) }}</span>
                            @if($hampirPenuh && $kuotaTersedia > 0)
                                <span class="text-[10px] text-amber-600 font-semibold">Hampir penuh!</span>
                            @else
                                <span class="text-body-xs text-on-surface-variant">tersedia</span>
                            @endif
                        </div>
                        <div class="bg-surface-container-low rounded-xl p-3 flex flex-col gap-1 text-center">
                            <span class="text-body-xs text-on-surface-variant">Biaya</span>
                            @if($biayaNominal > 0)
                                <span class="text-label-sm font-bold text-primary">{{ $fmtRupiah($biayaNominal) }}</span>
                            @else
                                <span class="text-label-md font-bold text-green-700">Gratis</span>
                            @endif
                        </div>
                    </div>

                    {{-- Progress Kuota --}}
                    <div>
                        <div class="flex justify-between text-body-xs text-on-surface-variant mb-1.5">
                            <span>Kuota Terisi</span>
                            <span class="font-semibold">{{ $pctTerisi }}%</span>
                        </div>
                        <div class="h-2 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 style="width:{{ $pctTerisi }}%; background:{{ $color['bg'] }};"></div>
                        </div>
                    </div>

                    {{-- Custom Accordion --}}
                    <div class="flex flex-col gap-1 border border-outline-variant rounded-xl overflow-hidden" id="{{ $accId }}">

                        {{-- Kuota Jurusan --}}
                        @if($kuotaJurusan->isNotEmpty())
                        <div class="{{ !$syaratList->isEmpty() || !$jadwalList->isEmpty() || !$biayaList->isEmpty() ? 'border-b border-outline-variant' : '' }}">
                            <button type="button"
                                    class="jalur-acc-toggle w-full flex items-center justify-between px-4 py-3 hover:bg-surface-container transition-colors text-left"
                                    data-acc-target="kuota-{{ $jalurItem->id }}" aria-expanded="false">
                                <span class="flex items-center gap-2 text-label-md font-semibold text-on-surface">
                                    <span class="material-symbols-outlined text-primary text-[18px]">account_tree</span>
                                    Kuota Per Jurusan
                                </span>
                                <span class="material-symbols-outlined text-outline text-[20px] jalur-acc-chevron transition-transform duration-200">expand_more</span>
                            </button>
                            <div id="kuota-{{ $jalurItem->id }}" class="hidden px-4 pb-4">
                                <div class="flex flex-col gap-2">
                                    @foreach($kuotaJurusan as $kj)
                                    <div class="flex items-center justify-between text-body-sm">
                                        <span class="text-on-surface">{{ $kj->jurusan?->nama ?? '—' }}</span>
                                        <span class="text-on-surface-variant">Sisa {{ number_format($kj->kuota - $kj->terisi) }} / {{ number_format($kj->kuota) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Syarat --}}
                        @if($syaratList->isNotEmpty())
                        <div class="{{ !$jadwalList->isEmpty() || !$biayaList->isEmpty() ? 'border-b border-outline-variant' : '' }}">
                            <button type="button"
                                    class="jalur-acc-toggle w-full flex items-center justify-between px-4 py-3 hover:bg-surface-container transition-colors text-left"
                                    data-acc-target="syarat-{{ $jalurItem->id }}" aria-expanded="false">
                                <span class="flex items-center gap-2 text-label-md font-semibold text-on-surface">
                                    <span class="material-symbols-outlined text-primary text-[18px]">task_alt</span>
                                    Syarat ({{ $syaratList->count() }})
                                </span>
                                <span class="material-symbols-outlined text-outline text-[20px] jalur-acc-chevron transition-transform duration-200">expand_more</span>
                            </button>
                            <div id="syarat-{{ $jalurItem->id }}" class="hidden px-4 pb-4">
                                <div class="flex flex-col gap-2">
                                    @foreach($syaratList as $srt)
                                    <div class="flex items-center gap-2 text-body-sm">
                                        <span class="material-symbols-outlined text-green-600 text-[16px] flex-shrink-0">check_circle</span>
                                        <span class="flex-1 text-on-surface">{{ $srt->nama }}</span>
                                        @if($srt->wajib)
                                            <span class="text-[10px] bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full font-semibold">Wajib</span>
                                        @else
                                            <span class="text-[10px] bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full">Opsional</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Jadwal --}}
                        @if($jadwalList->isNotEmpty())
                        <div class="{{ !$biayaList->isEmpty() ? 'border-b border-outline-variant' : '' }}">
                            <button type="button"
                                    class="jalur-acc-toggle w-full flex items-center justify-between px-4 py-3 hover:bg-surface-container transition-colors text-left"
                                    data-acc-target="jadwal-{{ $jalurItem->id }}" aria-expanded="false">
                                <span class="flex items-center gap-2 text-label-md font-semibold text-on-surface">
                                    <span class="material-symbols-outlined text-primary text-[18px]">date_range</span>
                                    Jadwal Pelaksanaan
                                </span>
                                <span class="material-symbols-outlined text-outline text-[20px] jalur-acc-chevron transition-transform duration-200">expand_more</span>
                            </button>
                            <div id="jadwal-{{ $jalurItem->id }}" class="hidden px-4 pb-4">
                                <div class="flex flex-col gap-2">
                                    @foreach($jadwalList as $jdw)
                                    @php $isAktif = $jadwalAktif && $jadwalAktif->id == $jdw->id; @endphp
                                    <div class="flex items-center gap-2 text-body-sm {{ $isAktif ? 'text-green-700 font-semibold' : 'text-on-surface' }}">
                                        <span class="bg-surface-container-high text-on-surface-variant text-[11px] font-semibold px-2 py-0.5 rounded">{{ ucfirst($jdw->tipe) }}</span>
                                        <span class="flex-1">{{ $fmtDate($jdw->mulai) }} — {{ $fmtDate($jdw->selesai) }}</span>
                                        @if($isAktif)
                                            <span class="text-[10px] bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded-full font-semibold">Aktif</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Biaya --}}
                        @if($biayaList->isNotEmpty())
                        <div>
                            <button type="button"
                                    class="jalur-acc-toggle w-full flex items-center justify-between px-4 py-3 hover:bg-surface-container transition-colors text-left"
                                    data-acc-target="biaya-{{ $jalurItem->id }}" aria-expanded="false">
                                <span class="flex items-center gap-2 text-label-md font-semibold text-on-surface">
                                    <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
                                    Rincian Biaya
                                </span>
                                <span class="material-symbols-outlined text-outline text-[20px] jalur-acc-chevron transition-transform duration-200">expand_more</span>
                            </button>
                            <div id="biaya-{{ $jalurItem->id }}" class="hidden px-4 pb-4">
                                <div class="flex flex-col gap-2">
                                    @foreach($biayaList as $by)
                                    <div class="flex items-center gap-2 text-body-sm">
                                        <span class="material-symbols-outlined text-primary text-[16px]">attach_money</span>
                                        <span class="flex-1 text-on-surface">{{ $by->nama }}</span>
                                        <span class="font-bold text-primary">{{ $fmtRupiah($by->nominal) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                </div>

                {{-- Card Footer --}}
                <div class="px-5 py-4 bg-surface-container-low border-t border-outline-variant">
                    @if($sudahDaftar)
                        <a href="{{ route('ppdb.pendaftaran.show', $pendAktif?->id) }}"
                           class="w-full border border-outline text-on-surface-variant text-label-md font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                            Lihat Pendaftaran
                        </a>
                    @elseif($canSelect)
                        <button type="button"
                                class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all"
                                onclick="bukaModalKonfirmasi(this)"
                                data-jalur-id="{{ $jalurItem->id }}"
                                data-jalur-nama="{{ $jalurItem->nama }}"
                                data-biaya="{{ $biayaNominal > 0 ? $fmtRupiah($biayaNominal) : 'Gratis' }}"
                                data-syarat-count="{{ $syaratList->count() }}">
                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                            Pilih Jalur Ini
                        </button>
                    @else
                        <button type="button" disabled
                                class="w-full bg-surface-container-high text-on-surface-variant text-label-md font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 cursor-not-allowed opacity-70">
                            @if(!$profilCukup)
                                <span class="material-symbols-outlined text-[18px]">lock</span>
                                Lengkapi Profil Dulu
                            @elseif($kuotaTersedia <= 0)
                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                Kuota Penuh
                            @else
                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                                Di Luar Jadwal
                            @endif
                        </button>
                    @endif
                </div>

            </article>
            @endforeach
        </div>

    @endif

</div>

{{-- ══ MODAL KONFIRMASI (Custom Tailwind) ══ --}}
<div id="modalKonfirmasiJalur"
     class="fixed inset-0 z-[9000] hidden items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="modal-konfirmasi-title">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="tutupModalKonfirmasi()"></div>

    {{-- Modal Card --}}
    <div class="relative z-10 bg-surface-container-lowest rounded-2xl soft-shadow overflow-hidden w-full max-w-md">

        {{-- Header --}}
        <div class="flex items-start gap-4 p-6 bg-green-50 border-b-2 border-green-200">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center flex-shrink-0 shadow">
                <span class="material-symbols-outlined text-[24px] text-on-primary" style="font-variation-settings:'FILL' 1">check_circle</span>
            </div>
            <div class="flex-1 min-w-0">
                <h5 id="modal-konfirmasi-title" class="text-headline-sm font-bold text-on-surface">Konfirmasi Pilihan Jalur</h5>
                <p class="text-body-sm text-on-surface-variant mt-0.5">Pastikan pilihan Anda sudah sesuai</p>
            </div>
            <button type="button" onclick="tutupModalKonfirmasi()"
                    class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 flex flex-col gap-4">

            {{-- Info Jalur --}}
            <div class="bg-surface-container-low border border-outline-variant rounded-xl p-4 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wide">Jalur</span>
                    <span id="modal-jalur-nama" class="text-label-md font-bold text-on-surface text-right">—</span>
                </div>
                <div class="h-px bg-outline-variant"></div>
                <div class="flex items-center justify-between">
                    <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wide">Biaya</span>
                    <span id="modal-biaya" class="text-label-md font-bold text-primary text-right">—</span>
                </div>
                <div class="h-px bg-outline-variant"></div>
                <div class="flex items-center justify-between">
                    <span class="text-label-sm font-semibold text-on-surface-variant uppercase tracking-wide">Syarat</span>
                    <span id="modal-syarat-count" class="text-label-md font-bold text-on-surface text-right">—</span>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                <span class="material-symbols-outlined text-blue-600 text-[20px] flex-shrink-0 mt-0.5">info</span>
                <p class="text-body-sm text-blue-800">
                    Setelah memilih jalur ini, Anda akan diarahkan untuk melengkapi formulir pendaftaran. Pastikan data yang Anda isi sudah benar.
                </p>
            </div>

            {{-- Checkbox Konfirmasi --}}
            <label class="flex items-start gap-3 bg-surface-container-low border-2 border-outline-variant rounded-xl p-4 cursor-pointer hover:border-primary transition-colors">
                <input type="checkbox" id="checkKonfirmasi" onchange="toggleSubmitBtn(this)"
                       class="w-5 h-5 mt-0.5 rounded accent-primary flex-shrink-0">
                <span class="text-body-md text-on-surface">
                    Saya sudah yakin dengan pilihan jalur ini dan siap melanjutkan
                </span>
            </label>

        </div>

        {{-- Footer --}}
        <div class="flex gap-3 px-6 py-4 bg-surface-container-low border-t border-outline-variant">
            <button type="button" onclick="tutupModalKonfirmasi()"
                    class="flex-1 border border-outline text-on-surface-variant text-label-md font-semibold py-3 px-4 rounded-xl hover:bg-surface-container transition-colors">
                Batal
            </button>
            <form action="{{ route('ppdb.pendaftaran.store') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="jalur_pendaftaran_id" id="hidden-jalur-id" value="">
                <button type="submit" id="btnSubmitKonfirmasi" disabled
                        class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                    Ya, Lanjutkan
                </button>
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ── Accordion --
    document.querySelectorAll('.jalur-acc-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.accTarget;
            const body     = document.getElementById(targetId);
            const chevron  = this.querySelector('.jalur-acc-chevron');
            if (!body) return;
            const isOpen = !body.classList.contains('hidden');

            // Close all within same card
            const card = this.closest('[id^="acc-j"]') || this.closest('article');
            if (card) {
                card.querySelectorAll('[id^="kuota-"],[id^="syarat-"],[id^="jadwal-"],[id^="biaya-"]').forEach(b => b.classList.add('hidden'));
                card.querySelectorAll('.jalur-acc-chevron').forEach(c => c.classList.remove('rotate-180'));
                card.querySelectorAll('.jalur-acc-toggle').forEach(b => b.setAttribute('aria-expanded', 'false'));
            }

            if (!isOpen) {
                body.classList.remove('hidden');
                chevron.classList.add('rotate-180');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // ── Modal --
    window.bukaModalKonfirmasi = function (btn) {
        document.getElementById('modal-jalur-nama').textContent    = btn.dataset.jalurNama;
        document.getElementById('modal-biaya').textContent          = btn.dataset.biaya;
        document.getElementById('modal-syarat-count').textContent   = btn.dataset.syaratCount + ' dokumen';
        document.getElementById('hidden-jalur-id').value            = btn.dataset.jalurId;
        const cb = document.getElementById('checkKonfirmasi');
        cb.checked = false;
        document.getElementById('btnSubmitKonfirmasi').disabled = true;
        const modal = document.getElementById('modalKonfirmasiJalur');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.tutupModalKonfirmasi = function () {
        const modal = document.getElementById('modalKonfirmasiJalur');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    window.toggleSubmitBtn = function (checkbox) {
        document.getElementById('btnSubmitKonfirmasi').disabled = !checkbox.checked;
    };

    // Animate kuota progress bars on load
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-jalur-id]').forEach(el => {
            const bar = el.closest('article')?.querySelector('.h-2 div');
            if (bar) {
                const w = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => { bar.style.width = w; }, 300);
            }
        });
    });

    // Close modal on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') tutupModalKonfirmasi();
    });
})();
</script>
@endpush
