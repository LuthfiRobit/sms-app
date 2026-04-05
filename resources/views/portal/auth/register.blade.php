@extends('layouts.portal')

@section('title', 'Daftar Akun Peserta')

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

        <div class="portal-card">
            {{-- Card Header --}}
            <div class="card-header border-0 rounded-top-4 py-4 px-4"
                 style="background:linear-gradient(135deg,#16a34a 0%,#059669 100%)">
                <div class="text-center text-white">
                    <div class="mb-2" style="font-size:2.5rem">🎓</div>
                    <h1 class="fs-5 fw-bold mb-1">Daftar Akun Peserta</h1>
                    <p class="mb-0 opacity-75" style="font-size:0.8125rem">
                        PPDB 2026/2027 — Portal Pendaftaran Online
                    </p>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="card-body px-4 py-4">
                <form id="form-register" action="{{ route('ppdb.register') }}" method="POST" novalidate>
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label fw-semibold" style="font-size:0.875rem">
                            Nama Lengkap
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input
                                type="text"
                                id="nama_lengkap"
                                name="nama_lengkap"
                                class="form-control border-start-0 ps-0 @error('nama_lengkap') is-invalid @enderror"
                                placeholder="Masukkan nama lengkap Anda"
                                value="{{ old('nama_lengkap') }}"
                                autocomplete="name"
                                autofocus
                            >
                            @error('nama_lengkap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

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
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text" style="font-size:0.75rem">
                            <i class="bi bi-info-circle me-1"></i>
                            Email ini akan digunakan sebagai username login Anda.
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold" style="font-size:0.875rem">
                            Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
                            >
                            <button class="btn btn-light border toggle-password" type="button"
                                    data-target="#password" title="Tampilkan/Sembunyikan">
                                <i class="bi bi-eye text-muted" id="icon-password"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold" style="font-size:0.875rem">
                            Konfirmasi Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock-fill text-muted"></i>
                            </span>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control border-start-0 border-end-0 ps-0"
                                placeholder="Ulangi password Anda"
                                autocomplete="new-password"
                            >
                            <button class="btn btn-light border toggle-password" type="button"
                                    data-target="#password_confirmation" title="Tampilkan/Sembunyikan">
                                <i class="bi bi-eye text-muted" id="icon-password-confirm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Syarat & Ketentuan --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input
                                class="form-check-input @error('setuju_syarat') is-invalid @enderror"
                                type="checkbox"
                                id="setuju_syarat"
                                name="setuju_syarat"
                                value="1"
                                {{ old('setuju_syarat') ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="setuju_syarat" style="font-size:0.8125rem">
                                Saya menyetujui
                                <a href="#" class="text-success fw-semibold">syarat dan ketentuan</a>
                                pendaftaran PPDB 2026/2027
                            </label>
                            @error('setuju_syarat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-grid mb-3">
                        <button type="submit" id="btn-register"
                                class="btn btn-success btn-lg fw-semibold"
                                style="border-radius:10px;background:linear-gradient(135deg,#16a34a,#059669);border:none;letter-spacing:0.3px">
                            <span id="btn-register-text">
                                <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                            </span>
                            <span id="btn-register-loading" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Memproses...
                            </span>
                        </button>
                    </div>
                </form>

                {{-- Link Login --}}
                <div class="text-center">
                    <span class="text-muted" style="font-size:0.875rem">Sudah punya akun?</span>
                    <a href="{{ route('ppdb.login') }}" class="ms-1 fw-semibold text-success text-decoration-none">
                        Login di sini <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1 text-success"></i>
                Data Anda aman dan terenkripsi
            </small>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .portal-main { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%); }
    .form-control:focus, .input-group-text { border-color: #86efac; }
    .form-control:focus { box-shadow: 0 0 0 3px rgba(22,163,74,0.15); }
    .form-check-input:checked { background-color: #16a34a; border-color: #16a34a; }
    .input-group .form-control.is-invalid { z-index: 0; }
    .btn-success:hover { opacity: 0.92; transform: translateY(-1px); transition: all 0.2s; }
</style>
@endpush

@push('scripts')
<script>
    // Toggle show/hide password
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target  = document.querySelector(this.dataset.target);
            const icon    = this.querySelector('i');
            const isPass  = target.type === 'password';
            target.type   = isPass ? 'text' : 'password';
            icon.classList.toggle('bi-eye',      !isPass);
            icon.classList.toggle('bi-eye-slash', isPass);
        });
    });

    // Loading state on submit
    document.getElementById('form-register').addEventListener('submit', function() {
        const btn     = document.getElementById('btn-register');
        const text    = document.getElementById('btn-register-text');
        const loading = document.getElementById('btn-register-loading');
        btn.disabled  = true;
        text.classList.add('d-none');
        loading.classList.remove('d-none');
    });
</script>
@endpush
