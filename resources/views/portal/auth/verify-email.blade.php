@extends('layouts.portal-stitch')

@section('title', 'Verifikasi Email')

@section('content')
<div class="min-h-screen pattern-bg-auth flex items-center justify-center p-4 py-10">
    <div class="w-full max-w-[900px]">

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
            <div class="md:w-[320px] bg-gradient-to-br from-primary to-primary-container p-10 flex flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 opacity-[0.07]"
                     style="background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z' fill='none' stroke='%23ffffff' stroke-width='1'/%3E%3C/svg%3E\")"></div>

                <div class="relative z-10">
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

                    <p class="text-tertiary-fixed-dim text-[10px] tracking-[0.5em] font-semibold mb-3" aria-hidden="true">✦ &nbsp; ✦ &nbsp; ✦</p>
                    <h1 class="text-headline-lg text-on-primary mb-3 leading-tight">Verifikasi<br>Email Anda</h1>
                    <p class="text-on-primary/75 text-body-md mb-8">
                        Kode OTP 6 digit telah dikirimkan ke email wali murid.
                    </p>

                    {{-- Email target --}}
                    <div class="bg-white/15 rounded-xl p-4">
                        <p class="text-on-primary/60 text-[11px] uppercase tracking-wider mb-1">Dikirim ke</p>
                        <p class="text-on-primary font-semibold text-body-md break-all">{{ $maskedEmail }}</p>
                    </div>

                    <div class="mt-6 space-y-3">
                        @foreach([
                            ['mark_email_read', 'Cek folder Inbox atau Spam'],
                            ['timer', 'Kode berlaku selama 10 menit'],
                            ['security', 'Jangan bagikan kode OTP'],
                        ] as $tip)
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-on-primary/60 text-[18px]">{{ $tip[0] }}</span>
                            <span class="text-on-primary/75 text-body-sm">{{ $tip[1] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10 mt-10">
                    <p class="text-on-primary/40 text-[11px]">&copy; {{ date('Y') }} LP Ma'arif NU Kraksaan</p>
                </div>
            </div>

            {{-- Right Panel: OTP Form --}}
            <div class="flex-1 p-8 md:p-10 flex flex-col justify-center">
                <h2 class="text-headline-md text-on-surface mb-1">Masukkan Kode OTP</h2>
                <p class="text-body-sm text-on-surface-variant mb-8">
                    Masukkan 6 digit kode verifikasi yang dikirim ke email wali murid.
                </p>

                {{-- Timer --}}
                <div class="flex justify-center mb-8">
                    <div class="inline-flex flex-col items-center bg-surface-container-low border-2 border-surface-container-high rounded-2xl px-8 py-4">
                        <span id="otp-timer" class="text-[36px] font-extrabold text-primary tracking-[4px] tabular-nums leading-none">10:00</span>
                        <span class="text-[11px] text-on-surface-variant uppercase tracking-wider mt-2">Kode berlaku selama</span>
                    </div>
                </div>

                {{-- OTP Form --}}
                <form id="form-otp" action="{{ route('ppdb.verify-email.post') }}" method="POST">
                    @csrf
                    <input type="hidden" id="otp" name="otp">

                    {{-- 6 Digit Boxes --}}
                    <div class="flex justify-center gap-3 mb-3" id="otp-inputs">
                        @for($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            maxlength="1"
                            id="otp-{{ $i }}"
                            class="otp-digit w-12 h-16 text-center text-2xl font-extrabold text-on-surface bg-surface-container-low border-2 border-outline-variant rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all caret-transparent"
                            inputmode="numeric"
                            pattern="[0-9]"
                            autocomplete="off"
                        >
                        @endfor
                    </div>

                    @error('otp')
                    <p class="text-center text-body-sm text-error mb-4 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
                    </p>
                    @enderror

                    {{-- Submit --}}
                    <button type="submit" id="btn-verify"
                            class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-4 px-6 rounded-[10px] flex items-center justify-center gap-2 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none mt-6 mb-5">
                        <span id="btn-verify-text" class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">verified_user</span>
                            Verifikasi Akun
                        </span>
                        <span id="btn-verify-loading" class="hidden items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memverifikasi...
                        </span>
                    </button>
                </form>

                {{-- Resend --}}
                <div class="text-center mb-3">
                    <form id="form-resend" action="{{ route('ppdb.resend-otp') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" id="btn-resend"
                                class="text-body-sm font-semibold text-primary hover:text-primary-container transition-colors disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1.5"
                                disabled>
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                            Kirim Ulang Kode
                            <span id="resend-countdown" class="text-on-surface-variant font-normal"></span>
                        </button>
                    </form>
                </div>

                {{-- Ganti Email --}}
                <div class="text-center">
                    <form action="{{ route('ppdb.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="text-body-sm text-on-surface-variant hover:text-on-surface transition-colors inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                            Gunakan email lain
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .otp-digit.filled { border-color: #00682f; background-color: #eff5ec; }
    .otp-digit.error  { border-color: #ba1a1a !important; background-color: #ffdad6; animation: otp-shake 0.4s ease; }
    #otp-timer.expired { color: #ba1a1a; }
    @keyframes otp-shake {
        0%, 100% { transform: translateX(0); }
        25%       { transform: translateX(-4px); }
        75%       { transform: translateX(4px); }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    const digits    = Array.from({ length: 6 }, function (_, i) { return document.getElementById('otp-' + i); });
    const hiddenOtp = document.getElementById('otp');
    const btnVerify = document.getElementById('btn-verify');
    const btnResend = document.getElementById('btn-resend');
    const form      = document.getElementById('form-otp');

    function syncHidden() { hiddenOtp.value = digits.map(function (d) { return d.value; }).join(''); }

    function submitForm() {
        syncHidden();
        btnVerify.disabled = true;
        document.getElementById('btn-verify-text').classList.add('hidden');
        var load = document.getElementById('btn-verify-loading');
        load.classList.remove('hidden');
        load.classList.add('flex');
        form.submit();
    }

    digits.forEach(function (input, idx) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                input.value = '';
                syncHidden();
                input.classList.remove('filled');
                if (idx > 0) digits[idx - 1].focus();
                e.preventDefault();
                return;
            }
            if (!/^[0-9]$/.test(e.key) && !['Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            if (input.value) {
                input.classList.add('filled');
                syncHidden();
                if (idx < 5) { digits[idx + 1].focus(); }
                else if (hiddenOtp.value.length === 6) { submitForm(); }
            } else {
                input.classList.remove('filled');
                syncHidden();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach(function (char, i) {
                if (digits[i]) { digits[i].value = char; digits[i].classList.add('filled'); }
            });
            syncHidden();
            digits[Math.min(pasted.length, 5)].focus();
            if (pasted.length === 6) submitForm();
        });

        input.addEventListener('focus', function () { input.select(); });
    });

    if (digits[0]) digits[0].focus();

    form.addEventListener('submit', function () {
        syncHidden();
        btnVerify.disabled = true;
        document.getElementById('btn-verify-text').classList.add('hidden');
        var load = document.getElementById('btn-verify-loading');
        load.classList.remove('hidden');
        load.classList.add('flex');
    });

    var totalSeconds  = 600;
    var timerEl       = document.getElementById('otp-timer');
    var timerInterval = setInterval(function () {
        totalSeconds--;
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            timerEl.textContent = '00:00';
            timerEl.classList.add('expired');
            digits.forEach(function (d) { d.disabled = true; });
            btnVerify.disabled = true;
            return;
        }
        timerEl.textContent = String(Math.floor(totalSeconds / 60)).padStart(2, '0') + ':' + String(totalSeconds % 60).padStart(2, '0');
    }, 1000);

    var resendCooldown = 60;
    var resendCountEl  = document.getElementById('resend-countdown');
    resendCountEl.textContent = '(' + resendCooldown + 'd)';
    var resendInterval = setInterval(function () {
        resendCooldown--;
        if (resendCooldown <= 0) {
            clearInterval(resendInterval);
            btnResend.disabled          = false;
            resendCountEl.textContent   = '';
        } else {
            resendCountEl.textContent   = '(' + resendCooldown + 'd)';
        }
    }, 1000);

    @error('otp')
        digits.forEach(function (d) { d.classList.add('error'); });
        setTimeout(function () { digits.forEach(function (d) { d.classList.remove('error'); }); }, 500);
    @enderror
})();
</script>
@endpush
