{{-- ══ STATUS NOTIFICATION BOXES ══ --}}
@if($isDraft && $pend?->catatan_verifikasi)
    <div class="status-box status-box--warning mb-3">
        <i class="bi bi-arrow-counterclockwise fs-4 flex-shrink-0"></i>
        <div>
            <strong>Pendaftaran Dikembalikan untuk Diperbaiki</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">Catatan admin: <em>{{ $pend->catatan_verifikasi }}</em></p>
        </div>
    </div>
@elseif($isDraft)
    <div class="status-box status-box--info mb-3">
        <i class="bi bi-info-circle fs-4 flex-shrink-0"></i>
        <div>
            <strong>Lengkapi Formulir dan Dokumen</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">Setelah semua data lengkap, klik tombol <strong>Kirim
                    Pendaftaran</strong> di bawah.</p>
        </div>
    </div>
@elseif($status === 'submit')
    <div class="status-box status-box--success mb-3">
        <i class="bi bi-send-check fs-4 flex-shrink-0"></i>
        <div><strong>Pendaftaran Berhasil Dikirim</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">Sedang menunggu verifikasi oleh admin. Pantau halaman ini secara
                berkala.</p>
        </div>
    </div>
    <div class="status-box status-box--payment mb-3">
        <i class="bi bi-credit-card-2-front fs-4 flex-shrink-0"></i>
        <div>
            <strong>Lakukan Pembayaran Biaya Registrasi</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">
                Sambil menunggu verifikasi, Anda sudah dapat melakukan pembayaran biaya registrasi
                via transfer manual atau payment gateway.
                Gunakan tombol <strong>Bayar Pendaftaran</strong> di bawah.
            </p>
        </div>
    </div>
@elseif($status === 'verifikasi')
    <div class="status-box status-box--info mb-3">
        <i class="bi bi-search fs-4 flex-shrink-0"></i>
        <div><strong>Sedang Diverifikasi</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">Admin sedang memeriksa kelengkapan data dan dokumen Anda.</p>
        </div>
    </div>
    <div class="status-box status-box--payment mb-3">
        <i class="bi bi-credit-card-2-front fs-4 flex-shrink-0"></i>
        <div>
            <strong>Segera Selesaikan Pembayaran</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">
                Selesaikan pembayaran biaya registrasi agar proses seleksi dapat berjalan.
                Gunakan tombol <strong>Bayar Pendaftaran</strong> di bawah.
            </p>
        </div>
    </div>
@elseif(in_array($status, ['lulus', 'daftar_ulang', 'siswa_tetap', 'tidak_lulus']))
    <div class="status-box status-box--{{ $status === 'tidak_lulus' ? 'warning' : 'success' }} mb-3">
        <i class="bi bi-{{ $status === 'tidak_lulus' ? 'x-circle' : 'trophy' }} fs-4 flex-shrink-0"></i>
        <div>
            <strong>{{ $st['label'] }}</strong>
            <p class="mb-0 mt-1" style="font-size:.85rem;">
                <a href="{{ route('ppdb.pengumuman.index') }}" class="fw-semibold">Lihat Pengumuman Resmi →</a>
            </p>
        </div>
    </div>
@endif
