@extends('layouts.portal-stitch')

@section('title', 'Masuk Portal PPDB')

@section('content')
<div class="min-h-screen pattern-bg-auth flex items-center justify-center p-4 py-10">
    <div class="w-full max-w-[960px]">

        {{-- Flash Messages --}}
        @if(session('error'))
        <div class="mb-4 flex items-center gap-3 bg-error-container text-on-error-container px-4 py-3 rounded-xl border border-error/20">
            <span class="material-symbols-outlined text-[20px] flex-shrink-0">error</span>
            <span class="text-body-sm">{{ session('error') }}</span>
        </div>
        @endif
        @if(session('success'))
        <div class="mb-4 flex items-center gap-3 bg-secondary-container text-on-secondary-container px-4 py-3 rounded-xl border border-secondary/20">
            <span class="material-symbols-outlined text-[20px] flex-shrink-0">check_circle</span>
            <span class="text-body-sm">{{ session('success') }}</span>
        </div>
        @endif
        @if(session('info'))
        <div class="mb-4 flex items-center gap-3 bg-surface-container text-on-surface-variant px-4 py-3 rounded-xl border border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0">info</span>
            <span class="text-body-sm">{{ session('info') }}</span>
        </div>
        @endif

        {{-- Split Card --}}
        <div class="flex flex-col md:flex-row bg-surface-container-lowest rounded-2xl soft-shadow border-t-[3px] border-tertiary-fixed-dim overflow-hidden">

            {{-- Left Panel: Branding --}}
            <div class="md:w-[380px] bg-gradient-to-br from-primary to-primary-container p-10 flex flex-col justify-between relative overflow-hidden">
                {{-- Background Pattern --}}
                <div class="absolute inset-0 opacity-[0.07]"
                     style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 0l5.8 24.2L60 30l-24.2 5.8L30 60l-5.8-24.2L0 30l24.2-5.8Z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

                <div class="relative z-10">
                    {{-- Logo --}}
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                            <img src="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}"
                                 alt="Logo LP Ma'arif NU" class="w-9 h-9 object-contain" style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.3))">
                        </div>
                        <div>
                            <p class="text-on-primary font-bold leading-tight text-[15px]">LP Ma'arif NU</p>
                            <p class="text-on-primary/70 text-[12px]">Kraksaan</p>
                        </div>
                    </div>

                    {{-- Headline --}}
                    <p class="text-tertiary-fixed-dim text-[10px] tracking-[0.5em] font-semibold mb-3" aria-hidden="true">✦ &nbsp; ✦ &nbsp; ✦</p>
                    <h1 class="text-headline-lg text-on-primary mb-3 leading-tight">Selamat<br>Datang!</h1>
                    <p class="text-on-primary/75 text-body-md mb-10">
                        Portal resmi PPDB LP Ma'arif NU Kraksaan. Masuk untuk melanjutkan proses pendaftaran putra-putri Anda.
                    </p>

                    {{-- Feature List --}}
                    <div class="space-y-4">
                        @foreach([
                            ['shield_person', 'Akun Aman & Terenkripsi'],
                            ['notifications_active', 'Notifikasi Status Real-time'],
                            ['event_available', 'Pantau Jadwal PPDB'],
                        ] as $feat)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-on-primary text-[18px]">{{ $feat[0] }}</span>
                            </div>
                            <span class="text-on-primary/85 text-body-sm">{{ $feat[1] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bottom ornament --}}
                <div class="relative z-10 mt-10">
                    <p class="text-on-primary/40 text-[11px]">&copy; {{ date('Y') }} LP Ma'arif NU Kraksaan</p>
                </div>
            </div>

            {{-- Right Panel: Form --}}
            <div class="flex-1 p-8 md:p-10 flex flex-col justify-center">
                <h2 class="text-headline-md text-on-surface mb-1">Masuk ke Portal</h2>
                <p class="text-body-sm text-on-surface-variant mb-8">Gunakan email dan password yang sudah terdaftar.</p>

                <form id="form-login-peserta" action="{{ route('ppdb.login.post') }}" method="POST" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-label-md text-on-surface-variant mb-2">
                            Alamat Email Wali Murid
                        </label>
                        <div class="flex rounded-lg overflow-hidden border @error('email') border-error ring-2 ring-error/20 @else border-outline-variant @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">mail</span>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="email@wali.com"
                                autocomplete="email"
                                autofocus
                                class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                            >
                        </div>
                        @error('email')
                        <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="text-label-md text-on-surface-variant">Password</label>
                            <a href="#" class="text-body-sm text-tertiary hover:text-tertiary-container transition-colors">Lupa Password?</a>
                        </div>
                        <div class="flex rounded-lg overflow-hidden border @error('password') border-error ring-2 ring-error/20 @else border-outline-variant @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">lock</span>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Password Anda"
                                autocomplete="current-password"
                                class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                            >
                            <button type="button" id="toggle-password"
                                    class="w-12 bg-surface-container-high flex items-center justify-center border-l border-outline-variant hover:bg-surface-container transition-colors flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]" id="icon-password">visibility</span>
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit" id="btn-login"
                            class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-4 px-6 rounded-[10px] flex items-center justify-center gap-2 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none mb-6">
                        <span id="btn-login-text" class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">login</span>
                            Masuk ke Portal
                        </span>
                        <span id="btn-login-loading" class="hidden items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </form>

                <div class="flex items-center gap-3 mb-6">
                    <div class="flex-1 h-px bg-outline-variant"></div>
                    <span class="text-body-sm text-on-surface-variant">atau</span>
                    <div class="flex-1 h-px bg-outline-variant"></div>
                </div>

                <div class="text-center">
                    <span class="text-body-sm text-on-surface-variant">Belum punya akun?</span>
                    <a href="{{ route('ppdb.register') }}"
                       class="ml-1 text-body-sm font-semibold text-primary hover:text-primary-container transition-colors">
                        Daftar sekarang
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const toggleBtn = document.getElementById('toggle-password');
    const pwInput   = document.getElementById('password');
    const pwIcon    = document.getElementById('icon-password');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const isPass  = pwInput.type === 'password';
            pwInput.type  = isPass ? 'text' : 'password';
            pwIcon.textContent = isPass ? 'visibility_off' : 'visibility';
        });
    }
    const form    = document.getElementById('form-login-peserta');
    const btn     = document.getElementById('btn-login');
    const btnText = document.getElementById('btn-login-text');
    const btnLoad = document.getElementById('btn-login-loading');
    if (form) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoad.classList.remove('hidden');
            btnLoad.classList.add('flex');
        });
    }
})();
</script>
@endpush
