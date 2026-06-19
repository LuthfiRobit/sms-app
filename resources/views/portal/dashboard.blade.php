@extends('layouts.portal-stitch')

@section('title', 'Dashboard Peserta')

{{-- ═══════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════ --}}
@section('navbar')
<nav class="bg-gradient-to-r from-primary to-primary-container text-on-primary sticky top-0 border-b-4 border-tertiary-container shadow-md z-50">
    <div class="flex justify-between items-center w-full px-4 md:px-8 h-20 max-w-[1440px] mx-auto">

        {{-- Brand --}}
        <div class="flex items-center gap-4">
            <div class="bg-surface text-primary p-2 rounded-lg font-headline-sm text-headline-sm font-bold shadow-sm select-none">LP</div>
            <span class="font-headline-md text-headline-md text-on-primary hidden sm:block">Marifat</span>
            <span class="text-body-sm font-semibold text-on-primary sm:hidden">Marifat</span>
        </div>

        {{-- Nav Links --}}
        <div class="hidden md:flex gap-8 items-center">
            <a href="{{ route('ppdb.beranda') }}"
               class="font-label-md text-label-md text-on-primary border-b-2 border-tertiary-fixed-dim pb-1">Beranda</a>
            <a href="{{ route('ppdb.pendaftaran.index') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Pendaftaran</a>
            <a href="{{ route('ppdb.profil.index') }}"
               class="font-label-md text-label-md text-on-primary/80 hover:text-tertiary-fixed transition-colors duration-200">Profil</a>
        </div>

        {{-- Right Actions --}}
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

{{-- ═══════════════════════════════════════════
     CONTENT
═══════════════════════════════════════════ --}}
@section('content')
@php
    $user        = auth()->user();
    $firstDaftar = $pendaftaran_list->first();
    $persen      = isset($progress_profil) ? (int) $progress_profil['persen'] : 0;

    $steps = [
        ['label' => 'Buat Akun',       'done' => true],
        ['label' => 'Lengkapi Profil', 'done' => $persen >= 100],
        ['label' => 'Pilih Jalur',     'done' => $pendaftaran_list->isNotEmpty()],
        ['label' => 'Dokumen',         'done' => $firstDaftar && in_array($firstDaftar->status, ['verifikasi','lulus','tidak_lulus','daftar_ulang','siswa_tetap'])],
        ['label' => 'Seleksi',         'done' => $firstDaftar && in_array($firstDaftar->status, ['lulus','tidak_lulus','daftar_ulang','siswa_tetap'])],
    ];
    $doneCount   = collect($steps)->filter(fn($s) => $s['done'])->count();
    $currentStep = $doneCount;

    $missingAspek = [];
    foreach ($progress_profil['aspek'] ?? [] as $name => $done) {
        if (!$done) $missingAspek[] = ucfirst(str_replace('_', ' ', $name));
    }
@endphp

<main class="flex-grow w-full max-w-[1440px] mx-auto px-4 md:px-8 py-stack-lg flex flex-col gap-gutter">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-secondary-container text-on-secondary-container px-4 py-3 rounded-xl border border-secondary/20">
        <span class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
        <span class="text-body-sm">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-error-container text-on-error-container px-4 py-3 rounded-xl border border-error/20">
        <span class="material-symbols-outlined text-[20px] flex-shrink-0">error</span>
        <span class="text-body-sm">{{ session('error') }}</span>
    </div>
    @endif

    {{-- ── Welcome Banner ────────────────────────────────────── --}}
    <section class="bg-surface-container-lowest rounded-2xl p-8 border-t-[3px] border-tertiary soft-shadow relative overflow-hidden">
        {{-- Decorative star --}}
        <div class="absolute top-0 right-0 opacity-10 pointer-events-none text-tertiary select-none" aria-hidden="true">
            <svg width="200" height="200" viewBox="0 0 100 100" fill="currentColor">
                <path d="M50 0L60 40L100 50L60 60L50 100L40 60L0 50L40 40Z"/>
            </svg>
        </div>

        <div class="relative z-10 text-center">
            <div class="text-tertiary text-2xl mb-4 tracking-[0.3em]" aria-hidden="true">✦ &nbsp; ✦ &nbsp; ✦</div>
            <h1 class="font-headline-lg text-headline-lg text-primary mb-2">
                Selamat Datang, <span id="greeting-name">{{ $user->name }}</span>!
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">
                Portal Pendaftaran Peserta Didik Baru (PPDB) Marifat.
                Silakan ikuti langkah-langkah pendaftaran untuk melengkapi profil calon peserta didik.
            </p>
            {{-- Status badge --}}
            <div class="mt-4 flex items-center justify-center gap-2">
                @if($user->status === 'active')
                    <span class="inline-flex items-center gap-1.5 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-body-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1">check_circle</span>
                        Akun Aktif
                    </span>
                @elseif($user->status === 'pending')
                    <span class="inline-flex items-center gap-1.5 bg-tertiary-fixed text-tertiary px-4 py-1.5 rounded-full text-body-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                        Menunggu Verifikasi
                    </span>
                @endif
            </div>
        </div>
    </section>

    {{-- ── Progress Stepper ──────────────────────────────────── --}}
    <section class="bg-surface-container-lowest rounded-2xl p-8 border-t-[3px] border-tertiary soft-shadow">
        <h2 class="font-headline-sm text-headline-sm text-on-surface mb-8">Status Pendaftaran</h2>

        <div class="flex justify-between items-center relative
                    before:absolute before:inset-0 before:top-[20px] before:h-1
                    before:bg-surface-variant before:w-full before:-z-10">
            @foreach($steps as $i => $step)
            @php $isActive = !$step['done'] && ($i === $currentStep); @endphp
            <div class="flex flex-col items-center gap-2 relative bg-surface-container-lowest px-2
                        @if($i >= 3) hidden md:flex @endif">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold
                    @if($step['done']) bg-secondary-container text-primary border-2 border-primary
                    @elseif($isActive) bg-gradient-to-br from-primary to-primary-container text-on-primary shadow-md
                    @else bg-surface-variant text-on-surface-variant @endif">
                    @if($step['done'])
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 1">check</span>
                    @else
                        {{ $i + 1 }}
                    @endif
                </div>
                <span class="font-label-md text-label-md text-center whitespace-nowrap
                    @if($step['done'] || $isActive) text-primary font-bold @else text-on-surface-variant @endif">
                    {{ $step['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── Bento Grid ────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">

        {{-- Kelengkapan Profil --}}
        <section class="bg-surface-container-lowest rounded-2xl p-8 border-t-[3px] border-tertiary soft-shadow flex flex-col">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-6">Kelengkapan Profil</h3>

            {{-- Circular Progress --}}
            <div class="flex items-center gap-6 mb-6">
                <div class="relative w-28 h-28 flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-surface-variant"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="currentColor" stroke-dasharray="100, 100" stroke-width="4"/>
                        <path id="progress-arc"
                              class="{{ $persen >= 100 ? 'text-secondary' : ($persen > 0 ? 'text-tertiary' : 'text-surface-dim') }}"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="currentColor"
                              stroke-dasharray="0, 100"
                              data-target="{{ $persen }}"
                              stroke-width="4"
                              stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                        <span class="font-headline-md text-headline-md {{ $persen >= 100 ? 'text-secondary' : 'text-primary' }}">
                            {{ $persen }}%
                        </span>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-body-sm text-on-surface-variant mb-1">
                        {{ $progress_profil['lengkap'] ?? 0 }} dari {{ $progress_profil['total'] ?? 0 }} aspek terisi
                    </p>
                    @if($persen >= 100)
                    <div class="inline-flex items-center gap-1.5 bg-secondary-container text-on-secondary-container px-3 py-1 rounded-full text-body-sm font-semibold">
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1">check_circle</span>
                        Profil Lengkap!
                    </div>
                    @else
                    <div class="w-full h-2 bg-surface-container-high rounded-full overflow-hidden mt-2">
                        <div class="h-full bg-gradient-to-r from-tertiary to-tertiary-container rounded-full transition-all duration-700"
                             id="profile-progress-bar" style="width:0%" data-target="{{ $persen }}%"></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Missing Items --}}
            @if(!empty($missingAspek))
            <div class="bg-error-container text-on-error-container p-4 rounded-xl flex items-start gap-3 mb-6">
                <span class="material-symbols-outlined text-error flex-shrink-0 mt-0.5">info</span>
                <div class="flex-1 min-w-0">
                    <p class="font-label-md text-label-md font-bold mb-2">Data Belum Lengkap</p>
                    <ul class="list-disc pl-4 font-body-sm text-body-sm space-y-1">
                        @foreach(array_slice($missingAspek, 0, 4) as $item)
                        <li>{{ $item }}</li>
                        @endforeach
                        @if(count($missingAspek) > 4)
                        <li>+{{ count($missingAspek) - 4 }} lainnya</li>
                        @endif
                    </ul>
                </div>
            </div>
            @elseif($persen >= 100)
            <div class="bg-secondary-container text-on-secondary-container p-4 rounded-xl flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined flex-shrink-0" style="font-variation-settings:'FILL' 1">verified</span>
                <p class="font-label-md text-label-md">Semua data profil sudah terisi dengan lengkap.</p>
            </div>
            @endif

            <a href="{{ route('ppdb.profil.index') }}"
               class="mt-auto w-full bg-gradient-to-r from-primary to-primary-container text-on-primary py-3 rounded-xl font-label-md text-label-md font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">{{ $persen >= 100 ? 'person_circle' : 'edit_note' }}</span>
                {{ $persen >= 100 ? 'Lihat Profil' : 'Lengkapi Sekarang' }}
            </a>
        </section>

        {{-- Informasi PPDB --}}
        <section class="bg-surface-container-lowest rounded-2xl p-8 border-t-[3px] border-tertiary soft-shadow flex flex-col">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-6">
                Informasi PPDB {{ date('Y') }}
            </h3>

            <div class="space-y-5 flex-grow">
                {{-- Pembukaan --}}
                @if($pembukaan)
                <div class="flex items-start gap-4">
                    <div class="bg-surface-container p-3 rounded-xl text-primary flex-shrink-0">
                        <span class="material-symbols-outlined">calendar_month</span>
                    </div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface-variant">Status Pendaftaran</p>
                        <p class="font-headline-sm text-headline-sm text-primary mt-0.5">{{ $pembukaan->nama }}</p>
                        @if($pembukaan->mulai && $pembukaan->selesai)
                        <p class="text-body-sm text-on-surface-variant mt-1">
                            {{ $pembukaan->mulai->format('d M Y') }} — {{ $pembukaan->selesai->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>
                @else
                <div class="flex items-start gap-4">
                    <div class="bg-surface-container p-3 rounded-xl text-on-surface-variant flex-shrink-0">
                        <span class="material-symbols-outlined">calendar_month</span>
                    </div>
                    <div>
                        <p class="font-label-md text-label-md text-on-surface-variant">Status Pendaftaran</p>
                        <p class="font-headline-sm text-headline-sm text-on-surface-variant mt-0.5">Belum Dibuka</p>
                        <p class="text-body-sm text-on-surface-variant mt-1">Pantau terus untuk info jadwal PPDB.</p>
                    </div>
                </div>
                @endif

                <div class="h-px bg-outline-variant w-full"></div>

                {{-- Pendaftaran cepat --}}
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant mb-3">Aksi Cepat</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('ppdb.pendaftaran.index') }}"
                           class="flex items-center gap-2 bg-surface-container-low hover:bg-surface-container transition-colors px-3 py-2.5 rounded-xl text-body-sm font-semibold text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[18px]">description</span>
                            Pendaftaran
                        </a>
                        <a href="{{ route('ppdb.profil.index') }}"
                           class="flex items-center gap-2 bg-surface-container-low hover:bg-surface-container transition-colors px-3 py-2.5 rounded-xl text-body-sm font-semibold text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[18px]">person</span>
                            Profil Saya
                        </a>
                        <a href="{{ route('ppdb.beranda') }}"
                           class="flex items-center gap-2 bg-surface-container-low hover:bg-surface-container transition-colors px-3 py-2.5 rounded-xl text-body-sm font-semibold text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[18px]">home</span>
                            Beranda
                        </a>
                        @if($pembukaan)
                        <a href="{{ route('ppdb.pendaftaran.pilih') }}"
                           class="flex items-center gap-2 bg-secondary-container text-on-secondary-container hover:opacity-90 transition-opacity px-3 py-2.5 rounded-xl text-body-sm font-semibold">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            Daftar Baru
                        </a>
                        @endif
                    </div>
                </div>

                <div class="h-px bg-outline-variant w-full"></div>

                {{-- Kontak --}}
                <div class="bg-surface-bright border border-outline-variant p-4 rounded-xl flex items-center justify-between gap-4">
                    <div>
                        <p class="font-label-md text-label-md font-bold text-on-surface">Butuh Bantuan?</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Hubungi tim panitia PPDB kami.</p>
                    </div>
                    <a href="https://wa.me/6281234567890" target="_blank"
                       class="flex-shrink-0 bg-surface text-primary border border-primary px-4 py-2 rounded-lg font-label-md text-label-md hover:bg-surface-container transition-colors whitespace-nowrap">
                        Kontak Admin
                    </a>
                </div>
            </div>
        </section>

    </div>

    {{-- ── Pendaftaran Saya ───────────────────────────────────── --}}
    <section class="bg-surface-container-lowest rounded-2xl border-t-[3px] border-tertiary soft-shadow overflow-hidden">
        <div class="flex items-center justify-between px-8 py-5 border-b border-surface-container-high">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Pendaftaran Saya</h3>
            @if($pendaftaran_list->isNotEmpty())
            <a href="{{ route('ppdb.pendaftaran.pilih') }}"
               class="flex items-center gap-1.5 bg-primary text-on-primary px-4 py-2 rounded-lg text-body-sm font-semibold hover:bg-primary-container transition-colors">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Daftar Baru
            </a>
            @endif
        </div>

        <div class="p-8">
            @if($pendaftaran_list->isEmpty())
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-24 h-24 mb-5">
                    <svg viewBox="0 0 140 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                        <rect x="30" y="20" width="80" height="80" rx="8" fill="#eff5ec" stroke="#acf3b9" stroke-width="2.5"/>
                        <rect x="44" y="35" width="52" height="6" rx="3" fill="#acf3b9"/>
                        <rect x="44" y="48" width="40" height="5" rx="2.5" fill="#eaf0e6"/>
                        <rect x="44" y="59" width="45" height="5" rx="2.5" fill="#eaf0e6"/>
                        <rect x="44" y="70" width="30" height="5" rx="2.5" fill="#eaf0e6"/>
                        <circle cx="100" cy="82" r="22" fill="#00682f"/>
                        <line x1="100" y1="72" x2="100" y2="92" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                        <line x1="90" y1="82" x2="110" y2="82" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <h4 class="font-headline-sm text-headline-sm text-on-surface mb-2">Belum Ada Pendaftaran</h4>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6 max-w-sm">
                    @if($pembukaan)
                        Pendaftaran sedang dibuka untuk tahun ajaran
                        <strong>{{ $pembukaan->tahunPelajaran?->nama ?? 'TA 2026/2027' }}</strong>. Mulai daftar sekarang!
                    @else
                        Pantau terus untuk info pembukaan pendaftaran PPDB.
                    @endif
                </p>
                @if($pembukaan)
                <a href="{{ route('ppdb.pendaftaran.pilih') }}"
                   class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-3 rounded-xl font-label-md text-label-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <span class="material-symbols-outlined text-[20px]">note_add</span>
                    Mulai Pendaftaran
                </a>
                @endif
            </div>

            @else
            {{-- Daftar --}}
            <div class="space-y-3">
                @foreach($pendaftaran_list as $daftar)
                @php
                    $statusMap = [
                        'draft'        => ['label' => 'Draft',        'icon' => 'draft',         'color' => 'bg-surface-container text-on-surface-variant'],
                        'submitted'    => ['label' => 'Dikirim',      'icon' => 'send',          'color' => 'bg-surface-container text-primary'],
                        'verifikasi'   => ['label' => 'Diverifikasi', 'icon' => 'manage_search', 'color' => 'bg-surface-container text-secondary'],
                        'lulus'        => ['label' => 'Lulus',        'icon' => 'trophy',        'color' => 'bg-secondary-container text-on-secondary-container'],
                        'tidak_lulus'  => ['label' => 'Tidak Lulus',  'icon' => 'cancel',        'color' => 'bg-error-container text-on-error-container'],
                        'daftar_ulang' => ['label' => 'Daftar Ulang', 'icon' => 'refresh',       'color' => 'bg-tertiary-fixed text-tertiary'],
                        'siswa_tetap'  => ['label' => 'Siswa Tetap',  'icon' => 'school',        'color' => 'bg-secondary-container text-on-secondary-container'],
                    ];
                    $sc = $statusMap[$daftar->status] ?? ['label' => ucfirst($daftar->status), 'icon' => 'circle', 'color' => 'bg-surface-container text-on-surface-variant'];
                @endphp
                <article class="flex items-center justify-between gap-4 p-5 rounded-2xl bg-surface-container-low hover:bg-surface-container transition-colors border border-outline-variant/40">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-body-sm text-on-surface-variant">No.</span>
                            <span class="font-body-sm font-bold text-on-surface font-mono">{{ $daftar->no_pendaftaran ?? '—' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span class="text-body-sm text-on-surface-variant flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-primary">account_tree</span>
                                {{ $daftar->jalurPendaftaran?->nama ?? 'Jalur tidak tersedia' }}
                            </span>
                            <span class="text-body-sm text-on-surface-variant flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-primary">event</span>
                                {{ $daftar->created_at?->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="inline-flex items-center gap-1.5 {{ $sc['color'] }} px-3 py-1 rounded-full text-body-sm font-semibold whitespace-nowrap">
                            <span class="material-symbols-outlined text-[14px]">{{ $sc['icon'] }}</span>
                            {{ $sc['label'] }}
                        </span>
                        <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                           class="flex items-center gap-1.5 bg-primary text-on-primary px-3 py-2 rounded-lg text-body-sm font-semibold hover:bg-primary-container transition-colors whitespace-nowrap">
                            <span class="material-symbols-outlined text-[16px]">{{ in_array($daftar->status, ['draft','submitted']) ? 'edit' : 'visibility' }}</span>
                            {{ in_array($daftar->status, ['draft','submitted']) ? 'Lanjutkan' : 'Detail' }}
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
            @endif
        </div>
    </section>

</main>
@endsection

{{-- ═══════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════ --}}
@push('scripts')
<script>
(function () {
    'use strict';

    // Greeting time
    var hour = new Date().getHours();
    var prefix = hour < 11 ? 'Selamat Pagi' : hour < 15 ? 'Selamat Siang' : hour < 18 ? 'Selamat Sore' : 'Selamat Malam';
    var nameEl = document.getElementById('greeting-name');
    if (nameEl) nameEl.closest('h1').innerHTML = prefix + ', <span id="greeting-name">' + nameEl.textContent + '</span>!';

    // Animate circular progress arc
    var arc = document.getElementById('progress-arc');
    if (arc) {
        var target = parseFloat(arc.dataset.target) || 0;
        setTimeout(function () {
            arc.setAttribute('stroke-dasharray', target + ', 100');
        }, 400);
    }

    // Animate linear progress bar
    var bar = document.getElementById('profile-progress-bar');
    if (bar) {
        setTimeout(function () { bar.style.width = bar.dataset.target; }, 400);
    }
})();
</script>
@endpush
