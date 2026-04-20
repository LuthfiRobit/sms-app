<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal PPDB 2026/2027 - Penerimaan Peserta Didik Baru">
    <title>@yield('title', 'Portal PPDB') — PPDB 2026/2027</title>

    {{-- Typography: Plus Jakarta Sans untuk body, Instrument Serif untuk aksen premium --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Instrument+Serif:ital@0;1&display=swap"
        rel="stylesheet">

    {{-- Bootstrap 5 + Icons + SweetAlert2 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @include('layouts.partials._style')
    {{-- Stack untuk CSS tambahan dari child pages --}}
    @stack('styles')
</head>

<body>

    {{-- Skip to content link untuk screen readers dan keyboard navigation --}}
    <a href="#main-content" class="skip-to-content">Skip to main content</a>

    {{-- NAVIGATION BAR
    Navbar sticky dengan branding, menu, dan user authentication --}}
    <nav class="navbar navbar-expand-md navbar-portal" role="navigation" aria-label="Main navigation">
        <div class="container">
            {{-- Brand/Logo --}}
            <a class="navbar-brand" href="{{ route('ppdb.dashboard') }}" aria-label="PPDB 2026/2027 - Homepage">
                <div class="brand-icon" aria-hidden="true">🎓</div>
                <div class="brand-text">
                    <div class="brand-text-primary">PPDB Online</div>
                    <div class="brand-text-sub">TA 2026/2027</div>
                </div>
            </a>

            {{-- Hamburger button untuk mobile --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPortal"
                aria-controls="navbarPortal" aria-expanded="false" aria-label="Toggle navigation menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Collapsible menu --}}
            <div class="collapse navbar-collapse" id="navbarPortal">

                {{-- Left menu: Dashboard & Pendaftaran (hanya untuk peserta yang sudah login) --}}
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
                                {{-- TODO: Ganti href="#" dengan route pendaftaran yang sebenarnya --}}
                                <a class="nav-link {{ request()->routeIs('ppdb.pendaftaran.*') ? 'active' : '' }}" href="#"
                                    aria-current="{{ request()->routeIs('ppdb.pendaftaran.*') ? 'page' : 'false' }}">
                                    <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>
                                    <span>Pendaftaran</span>
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                {{-- Right menu: User dropdown atau Login/Register --}}
                <ul class="navbar-nav ms-auto align-items-md-center">
                    @auth
                        {{-- User dropdown menu --}}
                        <li class="nav-item dropdown">
                            <a class="user-toggle dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-haspopup="true" aria-label="User menu">
                                <div class="user-avatar" aria-hidden="true">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                {{-- Show name only on desktop untuk space efficiency --}}
                                <span class="d-none d-md-inline">{{ Str::limit(auth()->user()->name, 20) }}</span>
                                <i class="bi bi-chevron-down user-caret" aria-hidden="true"></i>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">
                                {{-- Email header --}}
                                <li>
                                    <span class="dropdown-email" aria-label="Current user email">
                                        {{ auth()->user()->email }}
                                    </span>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                {{-- Profile link --}}
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-person" aria-hidden="true"></i>
                                        <span>Profil Saya</span>
                                    </a>
                                </li>

                                {{-- Pendaftaran link --}}
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                                        <span>Pendaftaran</span>
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                {{-- Logout button (tetap menggunakan POST form untuk security) --}}
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
                        {{-- Login link --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('ppdb.login') }}">
                                <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>
                                <span>Masuk</span>
                            </a>
                        </li>

                        {{-- Register CTA button --}}
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

    {{-- FLASH MESSAGES
    Alert notifications dari session (success, error, warning, info)
    Otomatis muncul setelah redirect dengan flash message --}}
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

    {{-- MAIN CONTENT
    Area konten utama - diisi oleh child pages via @yield('content') --}}
    <main class="portal-main" id="main-content" role="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- FOOTER
    Footer dengan branding, info kontak, dan copyright --}}
    <footer class="portal-footer" role="contentinfo">
        <div class="container">
            <div class="row g-4 align-items-center">
                {{-- Left column: Branding & description --}}
                <div class="col-sm-6">
                    <div class="footer-brand">
                        <span aria-hidden="true">🎓</span>
                        PPDB 2026/2027
                    </div>
                    <p class="mb-0">
                        Portal Penerimaan Peserta Didik Baru<br>
                        Sistem Informasi Sekolah
                    </p>
                </div>

                {{-- Right column: Contact info --}}
                <div class="col-sm-6">
                    <div class="footer-contact text-sm-end">
                        <a href="tel:0xxxxxxxxx" aria-label="Phone number">
                            <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                            (0xxx) xxx-xxxx
                        </a>
                        <a href="mailto:ppdb@sekolah.sch.id" aria-label="Email address">
                            <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                            ppdb@sekolah.sch.id
                        </a>
                        <span class="d-block mt-2">&copy; {{ date('Y') }} Hak Cipta Dilindungi</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- JAVASCRIPT LIBRARIES
    ════════════════════════════════════════════════════════════════════════ --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- CUSTOM SCRIPTS
    Navbar scroll effect dan Bootstrap tooltip initialization --}}
    <script>
        (function () {
            'use strict';

            // 1️⃣ Navbar shadow enhancement on scroll
            const navbar = document.querySelector('.navbar-portal');
            const handleScroll = () => {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            };

            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // Initial check

            // 2️⃣ Initialize Bootstrap tooltips globally
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el, {
                trigger: 'hover focus',
                delay: { show: 300, hide: 100 }
            }));

            // 3️⃣ Auto-close mobile menu after clicking a link
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            const navbarCollapse = document.getElementById('navbarPortal');

            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                    }
                });
            });

            // 4️⃣ Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });
        })();
    </script>

    {{-- Stack untuk JavaScript tambahan dari child pages --}}
    @stack('scripts')

</body>

</html>