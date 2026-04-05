@extends('layouts.portal')

@section('title', 'Login Peserta')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5" style="max-width:460px">

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
            <div class="card-header border-0 py-4 px-4 rounded-top-4"
                 style="background:linear-gradient(135deg,#16a34a 0%,#059669 100%)">
                <div class="text-center text-white">
                    <div class="mb-2" style="font-size:2.5rem">👋</div>
                    <h1 class="fs-5 fw-bold mb-1">Selamat Datang!</h1>
                    <p class="mb-0 opacity-75" style="font-size:0.8125rem">
                        Masuk ke Portal PPDB 2026/2027
                    </p>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="card-body px-4 py-4">
                <form id="form-login-peserta" action="{{ route('ppdb.login.post') }}" method="POST" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold" style="font-size:0.875rem">
                            Alamat Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                placeholder="nama@email.com"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                autofocus
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold mb-0" style="font-size:0.875rem">
                                Password
                            </label>
                            <a href="#" class="text-success text-decoration-none" style="font-size:0.8rem">
                                Lupa Password?
                            </a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                placeholder="Password Anda"
                                autocomplete="current-password"
                            >
                            <button class="btn btn-light border" type="button" id="toggle-password" title="Tampilkan/Sembunyikan">
                                <i class="bi bi-eye text-muted" id="icon-password"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-grid mb-3">
                        <button type="submit" id="btn-login"
                                class="btn btn-success btn-lg fw-semibold"
                                style="border-radius:10px;background:linear-gradient(135deg,#16a34a,#059669);border:none;letter-spacing:0.3px">
                            <span id="btn-login-text">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                            </span>
                            <span id="btn-login-loading" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Memproses...
                            </span>
                        </button>
                    </div>
                </form>

                {{-- Link Register --}}
                <div class="text-center">
                    <span class="text-muted" style="font-size:0.875rem">Belum punya akun?</span>
                    <a href="{{ route('ppdb.register') }}" class="ms-1 fw-semibold text-success text-decoration-none">
                        Daftar sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1 text-success"></i>
                Koneksi Anda aman dan terenkripsi
            </small>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .portal-main { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%); }
    .form-control:focus { border-color: #86efac; box-shadow: 0 0 0 3px rgba(22,163,74,0.15); }
    .btn-success:hover { opacity: 0.92; transform: translateY(-1px); transition: all 0.2s; }
    .input-group .form-control.is-invalid { z-index: 0; }
</style>
@endpush

@push('scripts')
<script>
    // Toggle show/hide password
    document.getElementById('toggle-password').addEventListener('click', function() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('icon-password');
        const isPass = input.type === 'password';
        input.type   = isPass ? 'text' : 'password';
        icon.classList.toggle('bi-eye',       !isPass);
        icon.classList.toggle('bi-eye-slash',  isPass);
    });

    // Loading state on submit
    document.getElementById('form-login-peserta').addEventListener('submit', function() {
        const btn     = document.getElementById('btn-login');
        const text    = document.getElementById('btn-login-text');
        const loading = document.getElementById('btn-login-loading');
        btn.disabled  = true;
        text.classList.add('d-none');
        loading.classList.remove('d-none');
    });
</script>
@endpush
