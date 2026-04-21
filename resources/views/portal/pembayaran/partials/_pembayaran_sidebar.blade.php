<div class="profil-sidebar">
    <nav class="profil-nav" aria-label="Navigasi Pembayaran">
        <a href="#biaya" class="profil-nav-item active">
            <i class="bi bi-receipt"></i>
            <span class="nav-label">Rincian Biaya</span>
        </a>
        <a href="#metode" class="profil-nav-item">
            <i class="bi bi-lightning-charge-fill"></i>
            <span class="nav-label">Metode Pembayaran</span>
        </a>
        <a href="#manual" class="profil-nav-item">
            <i class="bi bi-bank"></i>
            <span class="nav-label">Konfirmasi Manual</span>
        </a>
        <a href="#panduan" class="profil-nav-item">
            <i class="bi bi-question-circle"></i>
            <span class="nav-label">Bantuan & Panduan</span>
        </a>
    </nav>

    <div class="mt-4 pt-4 border-top">
        <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id ?? 0) }}" class="btn-daftar">
            <i class="bi bi-arrow-left"></i>
            Detail Pendaftaran
        </a>
    </div>
</div>
