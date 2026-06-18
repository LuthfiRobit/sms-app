@extends('layouts.portal-stitch')

@section('title', 'Beranda PPDB 2026/2027')

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
               class="font-label-md text-label-md text-on-primary border-b-2 border-tertiary-fixed-dim pb-1">Beranda</a>
            @auth
            <a href="{{ route('ppdb.pendaftaran.index') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Pendaftaran</a>
            <a href="{{ route('ppdb.profil.index') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Profil</a>
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('ppdb.dashboard') }}"
                   class="bg-surface text-primary px-4 md:px-6 py-2 rounded-lg font-label-md text-label-md shadow-sm hover:bg-surface-container-low transition-colors duration-200 flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">dashboard</span>
                    <span class="hidden sm:inline">Dashboard</span>
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
            @else
                <a href="{{ route('ppdb.login') }}"
                   class="bg-white/10 hover:bg-white/20 text-on-primary px-4 py-2 rounded-lg font-label-md text-label-md transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    <span class="hidden sm:inline">Masuk</span>
                </a>
                <a href="{{ route('ppdb.register') }}"
                   class="bg-tertiary-fixed-dim text-on-surface px-4 md:px-5 py-2 rounded-lg font-label-md text-label-md shadow-sm hover:bg-tertiary-fixed transition-colors duration-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    <span class="hidden sm:inline">Daftar</span>
                </a>
            @endauth
        </div>
    </div>
</nav>
@endsection

@section('content')

{{-- ── HERO ──────────────────────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden">
    <div class="bg-gradient-to-br from-primary via-primary-container to-[#004d24] min-h-[420px] md:min-h-[460px] flex items-center">
        {{-- Decorative pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

        <div class="relative w-full max-w-[1440px] mx-auto px-4 md:px-8 py-12 flex flex-col lg:flex-row items-center gap-10">

            {{-- Left: Content --}}
            <div class="flex-1 text-center lg:text-left">
                {{-- Status Badge --}}
                @if($pembukaan)
                    <div class="inline-flex items-center gap-2 bg-white/15 text-on-primary text-body-sm font-semibold px-4 py-1.5 rounded-full mb-5 backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-secondary-fixed animate-pulse"></span>
                        Pendaftaran Dibuka · {{ $pembukaan->tahunPelajaran?->nama ?? 'TA 2026/2027' }}
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 bg-white/10 text-on-primary/70 text-body-sm font-semibold px-4 py-1.5 rounded-full mb-5">
                        <span class="w-2 h-2 rounded-full bg-white/40"></span>
                        Pendaftaran Belum Dibuka
                    </div>
                @endif

                {{-- Heading --}}
                <h1 class="text-on-primary font-extrabold mb-4 leading-tight"
                    style="font-size:clamp(28px,5vw,48px);line-height:1.15;letter-spacing:-0.02em">
                    Selamat Datang di<br>
                    <span style="color:#f6bd50">Portal PPDB</span><br>
                    LP Ma'arif NU Kraksaan
                </h1>

                <p class="text-on-primary/75 text-body-lg mb-6 max-w-lg mx-auto lg:mx-0">
                    Proses penerimaan peserta didik baru yang mudah, cepat, dan transparan.
                    @if($jadwal_terdekat)
                        <br><span class="font-semibold text-tertiary-fixed">Tutup {{ $jadwal_terdekat->selesai?->format('d F Y') }}.</span>
                    @endif
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                    @auth
                        <a href="{{ route('ppdb.dashboard') }}"
                           class="inline-flex items-center gap-2 bg-tertiary-fixed-dim text-on-surface font-semibold px-6 py-3 rounded-xl shadow-md hover:bg-tertiary-fixed hover:-translate-y-0.5 transition-all duration-200">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">dashboard</span>
                            Dashboard Saya
                        </a>
                        <a href="{{ route('ppdb.pendaftaran.index') }}"
                           class="inline-flex items-center gap-2 bg-white/15 text-on-primary font-semibold px-6 py-3 rounded-xl hover:bg-white/25 transition-all duration-200 backdrop-blur-sm">
                            <span class="material-symbols-outlined text-[20px]">edit_document</span>
                            Mulai Daftar
                        </a>
                    @else
                        <a href="{{ route('ppdb.register') }}"
                           class="inline-flex items-center gap-2 bg-tertiary-fixed-dim text-on-surface font-semibold px-6 py-3 rounded-xl shadow-md hover:bg-tertiary-fixed hover:-translate-y-0.5 transition-all duration-200">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">person_add</span>
                            Daftar Sekarang
                        </a>
                        <a href="{{ route('ppdb.login') }}"
                           class="inline-flex items-center gap-2 bg-white/15 text-on-primary font-semibold px-6 py-3 rounded-xl hover:bg-white/25 transition-all duration-200 backdrop-blur-sm">
                            <span class="material-symbols-outlined text-[20px]">login</span>
                            Sudah Punya Akun
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Right: SVG Illustration --}}
            <div class="hidden lg:flex flex-shrink-0 items-center justify-center w-72 xl:w-80">
                <svg width="280" height="260" viewBox="0 0 360 300" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <ellipse cx="180" cy="130" rx="100" ry="18" fill="#dcfce7" opacity="0.7"/>
                    <polygon points="180,50 280,130 180,148 80,130" fill="rgba(255,255,255,0.25)"/>
                    <polygon points="180,50 280,130 180,120 80,130" fill="rgba(255,255,255,0.15)"/>
                    <line x1="280" y1="130" x2="280" y2="180" stroke="rgba(255,255,255,0.5)" stroke-width="3"/>
                    <circle cx="280" cy="183" r="6" fill="#f6bd50"/>
                    <rect x="60" y="140" width="60" height="80" rx="6" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
                    <line x1="72" y1="158" x2="108" y2="158" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <line x1="72" y1="168" x2="108" y2="168" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <line x1="72" y1="178" x2="95" y2="178" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <rect x="240" y="155" width="60" height="80" rx="6" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.3)" stroke-width="1.5"/>
                    <line x1="252" y1="173" x2="288" y2="173" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <line x1="252" y1="183" x2="288" y2="183" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <line x1="252" y1="193" x2="275" y2="193" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <circle cx="305" cy="90" r="28" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
                    <polyline points="295,90 302,97 315,83" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="48" y="118" font-size="18" fill="#f6bd50" opacity="0.8">★</text>
                    <text x="300" y="148" font-size="14" fill="#f6bd50" opacity="0.7">★</text>
                    <text x="155" y="270" font-size="12" fill="rgba(255,255,255,0.4)">★</text>
                </svg>
            </div>

        </div>
    </div>

    {{-- Wave divider --}}
    <div class="h-8 bg-gradient-to-b from-[#004d24] to-background"></div>
</div>

{{-- ── STATUS CARDS ─────────────────────────────────────────────────────────── --}}
<section class="w-full max-w-[1440px] mx-auto px-4 md:px-8 -mt-6 pb-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Card 1: Status PPDB --}}
        <div class="bg-surface-container-lowest rounded-2xl border-t-4 border-primary soft-shadow p-5 flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-[22px]" style="font-variation-settings:'FILL' 1">calendar_month</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-body-sm text-on-surface-variant mb-1">Status PPDB</p>
                @if($pembukaan)
                    <p class="text-headline-sm text-primary font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-secondary-fixed inline-block"></span>
                        Dibuka
                    </p>
                    <p class="text-body-sm text-on-surface-variant mt-0.5 truncate">{{ $pembukaan->nama }}</p>
                @else
                    <p class="text-headline-sm text-on-surface-variant font-bold">Belum Dibuka</p>
                    <p class="text-body-sm text-on-surface-variant mt-0.5">Pantau terus portal ini</p>
                @endif
            </div>
        </div>

        {{-- Card 2: Periode Pendaftaran --}}
        <div class="bg-surface-container-lowest rounded-2xl border-t-4 border-tertiary soft-shadow p-5 flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-tertiary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-tertiary text-[22px]" style="font-variation-settings:'FILL' 1">schedule</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-body-sm text-on-surface-variant mb-1">Periode Pendaftaran</p>
                @if($pembukaan)
                    <p class="text-headline-sm text-on-surface font-bold">
                        {{ $pembukaan->mulai?->format('d M') }} – {{ $pembukaan->selesai?->format('d M Y') }}
                    </p>
                    @php $sisaHari = (int) now()->diffInDays($pembukaan->selesai, false); @endphp
                    <p class="text-body-sm mt-0.5 {{ $sisaHari <= 3 && $sisaHari >= 0 ? 'text-error font-semibold' : 'text-on-surface-variant' }}">
                        @if($sisaHari > 0) Sisa {{ $sisaHari }} hari
                        @elseif($sisaHari == 0) Hari terakhir!
                        @else Sudah ditutup
                        @endif
                    </p>
                @else
                    <p class="text-headline-sm text-on-surface font-bold">—</p>
                    <p class="text-body-sm text-on-surface-variant mt-0.5">Belum ada jadwal</p>
                @endif
            </div>
        </div>

        {{-- Card 3: Jalur Tersedia --}}
        <div class="bg-surface-container-lowest rounded-2xl border-t-4 border-secondary soft-shadow p-5 flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-secondary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-secondary text-[22px]" style="font-variation-settings:'FILL' 1">account_tree</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-body-sm text-on-surface-variant mb-1">Jalur Tersedia</p>
                <p class="text-headline-sm text-on-surface font-bold">
                    {{ $pembukaan?->jalurPendaftaran->count() ?? 0 }} Jalur
                </p>
                <p class="text-body-sm text-on-surface-variant mt-0.5 truncate">
                    @if($pembukaan && $pembukaan->jalurPendaftaran->isNotEmpty())
                        {{ $pembukaan->jalurPendaftaran->pluck('nama')->implode(', ') }}
                    @else
                        Belum ada jalur tersedia
                    @endif
                </p>
            </div>
        </div>

    </div>
</section>

{{-- ── JALUR PENDAFTARAN ────────────────────────────────────────────────────── --}}
@if($pembukaan && $pembukaan->jalurPendaftaran->isNotEmpty())
<section class="w-full max-w-[1440px] mx-auto px-4 md:px-8 py-8">
    {{-- Section Header --}}
    <div class="text-center mb-8">
        <span class="inline-block text-label-md text-primary bg-primary/8 border border-primary/20 px-4 py-1.5 rounded-full mb-3">Jalur Penerimaan</span>
        <h2 class="text-headline-lg text-on-surface font-bold mb-2">Pilih Jalur Pendaftaran</h2>
        <p class="text-body-lg text-on-surface-variant">Pilih jalur yang sesuai dengan prestasi dan kondisi Anda</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($pembukaan->jalurPendaftaran as $jalur)
        <article class="bg-surface-container-lowest rounded-2xl border border-outline-variant soft-shadow overflow-hidden hover:-translate-y-1 hover:shadow-lg transition-all duration-200 group">
            <div class="bg-gradient-to-r from-primary to-primary-container p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-primary text-[20px]" style="font-variation-settings:'FILL' 1">verified</span>
                    </div>
                    <span class="text-on-primary font-bold text-body-sm tracking-widest uppercase">{{ $jalur->kode_jalur ?? '--' }}</span>
                </div>
                <span class="bg-secondary-fixed text-on-surface text-[11px] font-bold px-2.5 py-1 rounded-full">Aktif</span>
            </div>
            <div class="p-5">
                <h3 class="text-headline-sm text-on-surface font-bold mb-2">{{ $jalur->nama }}</h3>
                @if($jalur->deskripsi)
                    <p class="text-body-sm text-on-surface-variant mb-4 leading-relaxed">{{ Str::limit($jalur->deskripsi, 110) }}</p>
                @endif
                <div class="space-y-2">
                    @if($jalur->kuota)
                    <div class="flex items-center gap-2 text-body-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[16px] text-outline">group</span>
                        <span>Kuota: <strong class="text-on-surface">{{ $jalur->kuota }} peserta</strong></span>
                    </div>
                    @endif
                    @if($jalur->tanggal_mulai && $jalur->tanggal_selesai)
                    <div class="flex items-center gap-2 text-body-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[16px] text-outline">date_range</span>
                        <span>{{ $jalur->tanggal_mulai->format('d M') }} – {{ $jalur->tanggal_selesai->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($jalur->biayaRegistrasi && $jalur->biayaRegistrasi->isNotEmpty())
                    <div class="flex items-center gap-2 text-body-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-[16px] text-outline">payments</span>
                        <span>Biaya: <strong class="text-on-surface">Rp {{ number_format($jalur->biayaRegistrasi->first()?->nominal ?? 0, 0, ',', '.') }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif

{{-- ── LANGKAH PENDAFTARAN ──────────────────────────────────────────────────── --}}
<section class="w-full bg-surface-container-low py-12">
    <div class="max-w-[1440px] mx-auto px-4 md:px-8">
        {{-- Section Header --}}
        <div class="text-center mb-8">
            <span class="inline-block text-label-md text-primary bg-primary/8 border border-primary/20 px-4 py-1.5 rounded-full mb-3">Panduan</span>
            <h2 class="text-headline-lg text-on-surface font-bold mb-2">Langkah Pendaftaran</h2>
            <p class="text-body-lg text-on-surface-variant">Ikuti 4 langkah mudah untuk menyelesaikan pendaftaran Anda</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
            $steps = [
                ['num' => '1', 'icon' => 'person_add', 'title' => 'Buat Akun', 'desc' => 'Daftar dengan email dan password. Verifikasi email untuk aktivasi akun.', 'color' => 'primary'],
                ['num' => '2', 'icon' => 'edit_document', 'title' => 'Isi Formulir', 'desc' => 'Lengkapi data diri, data orang tua, dan pilih jalur pendaftaran.', 'color' => 'secondary'],
                ['num' => '3', 'icon' => 'upload_file', 'title' => 'Upload Dokumen', 'desc' => 'Unggah foto, ijazah, rapor, dan dokumen persyaratan lainnya.', 'color' => 'tertiary'],
                ['num' => '4', 'icon' => 'task_alt', 'title' => 'Verifikasi', 'desc' => 'Tunggu verifikasi dari panitia. Pantau status di dashboard.', 'color' => 'secondary'],
            ];
            @endphp
            @foreach($steps as $step)
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-5 relative soft-shadow">
                <div class="absolute -top-3 -left-1 w-8 h-8 rounded-full bg-{{ $step['color'] }} text-on-{{ $step['color'] }} flex items-center justify-center text-body-sm font-extrabold shadow-md">
                    {{ $step['num'] }}
                </div>
                <div class="w-12 h-12 rounded-xl bg-{{ $step['color'] }}/10 flex items-center justify-center mb-4 mt-2">
                    <span class="material-symbols-outlined text-{{ $step['color'] }} text-[26px]" style="font-variation-settings:'FILL' 1">{{ $step['icon'] }}</span>
                </div>
                <h3 class="text-headline-sm text-on-surface font-bold mb-2">{{ $step['title'] }}</h3>
                <p class="text-body-sm text-on-surface-variant leading-relaxed">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── TIMELINE AGENDA ──────────────────────────────────────────────────────── --}}
@if($agenda && $agenda->isNotEmpty())
@php $agendaByJalur = $agenda->groupBy('jalur_pendaftaran_id'); @endphp
<section class="w-full max-w-[1440px] mx-auto px-4 md:px-8 py-12">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Left: Nav --}}
        <div class="lg:w-72 flex-shrink-0">
            <div class="lg:sticky lg:top-24">
                <span class="inline-block text-label-md text-primary bg-primary/8 border border-primary/20 px-4 py-1.5 rounded-full mb-3">Agenda</span>
                <h2 class="text-headline-lg text-on-surface font-bold mb-2">Timeline Kegiatan</h2>
                <p class="text-body-md text-on-surface-variant mb-5">Pilih jalur untuk melihat detail jadwal.</p>

                <nav class="flex flex-col gap-2" role="tablist">
                    @foreach($agendaByJalur as $jalurId => $items)
                    @php
                        $jalur = $items->first()->jalurPendaftaran;
                        $isFirst = $loop->first;
                    @endphp
                    <button class="agenda-nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-left transition-all duration-200 {{ $isFirst ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container' }}"
                            data-target="pane-jalur-{{ $jalurId }}"
                            role="tab"
                            aria-selected="{{ $isFirst ? 'true' : 'false' }}">
                        <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1">label</span>
                        <span class="text-body-sm font-semibold">{{ $jalur->nama }}</span>
                    </button>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Right: Timeline Panes --}}
        <div class="flex-1 min-w-0">
            @foreach($agendaByJalur as $jalurId => $items)
            <div class="agenda-pane {{ $loop->first ? '' : 'hidden' }}" id="pane-jalur-{{ $jalurId }}" role="tabpanel">
                <div class="relative pl-6 border-l-2 border-outline-variant space-y-1">
                    @foreach($items as $item)
                    @php
                        $now      = now();
                        $isAktif  = $item->mulai && $item->selesai && $now->between($item->mulai, $item->selesai);
                        $isSelesai= $item->selesai && $now->isAfter($item->selesai);
                    @endphp
                    <div class="relative pb-6 last:pb-0">
                        {{-- Dot --}}
                        <div class="absolute -left-[25px] w-6 h-6 rounded-full flex items-center justify-center
                            {{ $isAktif ? 'bg-primary' : ($isSelesai ? 'bg-surface-container-high' : 'bg-surface-container-lowest border-2 border-outline-variant') }}">
                            @if($isAktif)
                                <span class="material-symbols-outlined text-on-primary text-[14px]" style="font-variation-settings:'FILL' 1">radio_button_checked</span>
                            @elseif($isSelesai)
                                <span class="material-symbols-outlined text-outline text-[14px]" style="font-variation-settings:'FILL' 1">check_circle</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-outline-variant"></span>
                            @endif
                        </div>

                        {{-- Content Card --}}
                        <div class="ml-2 bg-surface-container-lowest rounded-xl border {{ $isAktif ? 'border-primary shadow-sm' : 'border-outline-variant' }} p-4">
                            <p class="text-body-sm text-on-surface-variant mb-1">
                                @if($item->mulai && $item->selesai)
                                    {{ $item->mulai->format('d M') }} – {{ $item->selesai->format('d M Y') }}
                                @elseif($item->mulai)
                                    {{ $item->mulai->format('d M Y') }}
                                @else
                                    Tanggal belum ditentukan
                                @endif
                            </p>
                            <h3 class="text-headline-sm text-on-surface font-bold mb-1">{{ $item->nama }}</h3>
                            @if($item->keterangan)
                                <p class="text-body-sm text-on-surface-variant">{{ $item->keterangan }}</p>
                            @endif
                            @if($isAktif)
                                <span class="inline-flex items-center gap-1 mt-2 bg-primary text-on-primary text-[11px] font-bold px-3 py-1 rounded-full">
                                    <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1">schedule</span>
                                    Sedang Berlangsung
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- ── CTA BOTTOM ───────────────────────────────────────────────────────────── --}}
<section class="w-full bg-gradient-to-br from-primary to-primary-container py-14 relative overflow-hidden">
    <div class="absolute inset-0 opacity-[0.05]"
         style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>
    <div class="relative max-w-[1440px] mx-auto px-4 md:px-8 text-center">
        <h2 class="text-on-primary font-extrabold mb-3" style="font-size:clamp(22px,4vw,36px)">
            Siap Bergabung dengan Kami?
        </h2>
        <p class="text-on-primary/75 text-body-lg mb-8 max-w-xl mx-auto">
            Jangan sampai kehabisan kuota! Daftar sekarang dan raih kesempatan untuk bergabung dengan lembaga pendidikan terbaik LP Ma'arif NU Kraksaan.
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
            @guest
                <a href="{{ route('ppdb.register') }}"
                   class="inline-flex items-center gap-2 bg-tertiary-fixed-dim text-on-surface font-bold px-8 py-3.5 rounded-xl shadow-lg hover:bg-tertiary-fixed hover:-translate-y-0.5 transition-all duration-200">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">person_add</span>
                    Daftar Sekarang
                </a>
                <a href="{{ route('ppdb.login') }}"
                   class="inline-flex items-center gap-2 bg-white/15 text-on-primary font-semibold px-8 py-3.5 rounded-xl hover:bg-white/25 transition-all duration-200 backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[20px]">login</span>
                    Login
                </a>
            @else
                <a href="{{ route('ppdb.dashboard') }}"
                   class="inline-flex items-center gap-2 bg-tertiary-fixed-dim text-on-surface font-bold px-8 py-3.5 rounded-xl shadow-lg hover:bg-tertiary-fixed hover:-translate-y-0.5 transition-all duration-200">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">dashboard</span>
                    Ke Dashboard
                </a>
            @endguest
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.querySelectorAll('.agenda-nav-item');
    const panes    = document.querySelectorAll('.agenda-pane');

    navItems.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');

            navItems.forEach(function (b) {
                b.classList.remove('bg-primary', 'text-on-primary', 'shadow-sm');
                b.classList.add('bg-surface-container-lowest', 'border', 'border-outline-variant', 'text-on-surface-variant', 'hover:bg-surface-container');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('bg-primary', 'text-on-primary', 'shadow-sm');
            this.classList.remove('bg-surface-container-lowest', 'border', 'border-outline-variant', 'text-on-surface-variant', 'hover:bg-surface-container');
            this.setAttribute('aria-selected', 'true');

            panes.forEach(function (p) { p.classList.add('hidden'); });
            const target = document.getElementById(targetId);
            if (target) target.classList.remove('hidden');
        });
    });
});
</script>
@endpush

@endsection
