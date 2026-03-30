<!DOCTYPE html>
<html lang="id">

<head>
    <title>Login - Sistem Sekolah</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Sistem Informasi Manajemen Sekolah Terpadu" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('assets/sekolah-refaktor-template/images/favicon.svg') }}" type="image/x-icon" />

    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        id="main-font-link" />

    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/style-preset.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/custom-style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/fonts/tabler-icons.min.css') }}" />
</head>

<body>
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <a href="javascript:void(0);" class="d-flex justify-content-center text-decoration-none">
                            <h3 class="m-0 fw-bold text-primary">Sistem Sekolah</h3>
                        </a>

                        <div class="row">
                            <div class="d-flex justify-content-center">
                                <div class="auth-header">
                                    <h2 class="text-secondary mt-5"><b>Selamat Datang! 👋</b></h2>
                                    <p class="f-16 mt-2">Silahkan masuk menggunakan akun Anda</p>
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
                                    <input class="form-check-input input-primary" type="checkbox" id="remember" name="remember" value="1" />
                                    <label class="form-check-label text-muted" for="remember">Ingat Saya</label>
                                </div>
                                <a href="javascript:void(0);">
                                    <h5 class="text-secondary">Lupa Password?</h5>
                                </a>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary" id="btn-login">Login</button>
                            </div>
                        </form>

                        <hr />
                        <h5 class="d-flex justify-content-center">Belum memiliki akun?
                            <a href="javascript:void(0);" class="ms-2">Hubungi Admin</a>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Js from Template -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/script.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/theme.js') }}"></script>
    
    <!-- SweetAlert2 (for AJAX response parsing) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('admin.partials._global_handler')

    <!-- Template Utils -->
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
                    // Success callback
                    btn.html('<i class="ti ti-check"></i> Redirecting...');
                    if (response.data && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        window.location.href = "{{ route('admin.dashboard') }}";
                    }
                }, function(xhr) {
                    // Error callback
                    btn.prop('disabled', false).html(originalText);
                });
            });
        });
    </script>
</body>

</html>
