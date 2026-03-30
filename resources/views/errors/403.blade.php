<!DOCTYPE html>
<html lang="id">
<head>
    <title>403 Forbidden - Sistem Sekolah</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" />

    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/organisasi-template/assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/sekolah-refaktor-template/organisasi-template/assets/css/style-preset.css') }}" />
</head>
<body>
    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body text-center">
                        <i class="ti ti-lock text-danger" style="font-size: 4rem;"></i>
                        <h2 class="mt-4">403</h2>
                        <h4 class="text-muted">Akses Ditolak</h4>
                        <p class="text-muted mb-4">{{ $message ?? 'Anda tidak memiliki hak akses (permission) untuk membuka halaman ini.' }}</p>
                        
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="ti ti-home"></i> Kembali ke Dashboard
                        </a>
                        <br><br>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-danger">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
