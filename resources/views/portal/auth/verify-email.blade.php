@extends('layouts.portal')

@section('title', 'Verifikasi Email')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5" style="max-width:480px">

        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <div class="portal-card">
            {{-- Card Header --}}
            <div class="card-header border-0 py-4 px-4 rounded-top-4 text-center"
                 style="background:linear-gradient(135deg,#16a34a 0%,#059669 100%)">
                <div class="text-white">
                    <div class="mb-2" style="font-size:2.5rem">📧</div>
                    <h1 class="fs-5 fw-bold mb-1">Verifikasi Email Anda</h1>
                    <p class="mb-0 opacity-75" style="font-size:0.8125rem">
                        Kode OTP dikirim ke
                        <strong>{{ $maskedEmail }}</strong>
                    </p>
                </div>
            </div>

            <div class="card-body px-4 py-4">

                {{-- Timer Countdown --}}
                <div class="text-center mb-4">
                    <div class="otp-timer-wrapper">
                        <div id="otp-timer" class="otp-timer">10:00</div>
                        <div class="otp-timer-label">Kode berlaku selama</div>
                    </div>
                </div>

                {{-- OTP Form --}}
                <form id="form-otp" action="{{ route('ppdb.verify-email.post') }}" method="POST">
                    @csrf
                    {{-- Hidden input yang akan diisi script dari 6 digit box --}}
                    <input type="hidden" id="otp" name="otp">

                    {{-- 6 Digit Input Boxes --}}
                    <div class="d-flex justify-content-center gap-2 mb-2" id="otp-inputs">
                        @for($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                maxlength="1"
                                id="otp-{{ $i }}"
                                class="otp-digit form-control text-center fw-bold fs-4"
                                inputmode="numeric"
                                pattern="[0-9]"
                                autocomplete="off"
                                style="width:48px;height:56px;border-radius:10px;border:2px solid #d1fae5;
                                       caret-color:transparent;font-size:1.5rem!important;"
                            >
                        @endfor
                    </div>

                    @error('otp')
                        <div class="text-danger text-center small mb-3">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror

                    <p class="text-center text-muted mb-4" style="font-size:0.8rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Masukkan 6 digit kode yang dikirim ke email Anda
                    </p>

                    {{-- Submit button (auto-submit saat digit ke-6 terisi) --}}
                    <div class="d-grid mb-3">
                        <button type="submit" id="btn-verify"
                                class="btn btn-success btn-lg fw-semibold"
                                style="border-radius:10px;background:linear-gradient(135deg,#16a34a,#059669);border:none;">
                            <span id="btn-verify-text">
                                <i class="bi bi-shield-check me-2"></i>Verifikasi Akun
                            </span>
                            <span id="btn-verify-loading" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Memverifikasi...
                            </span>
                        </button>
                    </div>
                </form>

                {{-- Resend OTP --}}
                <div class="text-center mb-3">
                    <form id="form-resend" action="{{ route('ppdb.resend-otp') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" id="btn-resend" class="btn btn-link text-success text-decoration-none p-0 fw-semibold" disabled>
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            Kirim Ulang Kode
                            <span id="resend-countdown" class="text-muted fw-normal"></span>
                        </button>
                    </form>
                </div>

                {{-- Link Ganti Email --}}
                <div class="text-center">
                    <form action="{{ route('ppdb.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted text-decoration-none p-0" style="font-size:0.8125rem">
                            <i class="bi bi-arrow-left me-1"></i>Gunakan email lain
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
    .portal-main { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%); }

    /* OTP Timer */
    .otp-timer-wrapper {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        background: #f0fdf4;
        border: 2px solid #bbf7d0;
        border-radius: 12px;
        padding: 10px 24px;
    }
    .otp-timer {
        font-size: 1.75rem;
        font-weight: 800;
        color: #15803d;
        font-variant-numeric: tabular-nums;
        letter-spacing: 2px;
        line-height: 1.2;
    }
    .otp-timer.expired { color: #dc2626; }
    .otp-timer-label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

    /* OTP Digit Boxes */
    .otp-digit {
        transition: border-color 0.2s, box-shadow 0.2s;
        border: 2px solid #d1fae5 !important;
        background: #f9fafb;
    }
    .otp-digit:focus {
        border-color: #16a34a !important;
        box-shadow: 0 0 0 3px rgba(22,163,74,0.2) !important;
        background: #fff;
        outline: none;
    }
    .otp-digit.filled {
        border-color: #16a34a !important;
        background: #f0fdf4;
    }
    .otp-digit.error {
        border-color: #ef4444 !important;
        background: #fff5f5;
        animation: shake 0.4s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25%       { transform: translateX(-4px); }
        75%       { transform: translateX(4px); }
    }
    .btn-success:hover { opacity: 0.92; transform: translateY(-1px); transition: all 0.2s; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    'use strict';

    const digits     = Array.from({ length: 6 }, (_, i) => document.getElementById('otp-' + i));
    const hiddenOtp  = document.getElementById('otp');
    const btnVerify  = document.getElementById('btn-verify');
    const btnResend  = document.getElementById('btn-resend');
    const form       = document.getElementById('form-otp');

    // ── OTP Digit Navigation ──────────────────────────────────────
    digits.forEach(function(input, idx) {
        input.addEventListener('keydown', function(e) {
            // Allow: Backspace, Tab, Arrow keys
            if (e.key === 'Backspace') {
                input.value = '';
                syncHidden();
                input.classList.remove('filled');
                if (idx > 0) digits[idx - 1].focus();
                e.preventDefault();
                return;
            }
            // Block non-numeric except nav keys
            if (!/^[0-9]$/.test(e.key) && !['Tab','ArrowLeft','ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', function() {
            // Sanitize: only digits
            input.value = input.value.replace(/\D/g, '').slice(-1);

            if (input.value) {
                input.classList.add('filled');
                syncHidden();
                if (idx < 5) {
                    digits[idx + 1].focus();
                } else {
                    // Last digit filled → auto submit
                    syncHidden();
                    if (hiddenOtp.value.length === 6) {
                        submitForm();
                    }
                }
            } else {
                input.classList.remove('filled');
                syncHidden();
            }
        });

        // Handle paste on any digit
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);
            pasted.split('').forEach(function(char, i) {
                if (digits[i]) {
                    digits[i].value = char;
                    digits[i].classList.add('filled');
                }
            });
            syncHidden();
            const focusIdx = Math.min(pasted.length, 5);
            digits[focusIdx].focus();
            if (pasted.length === 6) submitForm();
        });

        input.addEventListener('focus', function() {
            input.select();
        });
    });

    // Focus on first digit
    if (digits[0]) digits[0].focus();

    function syncHidden() {
        hiddenOtp.value = digits.map(d => d.value).join('');
    }

    function submitForm() {
        syncHidden();
        btnVerify.disabled = true;
        document.getElementById('btn-verify-text').classList.add('d-none');
        document.getElementById('btn-verify-loading').classList.remove('d-none');
        form.submit();
    }

    form.addEventListener('submit', function() {
        syncHidden();
        btnVerify.disabled = true;
        document.getElementById('btn-verify-text').classList.add('d-none');
        document.getElementById('btn-verify-loading').classList.remove('d-none');
    });

    // ── OTP Countdown Timer (10 menit) ───────────────────────────
    let totalSeconds = 600; // 10 menit
    const timerEl    = document.getElementById('otp-timer');

    const timerInterval = setInterval(function() {
        totalSeconds--;
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            timerEl.textContent = '00:00';
            timerEl.classList.add('expired');
            // Disable form digits
            digits.forEach(d => d.disabled = true);
            btnVerify.disabled = true;
            return;
        }
        const m = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        const s = String(totalSeconds % 60).padStart(2, '0');
        timerEl.textContent = m + ':' + s;
    }, 1000);

    // ── Resend Button Cooldown (60 detik) ─────────────────────────
    let resendCooldown       = 60;
    const resendCountdownEl  = document.getElementById('resend-countdown');
    resendCountdownEl.textContent = '(' + resendCooldown + 'd)';

    const resendInterval = setInterval(function() {
        resendCooldown--;
        if (resendCooldown <= 0) {
            clearInterval(resendInterval);
            btnResend.disabled          = false;
            resendCountdownEl.textContent = '';
        } else {
            resendCountdownEl.textContent = '(' + resendCooldown + 'd)';
        }
    }, 1000);

    // ── Shake animation on validation error ──────────────────────
    @error('otp')
        digits.forEach(d => d.classList.add('error'));
        setTimeout(() => digits.forEach(d => d.classList.remove('error')), 500);
    @enderror
})();
</script>
@endpush
