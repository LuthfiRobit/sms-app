<!DOCTYPE html>
<html lang="id">

<head>
    <title>Login Admin — LP Ma'arif NU Kraksaan</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Admin Panel SIMS Terpadu — LP Ma'arif NU Kraksaan" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- [Favicon] -->
    <link rel="icon" href="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}" type="image/png" />

    <!-- [Google Font] Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" />

    <!-- [Template CSS] -->
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/style-preset.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/custom-style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/fonts/tabler-icons.min.css') }}" />

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

        /* Islamic geometric background */
        .auth-main {
            background-color: #F0FAF4;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cg fill='none' stroke='%2300843D' stroke-opacity='0.06' stroke-width='0.7'%3E%3Cpath d='M30 4L56 30L30 56L4 30Z'/%3E%3Cpath d='M30 16L44 30L30 44L16 30Z'/%3E%3Cpath d='M4 30L56 30M30 4L30 56M10 10L50 50M50 10L10 50'/%3E%3C/g%3E%3C/svg%3E");
        }

        .auth-maarif-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-maarif-header img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 2px 6px rgba(0,132,61,.2));
        }

        .auth-maarif-header h2 {
            color: #00843D;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .auth-maarif-header p {
            color: #6b7280;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
        }

        .auth-maarif-ornam {
            text-align: center;
            color: #C8952A;
            opacity: 0.6;
            font-size: 0.55rem;
            letter-spacing: 6px;
            margin-bottom: 1.25rem;
        }

        /* Override card top border with gold accent */
        .auth-form .card {
            border-top: 3px solid #C8952A !important;
            border-radius: 16px;
        }
    </style>
</head>

<body>
    <!-- [ Pre-loader ] -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">

                        {{-- LP Ma'arif NU Header --}}
                        <div class="auth-maarif-header">
                            <img src="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}"
                                 alt="Logo LP Ma'arif NU Kraksaan">
                            <h2>LP Ma'arif NU Kraksaan</h2>
                            <p>SIMS Terpadu — Admin Panel</p>
                        </div>

                        <div class="auth-maarif-ornam" aria-hidden="true">✦ &nbsp; ✦ &nbsp; ✦</div>

                        <div class="row">
                            <div class="d-flex justify-content-center">
                                <div class="auth-header">
                                    <h2 class="text-secondary mt-2"><b>Selamat Datang! 👋</b></h2>
                                    <p class="f-16 mt-1">Silahkan masuk menggunakan akun Anda</p>
                                </div>
                            </div>
                        </div>

                        <!-- Login Form -->
                        <form id="form-login" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="login" name="login"
                                    placeholder="Email atau Username" autofocus required />
                                <label for="login">Email atau Username</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Password" required />
                                <label for="password">Password</label>
                            </div>

                            <div class="d-flex mt-1 justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input input-primary" type="checkbox" id="remember"
                                        name="remember" value="1" />
                                    <label class="form-check-label text-muted" for="remember">Ingat Saya</label>
                                </div>
                                <a href="javascript:void(0);">
                                    <h5 class="text-secondary">Lupa Password?</h5>
                                </a>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary" id="btn-login">
                                    <i class="ti ti-login me-1"></i>Login
                                </button>
                            </div>
                        </form>

                        <hr />
                        <h5 class="d-flex justify-content-center text-muted" style="font-size:0.875rem">
                            Belum memiliki akun?
                            <a href="javascript:void(0);" class="ms-2 text-primary">Hubungi Administrator</a>
                        </h5>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/script.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/theme.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('admin.partials._global_handler')

    <script>
        layout_change('light');
        layout_caption_change('true');
        layout_rtl_change('false');
        preset_change('preset-1');

        $(document).ready(function() {
            $('#form-login').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#btn-login');
                let originalText = btn.html();

                btn.prop('disabled', true).html('<i class="ti ti-loader"></i> Memproses...');

                let formData = new FormData(this);

                handleAjax(form.attr('action'), 'POST', formData, function(response) {
                    btn.html('<i class="ti ti-check"></i> Redirecting...');
                    if (response.data && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        window.location.href = "{{ route('admin.dashboard') }}";
                    }
                }, function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                });
            });
        });
    </script>
</body>

</html>
