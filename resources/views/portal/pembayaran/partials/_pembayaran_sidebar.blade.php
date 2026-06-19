<div class="pay-sidebar">
    <div class="pay-sidebar-card">
        <div class="pay-sidebar-title">Langkah Pembayaran</div>
        <div class="pay-steps">
            <a href="#biaya" class="pay-step-item active">
                <div class="pay-step-num">1</div>
                <div>
                    <div class="pay-step-label">Cek Tagihan</div>
                    <div class="pay-step-sub">Rincian biaya</div>
                </div>
            </a>
            <a href="#metode" class="pay-step-item">
                <div class="pay-step-num">2</div>
                <div>
                    <div class="pay-step-label">Pilih Metode</div>
                    <div class="pay-step-sub">Midtrans / manual</div>
                </div>
            </a>
            <a href="#manual" class="pay-step-item">
                <div class="pay-step-num">3</div>
                <div>
                    <div class="pay-step-label">Konfirmasi</div>
                    <div class="pay-step-sub">Upload bukti</div>
                </div>
            </a>
            <a href="#panduan" class="pay-step-item">
                <div class="pay-step-num">?</div>
                <div>
                    <div class="pay-step-label">Bantuan</div>
                    <div class="pay-step-sub">Panduan & FAQ</div>
                </div>
            </a>
        </div>
        <div class="pay-sidebar-back">
            <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id ?? 0) }}" class="btn-back-to-detail">
                <i class="bi bi-arrow-left"></i> Detail Pendaftaran
            </a>
        </div>
    </div>
</div>
