{{-- SHOW HEADER: Progress card + Informasi Pendaftaran --}}
@php $user = auth()->user(); @endphp

<div class="bg-gradient-to-r from-primary to-primary-container rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative overflow-hidden shadow-lg border-b-4 border-tertiary-container">
    <div class="absolute inset-0 opacity-[0.06]"
         style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

    {{-- Left: Pendaftaran info & progress --}}
    <div class="relative z-10 flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-3 mb-3">
            <span class="text-body-sm text-on-primary/70">No. Pendaftaran</span>
            <span class="font-mono font-bold text-on-primary text-headline-sm">{{ $pend?->no_pendaftaran }}</span>
            <span class="inline-flex items-center gap-1.5 {{ $st['bg'] }} {{ $st['text'] }} text-label-sm font-semibold px-3 py-1 rounded-full">
                <span class="material-symbols-outlined text-[16px]">{{ $st['icon'] }}</span>
                {{ $st['label'] }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-body-sm text-on-primary/80 mb-4">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">account_tree</span>
                <span>{{ $jalur?->nama ?? '—' }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                <span>{{ $fmtTgl($pend?->tanggal_daftar ?? $pend?->created_at) }}</span>
            </div>
        </div>

        {{-- Progress bar --}}
        <div>
            <div class="flex justify-between text-body-sm text-on-primary/80 mb-1.5">
                <span>Kelengkapan Berkas</span>
                <span id="main-pct" class="font-bold text-on-primary">{{ $totalPersen }}%</span>
            </div>
            <div class="h-3 bg-white/20 rounded-full overflow-hidden">
                <div id="main-bar"
                     class="h-full bg-tertiary-fixed-dim rounded-full transition-all duration-700"
                     style="width:{{ $totalPersen }}%"
                     role="progressbar"
                     aria-valuenow="{{ $totalPersen }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        {{-- Hidden spans for JS --}}
        <span id="formulir-pct" class="sr-only">{{ $prog['formulir']['persen'] }}%</span>
        <span id="dokumen-pct"  class="sr-only">{{ $prog['dokumen']['persen'] }}%</span>
    </div>

    {{-- Right: User profile --}}
    <div class="relative z-10 flex items-center gap-4 flex-shrink-0">
        <img src="{{ $peserta?->foto
            ? asset('storage/' . $peserta->foto)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=100&background=00843d&color=fff&bold=true&rounded=true' }}"
             alt="Foto {{ $user->name }}"
             class="w-16 h-16 rounded-2xl object-cover ring-4 ring-white/30 shadow">
        <div>
            <div class="text-headline-sm font-bold text-on-primary">{{ $user->name }}</div>
            <div class="flex items-center gap-1.5 text-body-sm text-on-primary/80 mt-0.5">
                <span class="material-symbols-outlined text-[14px]">mail</span>
                <span>{{ Str::limit($user->email, 26) }}</span>
            </div>
            <span class="inline-flex items-center gap-1.5 mt-2 bg-white/15 rounded-full px-3 py-1 text-body-sm text-on-primary">
                <span class="w-2 h-2 rounded-full bg-tertiary-fixed-dim flex-shrink-0"></span>
                Akun Aktif
            </span>
        </div>
    </div>
</div>
