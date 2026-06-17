<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal PPDB LP Ma'arif NU Kraksaan — Penerimaan Peserta Didik Baru">
    <title>@yield('title', 'Portal PPDB') — LP Ma'arif NU Kraksaan</title>

    {{-- Typography: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    {{-- Bootstrap 5 + Icons + SweetAlert2 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @include('layouts.partials._style')
    @stack('styles')
</head>

<body>

    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    {{-- NAVIGATION BAR --}}
    <nav class="navbar navbar-expand-md navbar-portal" role="navigation" aria-label="Main navigation">
        <div class="container">
            {{-- Brand / Logo --}}
            <a class="navbar-brand" href="{{ route('ppdb.dashboard') }}" aria-label="Portal PPDB LP Ma'arif NU Kraksaan">
                <div class="brand-logo-wrap" aria-hidden="true">
                    <img src="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}"
                         alt="Logo LP Ma'arif NU" class="brand-logo">
                </div>
                <div class="brand-text">
                    <div class="brand-text-primary">PPDB Online</div>
                    <div class="brand-text-sub">LP Ma'arif NU Kraksaan</div>
                </div>
            </a>

            {{-- Hamburger button --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPortal"
                aria-controls="navbarPortal" aria-expanded="false" aria-label="Toggle navigation menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Collapsible menu --}}
            <div class="collapse navbar-collapse" id="navbarPortal">

                {{-- Left menu --}}
                <ul class="navbar-nav me-auto mb-2 mb-md-0">
                    @auth
                        @if(isset($isPeserta) && $isPeserta || auth()->user()->status !== null)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('ppdb.dashboard') ? 'active' : '' }}"
                                    href="{{ route('ppdb.dashboard') }}"
                                    aria-current="{{ request()->routeIs('ppdb.dashboard') ? 'page' : 'false' }}">
                                    <i class="bi bi-house-door me-1" aria-hidden="true"></i>
                                    <span>Beranda</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('ppdb.pendaftaran.*') ? 'active' : '' }}" href="#"
                                    aria-current="{{ request()->routeIs('ppdb.pendaftaran.*') ? 'page' : 'false' }}">
                                    <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>
                                    <span>Pendaftaran</span>
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                {{-- Right menu --}}
                <ul class="navbar-nav ms-auto align-items-md-center">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="user-toggle dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-haspopup="true" aria-label="User menu">
                                <div class="user-avatar" aria-hidden="true">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="d-none d-md-inline">{{ Str::limit(auth()->user()->name, 20) }}</span>
                                <i class="bi bi-chevron-down user-caret" aria-hidden="true"></i>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <span class="dropdown-email" aria-label="Current user email">
                                        {{ auth()->user()->email }}
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-person" aria-hidden="true"></i>
                                        <span>Profil Saya</span>
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                                        <span>Pendaftaran</span>
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <form action="{{ route('ppdb.logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                            <span>Keluar</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('ppdb.login') }}">
                                <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>
                                <span>Masuk</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="btn-nav-register" href="{{ route('ppdb.register') }}">
                                Daftar Sekarang
                            </a>
                        </li>
                    @endauth
                </ul>

            </div>
        </div>
    </nav>

    {{-- FLASH MESSAGES --}}
    @if(session()->hasAny(['success', 'error', 'info', 'warning']))
        <div class="container mt-3">
            @foreach(['success', 'error' => 'danger', 'info', 'warning'] as $type => $bsType)
                @php
                    $key = is_int($type) ? $bsType : $type;
                    $cls = is_int($type) ? $bsType : $bsType;
                @endphp
                @if(session($key))
                    <div class="alert alert-{{ $cls }} alert-dismissible fade show" role="alert">
                        <i class="bi bi-{{ $cls === 'success' ? 'check-circle-fill' : ($cls === 'danger' ? 'x-circle-fill' : ($cls === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')) }}"
                            aria-hidden="true"></i>
                        <span>{{ session($key) }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close notification"></button>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- MAIN CONTENT --}}
    <main class="portal-main" id="main-content" role="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="portal-footer" role="contentinfo">
        <div class="container">
            <div class="footer-ornam" aria-hidden="true">✦ &nbsp; ✦ &nbsp; ✦</div>
            <div class="row g-4 align-items-start">
                {{-- Left: Branding --}}
                <div class="col-sm-7">
                    <div class="footer-brand">
                        <img src="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}"
                             alt="Logo LP Ma'arif NU" width="32" height="32">
                        <span>LP Ma'arif NU Kraksaan</span>
                    </div>
                    <p class="mb-0">
                        Lembaga Pendidikan Ma'arif Nahdlatul Ulama<br>
                        Portal Penerimaan Peserta Didik Baru (PPDB) Online
                    </p>
                </div>

                {{-- Right: Contact --}}
                <div class="col-sm-5">
                    <div class="footer-contact text-sm-end">
                        <a href="tel:03358410xx" aria-label="Nomor telepon sekolah">
                            <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                            (0335) 841-xxx
                        </a>
                        <a href="mailto:ppdb@maarif-kraksaan.sch.id" aria-label="Alamat email PPDB">
                            <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                            ppdb@maarif-kraksaan.sch.id
                        </a>
                        <a href="#" aria-label="Alamat sekolah">
                            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                            Kraksaan, Probolinggo, Jawa Timur
                        </a>
                        <span class="d-block mt-2">&copy; {{ date('Y') }} LP Ma'arif NU Kraksaan</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- JAVASCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function () {
            'use strict';

            // Navbar shadow on scroll
            const navbar = document.querySelector('.navbar-portal');
            const handleScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 10);
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();

            // Initialize Bootstrap tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el, { trigger: 'hover focus', delay: { show: 300, hide: 100 } });
            });

            // Auto-close mobile menu after link click
            const navbarCollapse = document.getElementById('navbarPortal');
            document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) bsCollapse.hide();
                    }
                });
            });

            // Auto-dismiss alerts after 5 seconds
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert).close(), 5000);
            });
        })();
    </script>

    @stack('scripts')

</body>

</html>
