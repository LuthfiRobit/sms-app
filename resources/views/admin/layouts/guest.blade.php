<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>@yield('title', 'Login - Sistem Sekolah')</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Sistem Informasi Manajemen Sekolah Terpadu" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('assets/sekolah-refaktor-template/images/favicon.svg') }}" type="image/x-icon" />
    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        id="main-font-link" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/style-preset.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/css/custom-style.css') }}" />
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
        <div class="auth-wrapper v1">
            <div class="auth-form">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- [Required Js] -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/script.js') }}"></script>
    <script src="{{ asset('assets/sekolah-refaktor-template/js/theme.js') }}"></script>
</body>

</html>
