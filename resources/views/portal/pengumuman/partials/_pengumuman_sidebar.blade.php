<div class="profil-sidebar">
    <div class="profil-nav">
        <a href="{{ route('ppdb.dashboard') }}" class="profil-nav-item">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="#biaya" class="profil-nav-item active">
            <i class="bi bi-megaphone-fill"></i>
            <span>Status & Pengumuman</span>
        </a>
        <a href="{{ route('ppdb.pendaftaran.index') }}" class="profil-nav-item">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>Detail Pendaftaran</span>
        </a>
    </div>

    <div class="mt-4 p-3 rounded-4 bg-light border">
        <h6 class="fw-bold small mb-2"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Informasi</h6>
        <p class="x-small text-muted mb-0">Halaman ini menampilkan status seleksi dari setiap jalur pendaftaran yang Anda ikuti.</p>
    </div>
</div>
