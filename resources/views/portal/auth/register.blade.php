@extends('layouts.portal-stitch')

@section('title', 'Daftar Akun PPDB')

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

        {{-- Split Card --}}
        <div class="flex flex-col md:flex-row bg-surface-container-lowest rounded-2xl soft-shadow border-t-[3px] border-tertiary-fixed-dim overflow-hidden">

            {{-- Left Panel: Branding --}}
            <div class="md:w-[340px] bg-gradient-to-br from-primary to-primary-container p-10 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 opacity-[0.07]"
                     style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40' fill='none' stroke='%23ffffff' stroke-width='1.5'/%3E%3C/svg%3E\")"></div>

                <div class="relative z-10">
                    {{-- Logo --}}
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                            <img src="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}"
                                 alt="Logo Marifat" class="w-9 h-9 object-contain" style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.3))">
                        </div>
                        <div>
                            <p class="text-on-primary font-bold leading-tight text-[15px]">Marifat</p>
                            <p class="text-on-primary/70 text-[12px]">Ma'arif Integrated Facility</p>
                        </div>
                    </div>

                    <p class="text-tertiary-fixed-dim text-[10px] tracking-[0.5em] font-semibold mb-3" aria-hidden="true">✦ &nbsp; ✦ &nbsp; ✦</p>
                    <h1 class="text-headline-lg text-on-primary mb-3 leading-tight">Daftar<br>Akun PPDB</h1>
                    <p class="text-on-primary/75 text-body-md mb-10">
                        Buat akun untuk mendaftarkan putra-putri Anda ke LP Ma'arif NU Kraksaan (Marifat).
                    </p>

                    {{-- Steps Overview --}}
                    <div class="space-y-3">
                        @foreach([
                            ['1', 'Buat akun wali murid'],
                            ['2', 'Verifikasi email'],
                            ['3', 'Lengkapi profil peserta'],
                            ['4', 'Pilih jalur pendaftaran'],
                        ] as $step)
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0 text-on-primary font-bold text-[12px]">
                                {{ $step[0] }}
                            </div>
                            <span class="text-on-primary/85 text-body-sm">{{ $step[1] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10 mt-10">
                    <p class="text-on-primary/40 text-[11px]">&copy; {{ date('Y') }} Marifat — LP Ma'arif NU Kraksaan</p>
                </div>
            </div>

            {{-- Right Panel: Form --}}
            <div class="flex-1 p-8 md:p-10 overflow-y-auto">
                <h2 class="text-headline-md text-on-surface mb-1">Buat Akun Baru</h2>
                <p class="text-body-sm text-on-surface-variant mb-7">Isi data wali murid untuk mendaftar.</p>

                <form id="form-register" action="{{ route('ppdb.register') }}" method="POST" novalidate>
                    @csrf

                    {{-- Nama --}}
                    <div class="mb-4">
                        <label for="nama_lengkap" class="block text-label-md text-on-surface-variant mb-2">Nama Wali Murid</label>
                        <div class="flex rounded-lg overflow-hidden border @error('nama_lengkap') border-error ring-2 ring-error/20 @else border-outline-variant @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">person</span>
                            </div>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                                   placeholder="Nama lengkap ayah/ibu/wali" autocomplete="name" autofocus
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0">
                        </div>
                        @error('nama_lengkap')
                        <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-label-md text-on-surface-variant mb-2">Email Wali Murid</label>
                        <div class="flex rounded-lg overflow-hidden border @error('email') border-error ring-2 ring-error/20 @else border-outline-variant @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">mail</span>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="email@wali.com" autocomplete="email"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0">
                        </div>
                        @error('email')
                        <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                        </p>
                        @enderror
                        <p class="mt-1 text-body-sm text-on-surface-variant">Digunakan untuk login dan notifikasi PPDB.</p>
                    </div>

                    {{-- No HP --}}
                    <div class="mb-4">
                        <label for="no_hp" class="block text-label-md text-on-surface-variant mb-2">No HP Wali Murid</label>
                        <div class="flex rounded-lg overflow-hidden border @error('no_hp') border-error ring-2 ring-error/20 @else border-outline-variant @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">phone_iphone</span>
                            </div>
                            <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
                                   placeholder="08xxxxxxxxxx" autocomplete="tel" inputmode="numeric"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0">
                        </div>
                        @error('no_hp')
                        <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Password row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

                        {{-- Password --}}
                        <div>
                            <label for="password" class="block text-label-md text-on-surface-variant mb-2">Password</label>
                            <div class="flex rounded-lg overflow-hidden border @error('password') border-error ring-2 ring-error/20 @else border-outline-variant @enderror focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">lock</span>
                                </div>
                                <input type="password" id="password" name="password"
                                       placeholder="Min. 8 karakter" autocomplete="new-password"
                                       class="flex-1 bg-surface-container-lowest px-3 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0">
                                <button type="button" class="toggle-pw w-11 bg-surface-container-high flex items-center justify-center border-l border-outline-variant hover:bg-surface-container transition-colors flex-shrink-0" data-target="password">
                                    <span class="material-symbols-outlined text-outline text-[20px]">visibility</span>
                                </button>
                            </div>
                            @error('password')
                            <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Konfirmasi --}}
                        <div>
                            <label for="password_confirmation" class="block text-label-md text-on-surface-variant mb-2">Konfirmasi</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">lock_reset</span>
                                </div>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       placeholder="Ulangi password" autocomplete="new-password"
                                       class="flex-1 bg-surface-container-lowest px-3 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0">
                                <button type="button" class="toggle-pw w-11 bg-surface-container-high flex items-center justify-center border-l border-outline-variant hover:bg-surface-container transition-colors flex-shrink-0" data-target="password_confirmation">
                                    <span class="material-symbols-outlined text-outline text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>

                    </div>

                    {{-- Setuju Syarat --}}
                    <div class="mb-6">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="setuju_syarat" name="setuju_syarat" value="1"
                                   {{ old('setuju_syarat') ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 rounded border-2 border-outline-variant text-primary focus:ring-primary/20 cursor-pointer flex-shrink-0">
                            <label for="setuju_syarat" class="text-body-sm text-on-surface-variant leading-relaxed cursor-pointer">
                                Saya menyetujui
                                <a href="#" class="font-semibold text-primary hover:text-primary-container transition-colors">syarat dan ketentuan</a>
                                pendaftaran PPDB Marifat — LP Ma'arif NU Kraksaan
                            </label>
                        </div>
                        @error('setuju_syarat')
                        <p class="mt-1.5 text-body-sm text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit" id="btn-register"
                            class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-4 px-6 rounded-[10px] flex items-center justify-center gap-2 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none mb-5">
                        <span id="btn-register-text" class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">person_add</span>
                            Daftar Sekarang
                        </span>
                        <span id="btn-register-loading" class="hidden items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </form>

                <div class="text-center">
                    <span class="text-body-sm text-on-surface-variant">Sudah punya akun?</span>
                    <a href="{{ route('ppdb.login') }}"
                       class="ml-1 text-body-sm font-semibold text-primary hover:text-primary-container transition-colors">
                        Login di sini <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
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
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('.material-symbols-outlined');
            const isPass = input.type === 'password';
            input.type        = isPass ? 'text' : 'password';
            icon.textContent  = isPass ? 'visibility_off' : 'visibility';
        });
    });
    const form    = document.getElementById('form-register');
    const btn     = document.getElementById('btn-register');
    const btnText = document.getElementById('btn-register-text');
    const btnLoad = document.getElementById('btn-register-loading');
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
