<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal PPDB 2026/2027 - Penerimaan Peserta Didik Baru">
    <title>@yield('title', 'Portal PPDB') — PPDB 2026/2027</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --ppdb-primary:      #16a34a;
            --ppdb-primary-dark: #15803d;
            --ppdb-primary-light:#dcfce7;
            --ppdb-accent:       #059669;
            --ppdb-text:         #1e293b;
            --ppdb-muted:        #64748b;
            --ppdb-bg:           #f0fdf4;
            --ppdb-card-bg:      #ffffff;
            --navbar-height:     64px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--ppdb-bg);
            color: var(--ppdb-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Navbar ── */
        .navbar-portal {
            background: linear-gradient(135deg, var(--ppdb-primary) 0%, var(--ppdb-accent) 100%);
            height: var(--navbar-height);
            box-shadow: 0 2px 12px rgba(22,163,74,0.25);
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .navbar-portal .navbar-brand {
            color: #ffffff !important;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-portal .brand-icon {
            width: 34px;
            height: 34px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .navbar-portal .nav-link {
            color: rgba(255,255,255,0.88) !important;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .navbar-portal .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255,255,255,0.15);
        }
        .navbar-portal .navbar-toggler {
            border: 1px solid rgba(255,255,255,0.4);
            color: #ffffff;
        }
        .navbar-portal .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255%2C255%2C255%2C0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .navbar-portal .dropdown-menu {
            border-radius: 10px;
            border: none;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            padding: 8px;
            min-width: 200px;
        }
        .navbar-portal .dropdown-item {
            border-radius: 6px;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            color: var(--ppdb-text);
            transition: background 0.15s;
        }
        .navbar-portal .dropdown-item:hover {
            background: var(--ppdb-primary-light);
            color: var(--ppdb-primary-dark);
        }
        .navbar-portal .dropdown-item.text-danger:hover {
            background: #fee2e2;
            color: #dc2626;
        }
        .navbar-portal .user-avatar {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
            font-weight: 600;
        }
        .user-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.92) !important;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .user-toggle:hover { background: rgba(255,255,255,0.15); }
        .user-toggle::after { display: none; } /* hide default caret */

        /* ── Main Content ── */
        main.portal-main {
            flex: 1;
            padding: 32px 0 48px;
        }

        /* ── Footer ── */
        .portal-footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 20px 0;
        }
        .portal-footer .footer-brand {
            font-weight: 700;
            color: var(--ppdb-primary);
            font-size: 0.95rem;
        }
        .portal-footer p {
            font-size: 0.8125rem;
            color: var(--ppdb-muted);
            margin: 0;
        }

        /* ── Global Card Style ── */
        .portal-card {
            background: var(--ppdb-card-bg);
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        /* ── Alert flash ── */
        .alert { border-radius: 10px; font-size: 0.875rem; border: none; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger  { background: #fee2e2; color: #991b1b; }
        .alert-info    { background: #dbeafe; color: #1e40af; }
        .alert-warning { background: #fef3c7; color: #92400e; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── NAVBAR ── --}}
<nav class="navbar navbar-portal navbar-expand-lg">
    <div class="container">
        {{-- Brand --}}
        <a class="navbar-brand" href="{{ route('ppdb.login') }}">
            <div class="brand-icon">🎓</div>
            <div>
                <div style="line-height:1.2">PPDB 2026/2027</div>
                <div style="font-size:0.65rem;font-weight:400;opacity:0.85">Portal Peserta</div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPortal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPortal">
            <ul class="navbar-nav me-auto">
                @auth
                    @if(isset($isPeserta) && $isPeserta || auth()->user()->status !== null)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('ppdb.dashboard') }}">
                                <i class="bi bi-house me-1"></i> Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-file-earmark-text me-1"></i> Pendaftaran
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    <li class="nav-item dropdown">
                        <a class="user-toggle dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="d-none d-md-inline">{{ Str::limit(auth()->user()->name, 18) }}</span>
                            <i class="bi bi-chevron-down" style="font-size:11px;opacity:0.7"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text text-muted" style="font-size:0.75rem;padding:4px 12px 8px;">
                                    {{ auth()->user()->email }}
                                </span>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person me-2"></i>Profil Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-file-earmark-text me-2"></i>Pendaftaran
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="{{ route('ppdb.logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ppdb.login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item ms-1">
                        <a class="btn btn-sm" href="{{ route('ppdb.register') }}"
                           style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.4);border-radius:8px;padding:5px 14px;">
                            Daftar
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- ── MAIN CONTENT ── --}}
<main class="portal-main">
    <div class="container">
        @yield('content')
    </div>
</main>

{{-- ── FOOTER ── --}}
<footer class="portal-footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="footer-brand">🎓 PPDB 2026/2027</div>
                <p>Portal Penerimaan Peserta Didik Baru — Sistem Informasi Sekolah</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>
                    <i class="bi bi-telephone me-1"></i> (0xxx) xxx-xxxx &nbsp;|&nbsp;
                    <i class="bi bi-envelope me-1"></i> ppdb@sekolah.sch.id
                </p>
                <p>&copy; {{ date('Y') }} Hak Cipta Dilindungi</p>
            </div>
        </div>
    </div>
</footer>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
