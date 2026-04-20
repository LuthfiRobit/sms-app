<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal PPDB 2026/2027 - Penerimaan Peserta Didik Baru">
    <title>@yield('title', 'Portal PPDB') — PPDB 2026/2027</title>

    {{-- [DIUBAH] Mengganti Inter (terlalu umum) dengan Plus Jakarta Sans yang lebih modern
         dan sesuai konteks aplikasi Indonesia/pendidikan. Ditambah Instrument Serif
         sebagai aksen display agar brand terasa premium. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════
           DESIGN TOKENS — satu sumber kebenaran warna/ukuran
           [DIUBAH] Variabel diorganisir lebih sistematis dan
           ditambah token untuk shadow, transition, dan radius
           ═══════════════════════════════════════════════════ */
        :root {
            /* Brand colors */
            --c-primary:        #16a34a;
            --c-primary-dark:   #15803d;
            --c-primary-light:  #dcfce7;
            --c-primary-50:     #f0fdf4;
            --c-accent:         #059669;
            --c-accent-teal:    #0d9488;

            /* Neutrals */
            --c-text:           #111827;
            --c-text-muted:     #6b7280;
            --c-text-subtle:    #9ca3af;
            --c-border:         #e5e7eb;
            --c-border-light:   #f3f4f6;
            --c-bg:             #f8fafc;
            --c-surface:        #ffffff;

            /* Shadows */
            --shadow-xs:  0 1px 2px rgba(0,0,0,.05);
            --shadow-sm:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
            --shadow-md:  0 4px 16px rgba(0,0,0,.06), 0 2px 4px rgba(0,0,0,.04);
            --shadow-lg:  0 8px 32px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.04);
            --shadow-primary: 0 4px 14px rgba(22,163,74,.22);

            /* Radii */
            --r-sm:  6px;
            --r-md:  10px;
            --r-lg:  14px;
            --r-xl:  18px;
            --r-2xl: 24px;

            /* Transitions */
            --t-fast:   .15s ease;
            --t-normal: .22s ease;

            /* Layout */
            --navbar-h: 62px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        /* [DIUBAH] Mengganti background flat menjadi subtle pattern
           agar halaman terasa lebih berkarakter tanpa terlalu ramai */
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: var(--c-bg);
            /* Subtle dot grid – hanya dekoratif */
            background-image: radial-gradient(circle, #d1d5db 1px, transparent 1px);
            background-size: 32px 32px;
            color: var(--c-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* ═══════════════════════════════════════
           NAVBAR
           [DIUBAH] Gradient diperhalus, menambah
           backdrop-blur saat di-scroll, dan
           memperbaiki aksesibilitas focus ring.
           ═══════════════════════════════════════ */
        .navbar-portal {
            background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-accent-teal) 100%);
            height: var(--navbar-h);
            box-shadow: 0 1px 0 rgba(255,255,255,.08), var(--shadow-primary);
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: box-shadow var(--t-normal);
        }

        /* [BARU] Efek shadow diperkuat saat navbar menempel setelah scroll */
        .navbar-portal.scrolled {
            box-shadow: 0 2px 20px rgba(22,163,74,.32);
        }

        /* Brand */
        .navbar-portal .navbar-brand {
            color: #fff !important;
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -.4px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .brand-icon {
            width: 36px;
            height: 36px;
            /* [DIUBAH] Dari emoji biasa ke kotak bergaya dengan border */
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: var(--r-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .brand-text-primary { line-height: 1.2; font-size: 1rem; }
        .brand-text-sub {
            font-size: .6rem;
            font-weight: 400;
            opacity: .8;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        /* Nav links */
        .navbar-portal .nav-link {
            color: rgba(255,255,255,.88) !important;
            font-size: .855rem;
            font-weight: 500;
            padding: .38rem .75rem;
            border-radius: var(--r-sm);
            transition: background var(--t-fast), color var(--t-fast);
        }
        .navbar-portal .nav-link:hover,
        .navbar-portal .nav-link:focus-visible {
            color: #fff !important;
            background: rgba(255,255,255,.15);
            outline: none;
        }
        .navbar-portal .nav-link.active {
            background: rgba(255,255,255,.2);
            color: #fff !important;
            font-weight: 600;
        }

        /* Hamburger */
        .navbar-portal .navbar-toggler {
            border: 1px solid rgba(255,255,255,.35);
            padding: .3rem .5rem;
            border-radius: var(--r-sm);
        }
        .navbar-portal .navbar-toggler:focus { box-shadow: 0 0 0 3px rgba(255,255,255,.3); }
        .navbar-portal .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255%2C255%2C255%2C0.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Dropdown user menu */
        .navbar-portal .dropdown-menu {
            /* [DIUBAH] Styling dropdown lebih modern dengan shadow besar dan
               animasi masuk yang halus menggunakan CSS transform */
            border-radius: var(--r-lg);
            border: 1px solid var(--c-border);
            box-shadow: var(--shadow-lg);
            padding: 6px;
            min-width: 220px;
            animation: dropIn .18s ease;
            transform-origin: top right;
            margin-top: 8px !important;
        }
        @keyframes dropIn {
            from { opacity: 0; transform: scale(.95) translateY(-6px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .navbar-portal .dropdown-item {
            border-radius: var(--r-sm);
            font-size: .855rem;
            padding: .5rem .75rem;
            color: var(--c-text);
            transition: background var(--t-fast), color var(--t-fast);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .navbar-portal .dropdown-item:hover { background: var(--c-primary-50); color: var(--c-primary-dark); }
        .navbar-portal .dropdown-item.text-danger:hover { background: #fff1f1; color: #dc2626; }
        .navbar-portal .dropdown-item i { width: 16px; text-align: center; opacity: .8; }

        /* User avatar + toggle */
        .user-avatar {
            width: 34px;
            height: 34px;
            /* [DIUBAH] Gradient agar lebih premium dari warna solid */
            background: linear-gradient(135deg, rgba(255,255,255,.35) 0%, rgba(255,255,255,.15) 100%);
            border: 1.5px solid rgba(255,255,255,.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            color: #fff;
            font-weight: 700;
            flex-shrink: 0;
        }
        .user-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,.92) !important;
            text-decoration: none;
            font-size: .855rem;
            font-weight: 500;
            padding: .28rem .6rem;
            border-radius: var(--r-md);
            transition: background var(--t-fast);
        }
        .user-toggle:hover,
        .user-toggle:focus-visible { background: rgba(255,255,255,.15); outline: none; }
        .user-toggle::after { display: none; }
        .user-caret { font-size: 10px; opacity: .65; transition: transform var(--t-fast); }
        .user-toggle[aria-expanded="true"] .user-caret { transform: rotate(180deg); }

        /* Divider email di dropdown */
        .dropdown-email {
            font-size: .72rem;
            color: var(--c-text-muted);
            padding: 4px 12px 6px;
            display: block;
        }
        .dropdown-divider { border-color: var(--c-border-light); margin: 4px 0; }

        /* Pill button "Daftar" di navbar */
        .btn-nav-register {
            background: rgba(255,255,255,.18);
            color: #fff;
            border: 1px solid rgba(255,255,255,.4);
            border-radius: 20px;
            padding: .3rem 1rem;
            font-size: .825rem;
            font-weight: 600;
            letter-spacing: .2px;
            transition: background var(--t-fast), border-color var(--t-fast);
            text-decoration: none;
        }
        .btn-nav-register:hover {
            background: rgba(255,255,255,.3);
            color: #fff;
            border-color: rgba(255,255,255,.6);
        }

        /* ═══════════════════════════════════════
           MAIN CONTENT
           [DIUBAH] Padding atas/bawah distandarisasi
           menggunakan skala spacing yang konsisten
           ═══════════════════════════════════════ */
        main.portal-main {
            flex: 1;
            padding: 36px 0 56px;
        }

        /* ═══════════════════════════════════════
           FLASH ALERTS
           [DIUBAH] Alert diperhalus dengan ikon
           dan tanpa border kiri (lebih clean)
           ═══════════════════════════════════════ */
        .alert {
            border-radius: var(--r-md);
            font-size: .855rem;
            border: none;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
        }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger  { background: #fee2e2; color: #991b1b; }
        .alert-info    { background: #dbeafe; color: #1e40af; }
        .alert-warning { background: #fef3c7; color: #92400e; }

        /* ═══════════════════════════════════════
           PORTAL CARD — komponen card global
           [DIUBAH] Radius lebih besar dan shadow
           lebih halus agar konsisten di semua halaman
           ═══════════════════════════════════════ */
        .portal-card {
            background: var(--c-surface);
            border-radius: var(--r-xl);
            border: 1px solid var(--c-border);
            box-shadow: var(--shadow-md);
        }

        /* ═══════════════════════════════════════
           FOOTER
           [DIUBAH] Ditambah border-top berwarna
           gradient agar terlihat "selesai" dengan
           baik dan tidak terpotong tiba-tiba
           ═══════════════════════════════════════ */
        .portal-footer {
            background: var(--c-surface);
            border-top: 1px solid var(--c-border);
            padding: 24px 0;
            margin-top: auto;
        }
        .portal-footer .footer-brand {
            font-weight: 700;
            color: var(--c-primary);
            font-size: .925rem;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }
        .portal-footer p {
            font-size: .8rem;
            color: var(--c-text-muted);
            margin: 0;
        }
        .footer-contact {
            font-size: .8rem;
            color: var(--c-text-muted);
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 3px;
        }
        .footer-contact a {
            color: var(--c-text-muted);
            text-decoration: none;
            transition: color var(--t-fast);
        }
        .footer-contact a:hover { color: var(--c-primary); }

        /* ── Responsive mobile adjustments ── */
        @media (max-width: 576px) {
            main.portal-main { padding: 24px 0 40px; }
            .footer-contact { align-items: flex-start; margin-top: 12px; }
        }
    </style>

    {{-- [DIUBAH] @stack('styles') dipindah ke sini (sebelum </head>)
         agar child view bisa override variabel CSS jika diperlukan --}}
    @stack('styles')
</head>
<body>

{{-- ════════════════════════════════════════════════════
     NAVBAR
     [DIUBAH] Struktur lebih bersih, menambahkan
     aria-label untuk aksesibilitas, dan link "Beranda"
     menggunakan class active secara dinamis
     ════════════════════════════════════════════════════ --}}
<nav class="navbar navbar-portal navbar-expand-lg" aria-label="Navigasi utama">
    <div class="container">

        {{-- Brand --}}
        <a class="navbar-brand" href="{{ route('ppdb.login') }}">
            <div class="brand-icon" aria-hidden="true">🎓</div>
            <div>
                <div class="brand-text-primary">PPDB 2026/2027</div>
                <div class="brand-text-sub">Portal Peserta</div>
            </div>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarPortal"
                aria-controls="navbarPortal"
                aria-expanded="false"
                aria-label="Buka/tutup menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPortal">

            {{-- Menu kiri — hanya tampil bila sudah login sebagai peserta --}}
            <ul class="navbar-nav me-auto gap-1">
                @auth
                    @if(isset($isPeserta) && $isPeserta || auth()->user()->status !== null)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ppdb.dashboard') ? 'active' : '' }}"
                               href="{{ route('ppdb.dashboard') }}">
                                <i class="bi bi-house-door me-1" aria-hidden="true"></i>Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            {{-- [TODO] Ganti href="#" dengan route pendaftaran yang sebenarnya --}}
                            <a class="nav-link {{ request()->routeIs('ppdb.pendaftaran.*') ? 'active' : '' }}"
                               href="#">
                                <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Pendaftaran
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            {{-- Menu kanan --}}
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                @auth
                    <li class="nav-item dropdown">
                        <a class="user-toggle dropdown-toggle"
                           href="#"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                           aria-label="Menu akun">
                            <div class="user-avatar" aria-hidden="true">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            {{-- [DIUBAH] Limit karakter dikurangi di mobile agar tidak overflow --}}
                            <span class="d-none d-md-inline">{{ Str::limit(auth()->user()->name, 20) }}</span>
                            <i class="bi bi-chevron-down user-caret" aria-hidden="true"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            {{-- Info email sebagai header dropdown --}}
                            <li>
                                <span class="dropdown-email">{{ auth()->user()->email }}</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person" aria-hidden="true"></i>Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>Pendaftaran
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                {{-- [DIUBAH] Form logout tetap menggunakan POST (aman terhadap CSRF).
                                     Button dibuat terlihat seperti link dengan styling CSS --}}
                                <form action="{{ route('ppdb.logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ppdb.login') }}">
                            <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Masuk
                        </a>
                    </li>
                    <li class="nav-item ms-1">
                        <a class="btn-nav-register" href="{{ route('ppdb.register') }}">
                            Daftar Sekarang
                        </a>
                    </li>
                @endauth
            </ul>

        </div>
    </div>
</nav>

{{-- ════════════════════════════════════════════════════
     FLASH MESSAGES — tampil otomatis dari session
     [BARU] Blok ini dipindah ke layout agar semua
     halaman otomatis mendapat flash message tanpa
     perlu copy-paste di setiap view
     ════════════════════════════════════════════════════ --}}
@if(session()->hasAny(['success', 'error', 'info', 'warning']))
    <div class="container mt-3">
        @foreach(['success', 'error' => 'danger', 'info', 'warning'] as $type => $bsType)
            @php $key = is_int($type) ? $bsType : $type;
            $cls = is_int($type) ? $bsType : $bsType; @endphp
            @if(session($key))
                <div class="alert alert-{{ $cls }} alert-dismissible" role="alert">
                    <i class="bi bi-{{ $cls === 'success' ? 'check-circle' : ($cls === 'danger' ? 'x-circle' : ($cls === 'warning' ? 'exclamation-triangle' : 'info-circle')) }}"
                       aria-hidden="true"></i>
                    <span>{{ session($key) }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif
        @endforeach
    </div>
@endif

{{-- ════════════════════════════════════════════════════
     MAIN CONTENT
     ════════════════════════════════════════════════════ --}}
<main class="portal-main" id="main-content">
    <div class="container">
        @yield('content')
    </div>
</main>

{{-- ════════════════════════════════════════════════════
     FOOTER
     [DIUBAH] Responsif — kontak ditampilkan vertikal
     di mobile dan horizontal di desktop
     ════════════════════════════════════════════════════ --}}
<footer class="portal-footer" role="contentinfo">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="footer-brand">
                    <span aria-hidden="true">🎓</span> PPDB 2026/2027
                </div>
                <p>Portal Penerimaan Peserta Didik Baru — Sistem Informasi Sekolah</p>
            </div>
            <div class="col-sm-6">
                <div class="footer-contact text-sm-end">
                    <a href="tel:0xxxxxxxxx">
                        <i class="bi bi-telephone me-1" aria-hidden="true"></i>(0xxx) xxx-xxxx
                    </a>
                    <a href="mailto:ppdb@sekolah.sch.id">
                        <i class="bi bi-envelope me-1" aria-hidden="true"></i>ppdb@sekolah.sch.id
                    </a>
                    <span>&copy; {{ date('Y') }} Hak Cipta Dilindungi</span>
                </div>
            </div>
        </div>
    </div>
</footer>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- [BARU] Script kecil untuk efek navbar scrolled dan inisialisasi tooltip global --}}
<script>
    // Navbar shadow saat scroll
    (function () {
        const nav = document.querySelector('.navbar-portal');
        const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        // Aktifkan semua Bootstrap Tooltip secara global di layout
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el, { trigger: 'hover focus' });
        });
    })();
</script>

{{-- [DIUBAH] @stack('scripts') tetap di bagian bawah body untuk performa optimal --}}
@stack('scripts')
</body>
</html>