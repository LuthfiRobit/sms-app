@extends('layouts.portal-stitch')

@section('title', 'Pendaftaran Saya — PPDB')

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
@php
    $pendaftaranList = $data['data'] ?? [];
    $hasPendaftaran  = !empty($pendaftaranList) && count($pendaftaranList) > 0;

    $statusConfig = [
        'draft'       => ['label' => 'Draft',        'icon' => 'draft',        'bg' => 'bg-surface-container-high',    'text' => 'text-on-surface-variant', 'dot' => 'bg-outline'],
        'submit'      => ['label' => 'Menunggu',      'icon' => 'hourglass_empty', 'bg' => 'bg-amber-50',            'text' => 'text-amber-700',          'dot' => 'bg-amber-400'],
        'verifikasi'  => ['label' => 'Verifikasi',    'icon' => 'manage_search', 'bg' => 'bg-blue-50',               'text' => 'text-blue-700',           'dot' => 'bg-blue-400'],
        'lulus'       => ['label' => 'Lulus',         'icon' => 'emoji_events',  'bg' => 'bg-green-50',              'text' => 'text-green-700',          'dot' => 'bg-green-500'],
        'tidak_lulus' => ['label' => 'Tidak Lulus',   'icon' => 'cancel',        'bg' => 'bg-red-50',                'text' => 'text-red-700',            'dot' => 'bg-red-400'],
        'daftar_ulang'=> ['label' => 'Daftar Ulang',  'icon' => 'autorenew',     'bg' => 'bg-blue-50',               'text' => 'text-blue-700',           'dot' => 'bg-blue-500'],
        'siswa_tetap' => ['label' => 'Siswa Tetap',   'icon' => 'school',        'bg' => 'bg-green-50',              'text' => 'text-green-700',          'dot' => 'bg-green-500'],
    ];

    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $formatTgl = function ($tgl) use ($bulanIndo) {
        if (!$tgl) return '—';
        $dt = $tgl instanceof \Carbon\Carbon ? $tgl : \Carbon\Carbon::parse($tgl);
        return $dt->day . ' ' . $bulanIndo[$dt->month] . ' ' . $dt->year;
    };

    $user = auth()->user();
@endphp

{{-- Toast --}}
<div id="ppdb-toast"
     class="fixed top-5 right-5 z-[9999] hidden min-w-[280px] max-w-sm flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-on-primary text-body-sm font-semibold transition-all duration-300"
     role="alert" aria-live="assertive">
    <span id="ppdb-toast-icon" class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
    <span id="ppdb-toast-msg"></span>
</div>

<div class="w-full max-w-[1440px] mx-auto px-4 md:px-8 py-6 flex flex-col gap-6">

    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-primary to-primary-container rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden shadow-lg border-b-4 border-tertiary-container">
        {{-- Islamic pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

        {{-- Left: Icon + Title --}}
        <div class="relative z-10 flex items-start gap-5 flex-1 min-w-0">
            <div class="w-16 h-16 rounded-2xl bg-white/20 border-2 border-white/30 flex items-center justify-center flex-shrink-0 shadow">
                <span class="material-symbols-outlined text-[32px] text-on-primary" style="font-variation-settings:'FILL' 1">description</span>
            </div>
            <div>
                <h1 class="text-display-sm font-bold text-on-primary mb-1">Pendaftaran Saya</h1>
                <p class="text-body-md text-on-primary/80">Kelola dan pantau status pendaftaran PPDB Anda</p>
                <div class="flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('ppdb.pendaftaran.pilih') }}"
                       class="bg-tertiary-fixed-dim text-on-primary px-5 py-2.5 rounded-xl font-label-md text-label-md shadow-sm hover:brightness-105 transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        Daftar Jalur Baru
                    </a>
                    <a href="{{ route('ppdb.dashboard') }}"
                       class="bg-white/15 hover:bg-white/25 text-on-primary px-5 py-2.5 rounded-xl font-label-md text-label-md transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">home</span>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: User info --}}
        <div class="relative z-10 flex items-center gap-4 flex-shrink-0">
            <img src="{{ $user->peserta?->foto
                ? asset('storage/' . $user->peserta->foto)
                : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=80&background=00843d&color=fff&bold=true&rounded=true' }}"
                 alt="Foto {{ $user->name }}"
                 class="w-16 h-16 rounded-2xl object-cover ring-4 ring-white/30 shadow">
            <div>
                <div class="text-headline-sm font-bold text-on-primary">{{ $user->name }}</div>
                <div class="flex items-center gap-1.5 text-body-sm text-on-primary/80 mt-0.5">
                    <span class="material-symbols-outlined text-[14px]">mail</span>
                    <span>{{ Str::limit($user->email, 28) }}</span>
                </div>
                <span class="inline-flex items-center gap-1.5 mt-2 bg-white/15 rounded-full px-3 py-1 text-body-sm text-on-primary">
                    <span class="w-2 h-2 rounded-full bg-tertiary-fixed-dim flex-shrink-0"></span>
                    Akun Aktif
                </span>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-body-md">
            <span class="material-symbols-outlined text-[20px] text-green-600 flex-shrink-0">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-body-md">
            <span class="material-symbols-outlined text-[20px] text-red-500 flex-shrink-0">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-5 py-3 text-body-md">
            <span class="material-symbols-outlined text-[20px] text-blue-500 flex-shrink-0">info</span>
            {{ session('info') }}
        </div>
    @endif

    {{-- EMPTY STATE --}}
    @if(!$hasPendaftaran)
        <div class="flex justify-center py-10">
            <div class="bg-surface-container-lowest rounded-2xl soft-shadow border-t-[3px] border-tertiary-fixed-dim p-10 flex flex-col items-center text-center max-w-md w-full">
                <div class="w-24 h-24 bg-primary/8 rounded-full flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-[48px] text-primary" style="font-variation-settings:'FILL' 1">description</span>
                </div>
                <h2 class="text-headline-md font-bold text-on-surface mb-2">Belum Ada Pendaftaran</h2>
                <p class="text-body-md text-on-surface-variant mb-6">
                    Anda belum memiliki pendaftaran PPDB aktif.<br>
                    Mulai daftar sekarang sebelum kuota habis!
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="{{ route('ppdb.pendaftaran.pilih') }}"
                       class="bg-gradient-to-r from-primary to-primary-container text-on-primary px-8 py-3 rounded-xl font-label-md text-label-md shadow hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        Mulai Daftar Sekarang
                    </a>
                    <a href="{{ route('ppdb.dashboard') }}"
                       class="bg-surface-container text-on-surface px-6 py-3 rounded-xl font-label-md text-label-md hover:bg-surface-container-high transition flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">home</span>
                        Dashboard
                    </a>
                </div>
                <div class="mt-6 flex items-center gap-2 text-body-sm text-on-surface-variant bg-surface-container-low rounded-xl px-4 py-3">
                    <span class="material-symbols-outlined text-[16px] text-tertiary-fixed-dim">lightbulb</span>
                    <span>Tip: Pastikan profil Anda sudah lengkap sebelum mendaftar.</span>
                </div>
            </div>
        </div>

    @else
        {{-- Stats Summary --}}
        @php
            $countDraft  = collect($pendaftaranList)->where('status', 'draft')->count();
            $countProses = collect($pendaftaranList)->whereIn('status', ['submit', 'verifikasi'])->count();
            $countLulus  = collect($pendaftaranList)->where('status', 'lulus')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-surface-container-lowest rounded-xl soft-shadow p-4 flex flex-col items-center text-center border border-outline-variant">
                <span class="text-display-sm font-bold text-on-surface">{{ count($pendaftaranList) }}</span>
                <span class="text-body-sm text-on-surface-variant">Total Pendaftaran</span>
            </div>
            @if($countDraft)
            <div class="bg-surface-container-lowest rounded-xl soft-shadow p-4 flex flex-col items-center text-center border border-outline-variant">
                <span class="text-display-sm font-bold text-on-surface-variant">{{ $countDraft }}</span>
                <span class="text-body-sm text-on-surface-variant">Draft</span>
            </div>
            @endif
            @if($countProses)
            <div class="bg-amber-50 rounded-xl soft-shadow p-4 flex flex-col items-center text-center border border-amber-200">
                <span class="text-display-sm font-bold text-amber-700">{{ $countProses }}</span>
                <span class="text-body-sm text-amber-600">Proses</span>
            </div>
            @endif
            @if($countLulus)
            <div class="bg-green-50 rounded-xl soft-shadow p-4 flex flex-col items-center text-center border border-green-200">
                <span class="text-display-sm font-bold text-green-700">{{ $countLulus }}</span>
                <span class="text-body-sm text-green-600">Lulus</span>
            </div>
            @endif
        </div>

        {{-- Pendaftaran Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($pendaftaranList as $daftar)
            @php
                $st = $statusConfig[$daftar->status] ?? [
                    'label'=>$daftar->status,'icon'=>'circle','bg'=>'bg-surface-container-high','text'=>'text-on-surface-variant','dot'=>'bg-outline'
                ];
                $isDraft  = $daftar->status === 'draft';
                $isSubmit = $daftar->status === 'submit';
                $tglDaftar = $formatTgl($daftar->tanggal_daftar ?? $daftar->created_at);
                $isLulus  = in_array($daftar->status, ['lulus', 'siswa_tetap']);
            @endphp
            <article class="bg-surface-container-lowest rounded-xl soft-shadow overflow-hidden border border-outline-variant flex flex-col {{ $isLulus ? 'border-t-[3px] border-t-green-400' : ($isDraft ? 'border-t-[3px] border-t-tertiary-fixed-dim' : 'border-t-[3px] border-t-outline-variant') }}">

                {{-- Card Header --}}
                <div class="flex items-center justify-between px-5 py-4 bg-surface-container-low border-b border-outline-variant">
                    <div>
                        <div class="text-body-sm text-on-surface-variant">No. Pendaftaran</div>
                        <div class="text-label-md font-bold text-on-surface font-mono">{{ $daftar->no_pendaftaran }}</div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 {{ $st['bg'] }} {{ $st['text'] }} text-label-sm font-semibold px-3 py-1.5 rounded-full">
                        <span class="material-symbols-outlined text-[16px]">{{ $st['icon'] }}</span>
                        {{ $st['label'] }}
                    </span>
                </div>

                {{-- Card Body --}}
                <div class="px-5 py-4 flex-1 flex flex-col gap-3">

                    {{-- Lembaga --}}
                    @php $lembagaDaftar = $daftar->lembaga ?? $daftar->jalurPendaftaran?->pembukaan?->lembaga; @endphp
                    @if($lembagaDaftar)
                    <div class="flex items-center gap-2 -mt-1 mb-1">
                        <span class="material-symbols-outlined text-[15px] text-on-surface-variant">apartment</span>
                        <span class="text-body-sm text-on-surface-variant font-medium">{{ $lembagaDaftar->nama }}</span>
                        <span class="text-label-sm px-1.5 py-0.5 rounded-full bg-secondary-container/50 text-on-secondary-container">{{ $lembagaDaftar->jenis }}</span>
                    </div>
                    @endif

                    {{-- Jalur & Gelombang --}}
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings:'FILL' 1">account_tree</span>
                        </div>
                        <div>
                            <div class="text-label-md font-semibold text-on-surface">
                                {{ $daftar->jalurPendaftaran?->nama ?? '—' }}
                            </div>
                            <div class="text-body-sm text-on-surface-variant">
                                {{ $daftar->tahunPelajaran?->nama ?? ($daftar->jalurPendaftaran?->pembukaanPpdb?->nama ?? '—') }}
                            </div>
                        </div>
                    </div>

                    {{-- Info row --}}
                    <div class="flex items-center gap-4 text-body-sm text-on-surface-variant">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                            <span>{{ $tglDaftar }}</span>
                        </div>
                        <div class="{{ $st['dot'] }} w-1.5 h-1.5 rounded-full"></div>
                        <span>{{ $st['label'] }}</span>
                    </div>

                    {{-- Progress Bar (hanya draft) --}}
                    @if($isDraft)
                    <div class="mt-1">
                        <div class="flex items-center justify-between text-body-sm text-on-surface-variant mb-1.5">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">task_alt</span> Kelengkapan</span>
                            <span class="text-[11px]">Lihat detail untuk progress penuh</span>
                        </div>
                        <div class="h-2 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all duration-700" style="width:0%" data-pendaftaran-id="{{ $daftar->id }}"></div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- Card Footer --}}
                <div class="px-5 py-3 bg-surface-container-low border-t border-outline-variant flex items-center gap-2 flex-wrap">
                    @if($isDraft)
                        <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                           class="flex-1 bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-sm font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-1.5 hover:shadow hover:-translate-y-0.5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">edit_note</span>
                            Lanjutkan Pengisian
                        </a>
                    @else
                        <a href="{{ route('ppdb.pendaftaran.show', $daftar->id) }}"
                           class="flex-1 border border-primary text-primary text-label-sm font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-1.5 hover:bg-primary/5 transition-all">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            Lihat Detail
                        </a>
                    @endif

                    @if($isSubmit)
                        <a href="/ppdb/pembayaran/{{ $daftar->id }}"
                           class="bg-amber-500 text-white text-label-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-1.5 hover:bg-amber-600 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">credit_card</span>
                            Bayar
                        </a>
                    @endif

                    @if($isLulus)
                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 text-label-sm font-semibold px-3 py-2 rounded-lg border border-green-200">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            Selesai
                        </span>
                    @endif
                </div>

            </article>
            @endforeach
        </div>

        {{-- CTA bottom --}}
        <div class="flex justify-center py-4">
            <a href="{{ route('ppdb.pendaftaran.pilih') }}"
               class="border-2 border-primary text-primary px-8 py-3 rounded-xl font-label-md text-label-md hover:bg-primary/5 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Daftar di Jalur Lain
            </a>
        </div>

    @endif

</div>
@endsection
