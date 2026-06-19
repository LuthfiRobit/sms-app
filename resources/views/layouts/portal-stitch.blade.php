<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PPDB Online') — Marifat</title>
    <link rel="icon" href="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "outline": "#6e7a6e",
                        "on-primary": "#ffffff",
                        "outline-variant": "#bdcabb",
                        "on-surface-variant": "#3e4a3e",
                        "surface-container-lowest": "#ffffff",
                        "tertiary-fixed-dim": "#f6bd50",
                        "secondary-container": "#acf3b9",
                        "surface-container": "#eaf0e6",
                        "on-primary-container": "#e7ffe6",
                        "error": "#ba1a1a",
                        "on-surface": "#171d17",
                        "background": "#f5fbf1",
                        "surface": "#f5fbf1",
                        "primary": "#00682f",
                        "primary-container": "#00843d",
                        "surface-container-low": "#eff5ec",
                        "on-background": "#171d17",
                        "surface-container-high": "#e4eae0",
                        "tertiary": "#765400",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#fff8f1",
                        "tertiary-container": "#966b00",
                        "tertiary-fixed": "#ffdea7",
                        "inverse-surface": "#2c322c",
                        "surface-dim": "#d6dcd2",
                        "primary-fixed-dim": "#71dc8a",
                        "error-container": "#ffdad6",
                        "on-primary-fixed-variant": "#005324",
                        "secondary": "#286b3d",
                        "surface-container-highest": "#dee4db",
                        "secondary-fixed": "#acf3b9",
                        "on-error-container": "#93000a",
                        "on-primary-fixed": "#00210a",
                        "on-secondary-container": "#2f7143",
                        "secondary-fixed-dim": "#91d69f",
                        "on-error": "#ffffff",
                        "surface-bright": "#f5fbf1",
                        "inverse-on-surface": "#edf3e9",
                        "surface-variant": "#dee4db",
                        "inverse-primary": "#71dc8a",
                        "surface-tint": "#006d31",
                        "primary-fixed": "#8ef9a4",
                        "on-secondary-fixed": "#00210c",
                        "on-tertiary-fixed": "#271900",
                        "on-secondary": "#ffffff",
                        "on-secondary-fixed-variant": "#075228",
                        "on-tertiary-fixed-variant": "#5e4200"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "margin-desktop": "48px",
                        "stack-sm": "8px",
                        "gutter": "24px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "container-max": "1200px",
                        "margin-mobile": "16px",
                        "unit": "8px"
                    },
                    fontFamily: {
                        "headline-sm": ["Plus Jakarta Sans"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "700"}],
                        "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "500"}],
                        "label-md": ["14px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "display-lg": ["48px", {"lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "800"}]
                    }
                }
            }
        }
    </script>

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            user-select: none;
            vertical-align: middle;
        }
        .soft-shadow { box-shadow: 0 8px 32px rgba(0,0,0,.08); }
        .islamic-pattern {
            background-color: #f5fbf1;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l10 20 20 10-20 10-10 20-10-20L0 30l20-10L30 0z' fill='%2300682f' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
        }
        .pattern-bg-auth {
            background-color: #eff5ec;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cg fill='none' stroke='%2300682f' stroke-opacity='0.07' stroke-width='0.8'%3E%3Cpath d='M30 0l30 30-30 30L0 30Z'/%3E%3Cpath d='M30 12l18 18-18 18-18-18Z'/%3E%3Cline x1='0' y1='30' x2='60' y2='30'/%3E%3Cline x1='30' y1='0' x2='30' y2='60'/%3E%3C/g%3E%3C/svg%3E");
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #bdcabb; border-radius: 9999px; }
    </style>

    @stack('styles')
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col antialiased islamic-pattern">

    @yield('navbar')

    @yield('content')

    @hasSection('footer')
        @yield('footer')
    @else
    <footer class="bg-surface-container-highest text-on-surface-variant border-t-2 border-tertiary-fixed mt-auto">
        <div class="flex flex-col md:flex-row justify-between items-center px-4 md:px-8 py-stack-lg w-full max-w-[1440px] mx-auto gap-4">
            <div class="text-headline-sm text-primary font-bold">Marifat</div>
            <div class="flex gap-6">
                <a href="#" class="text-body-sm text-on-surface-variant opacity-80 hover:text-primary transition-colors">Kebijakan Privasi</a>
                <a href="#" class="text-body-sm text-on-surface-variant opacity-80 hover:text-primary transition-colors">Syarat &amp; Ketentuan</a>
                <a href="#" class="text-body-sm text-on-surface-variant opacity-80 hover:text-primary transition-colors">Kontak Kami</a>
            </div>
            <div class="text-body-sm text-on-surface-variant opacity-80 text-center md:text-right">
                &copy; {{ date('Y') }} Marifat — LP Ma'arif NU Kraksaan. All Rights Reserved.
            </div>
        </div>
    </footer>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>
</html>
