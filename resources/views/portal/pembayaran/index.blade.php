@extends('layouts.portal')

@section('title', 'Pembayaran Pendaftaran')

@section('content')

    @php
        $noPendaftaran = $pendaftaran->no_pendaftaran ?? ('PPDB-' . ($pendaftaran->id ?? '?'));
        $namaLengkap = $peserta->nama_lengkap ?? auth()->user()->name ?? '—';
        $namaJalur = $jalur->nama ?? '—';
        $statusDaftar = $pendaftaran->status ?? '—';

        $statusMap = [
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'submit' => ['label' => 'Dikirim', 'color' => 'primary'],
            'verifikasi' => ['label' => 'Diverifikasi', 'color' => 'info'],
            'lulus' => ['label' => 'Lulus', 'color' => 'success'],
            'tidak_lulus' => ['label' => 'Tidak Lulus', 'color' => 'danger'],
            'daftar_ulang' => ['label' => 'Daftar Ulang', 'color' => 'warning'],
            'siswa_tetap' => ['label' => 'Siswa Tetap', 'color' => 'success'],
        ];
        $st = $statusMap[$statusDaftar] ?? ['label' => ucfirst($statusDaftar), 'color' => 'secondary'];

        $nominalBiaya = $biaya ? (float) $biaya->nominal : 0;
        $nominalFmt = 'Rp ' . number_format($nominalBiaya, 0, ',', '.');

        // Status pembayaran existing
        $statusPembayaranMap = [
            'pending' => ['label' => 'Menunggu Konfirmasi', 'color' => 'warning', 'icon' => 'bi-hourglass-split'],
            'paid' => ['label' => 'Lunas', 'color' => 'success', 'icon' => 'bi-check-circle-fill'],
            'expired' => ['label' => 'Kedaluwarsa', 'color' => 'secondary', 'icon' => 'bi-clock-history'],
            'failed' => ['label' => 'Gagal', 'color' => 'danger', 'icon' => 'bi-x-circle-fill'],
            'refund' => ['label' => 'Refund', 'color' => 'info', 'icon' => 'bi-arrow-counterclockwise'],
        ];
        $sp = isset($pembayaran) && $pembayaran
            ? ($statusPembayaranMap[$pembayaran->status] ?? ['label' => ucfirst($pembayaran->status), 'color' => 'secondary', 'icon' => 'bi-circle'])
            : null;

        $rekeningTujuan = [
            ['bank' => 'BRI', 'no' => '1234-5678-9012-3456', 'atas_nama' => 'SMK Negeri 1 Contoh'],
            ['bank' => 'BNI', 'no' => '9876-5432-1098-7654', 'atas_nama' => 'SMK Negeri 1 Contoh'],
            ['bank' => 'BSI', 'no' => '0011-2233-4455-6677', 'atas_nama' => 'SMK Negeri 1 Contoh'],
        ];
    @endphp


    @include('portal.pembayaran.partials._pembayaran_header')

    {{-- Alert status draft (tidak bisa bayar) --}}
    @if(isset($error_status))
        <div class="status-banner status-banner--warning">
            <i class="bi bi-exclamation-triangle-fill fs-1 mb-3 d-block"></i>
            <h4 class="fw-800">Belum Dapat Melakukan Pembayaran</h4>
            <p class="mb-4">{!! $error_status !!}</p>
            <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id ?? 0) }}" class="btn btn-success px-4 py-2 fw-bold"
                style="border-radius: var(--radius-md)">
                <i class="bi bi-pencil me-2"></i>Lanjutkan Pengisian Pendaftaran
            </a>
        </div>
    @else

        <div class="row g-4">
            {{-- 🛰️ SIDEBAR NAVIGATION --}}
            <div class="col-lg-3 col-md-4">
                @include('portal.pembayaran.partials._pembayaran_sidebar')
            </div>

            {{-- 📂 CONTENT AREA --}}
            <div class="col-lg-9 col-md-8">

                {{-- ══ SECTION: STATUS & RINCIAN ══ --}}
                <div id="biaya">
                    {{-- Status Pembayaran Card (Jika sudah ada record) --}}
                    @if(isset($pembayaran) && $pembayaran)
                        <div class="profil-card mb-4">
                            <div class="profil-card-header">
                                <i class="bi bi-receipt-cutoff"></i>
                                <span>Status Pembayaran Anda</span>
                            </div>
                            <div class="profil-card-body">
                                @if($pembayaran->status === 'paid')
                                    <div class="status-banner status-banner--success py-4 mb-4">
                                        <i class="bi bi-check-circle-fill fs-1 mb-2 d-block"></i>
                                        <h5 class="fw-900 mb-1">Pembayaran Terkonfirmasi!</h5>
                                        <p class="small mb-0 opacity-90">
                                            @if($pembayaran->metode === 'midtrans')
                                                Pembayaran Anda telah berhasil diverifikasi otomatis oleh sistem.
                                            @else
                                                Pembayaran Anda telah berhasil diverifikasi oleh Panitia.
                                            @endif
                                        </p>
                                    </div>
                                @elseif($pembayaran->status === 'pending')
                                    @if($pembayaran->metode === 'midtrans')
                                        <div class="status-banner status-banner--warning py-4 mb-4" style="background-color: #fff3cd; border-color: #ffe69c;">
                                            <i class="bi bi-exclamation-circle fs-1 mb-2 d-block text-warning"></i>
                                            <h5 class="fw-900 mb-1">Segera Lakukan Pembayaran!</h5>
                                            <p class="small mb-0 opacity-90">Anda memiliki tagihan otomatis yang belum dibayar.</p>
                                            
                                            @if(is_array($pembayaran->midtrans_response) && isset($pembayaran->midtrans_response['va_numbers'][0]))
                                                <div class="mt-3 p-3 bg-white rounded border text-start shadow-sm">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-uppercase fw-bold text-muted">{{ $pembayaran->midtrans_response['va_numbers'][0]['bank'] }} Virtual Account</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                        <span class="fs-5 fw-bold font-monospace text-primary-emphasis">{{ $pembayaran->midtrans_response['va_numbers'][0]['va_number'] }}</span>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" onclick="copyToClipboard('{{ $pembayaran->midtrans_response['va_numbers'][0]['va_number'] }}', this)">
                                                            <i class="bi bi-clipboard me-1"></i>Salin
                                                        </button>
                                                    </div>
                                                    @if(isset($pembayaran->midtrans_response['expiry_time']))
                                                    <hr class="my-2 opacity-25">
                                                    <div class="small text-danger fw-600">
                                                        <i class="bi bi-clock-history me-1"></i>Batas Waktu: {{ \Carbon\Carbon::parse($pembayaran->midtrans_response['expiry_time'])->isoFormat('D MMMM YYYY, HH:mm') }} WIB
                                                    </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="status-banner status-banner--warning py-4 mb-4">
                                            <i class="bi bi-hourglass-split fs-1 mb-2 d-block text-warning"></i>
                                            <h5 class="fw-900 mb-1">Menunggu Verifikasi Manual</h5>
                                            <p class="small mb-0 opacity-90">Bukti transfer Anda sedang dalam proses peninjauan oleh Panitia.</p>
                                        </div>
                                    @endif
                                @endif

                                <div class="info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Order ID</span>
                                        <span class="info-val font-monospace">{{ $pembayaran->order_id ?? '—' }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Metode</span>
                                        <span
                                            class="info-val text-capitalize">{{ $pembayaran->metode === 'manual' ? 'Transfer Manual' : ($pembayaran->metode ?? '—') }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Nominal Bayar</span>
                                        <span class="info-val text-success fw-800">Rp
                                            {{ number_format((float) $pembayaran->amount, 0, ',', '.') }}</span>
                                    </div>
                                    @if($pembayaran->bukti_bayar)
                                        <div class="info-row">
                                            <span class="info-label">Bukti Bayar</span>
                                            @php
                                                $ext = pathinfo($pembayaran->bukti_bayar, PATHINFO_EXTENSION);
                                                $type = strtolower($ext) === 'pdf' ? 'pdf' : 'image';
                                                $url = Storage::url($pembayaran->bukti_bayar);
                                            @endphp
                                            <button type="button" 
                                                    onclick="previewDokumen('{{ $url }}', '{{ $type }}', 'Bukti Pembayaran')"
                                                class="btn btn-sm btn-outline-success fw-bold px-3">
                                                <i class="bi bi-eye me-1"></i>Lihat Berkas
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i class="bi bi-receipt"></i>
                            <span>Rincian Biaya Pendaftaran</span>
                        </div>
                        <div class="profil-card-body">
                            @if($biaya)
                                <div class="biaya-list">
                                    <div class="biaya-row">
                                        <div>
                                            <div class="biaya-nama-title">{{ $biaya->nama }}</div>
                                            @if($biaya->deskripsi)
                                                <div class="biaya-nama-desc">{{ $biaya->deskripsi }}</div>
                                            @endif
                                        </div>
                                        <div class="biaya-nominal">{{ $nominalFmt }}</div>
                                    </div>
                                </div>
                                <div class="biaya-total-row">
                                    <span class="biaya-total-label">Total Tagihan</span>
                                    <span class="biaya-total-nominal">{{ $nominalFmt }}</span>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-3" style="font-size: 3rem">ℹ️</div>
                                    <h5>Biaya Belum Tersedia</h5>
                                    <p class="text-muted small">Hubungi Panitia PPDB untuk informasi biaya registrasi.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ══ SECTION: METODE ══ --}}
                @if(!$pembayaran || $pembayaran->status !== 'paid')
                <div id="metode">
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Bayar Cepat via Midtrans</span>
                        </div>
                        <div class="profil-card-body">
                            <div class="p-4 rounded-4 mb-4" style="background: #eff6ff; border: 1.5px solid #dbeafe;">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="badge bg-white text-primary border border-primary-subtle px-3 py-2">Otomatis
                                    </div>
                                    <div class="text-primary-emphasis fw-bold">10+ Metode Pembayaran Terintegrasi</div>
                                </div>
                                <p class="small text-primary-emphasis mb-0">
                                    Gunakan Midtrans untuk pembayaran instan melalui QRIS, Gopey, ShopeePay, VA (Virtual
                                    Account) semua bank, atau Kartu Kredit.
                                    <strong>Verifikasi instan tanpa perlu upload bukti.</strong>
                                </p>
                            </div>

                            <button id="btn-bayar-midtrans" class="btn-pay-midtrans" @if(!$biaya) disabled @endif>
                                <i class="bi bi-lightning-charge-fill"></i>
                                Bayar Sekarang — {{ $nominalFmt }}
                            </button>

                            <div id="midtrans-alert" class="alert alert-info mt-4 d-none rounded-4 border-0 shadow-sm">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <span id="midtrans-alert-msg" class="fw-600"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ SECTION: MANUAL ══ --}}
                <div id="manual">
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i class="bi bi-bank"></i>
                            <span>Atau Transfer Manual</span>
                        </div>
                        <div class="profil-card-body">
                            <div class="row g-4">
                                {{-- Sisi Kiri: List Rekening (Scrollable) --}}
                                <div class="col-md-5 border-md-end border-mobile-bottom pe-md-4">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-2 text-primary"></i>Rekening Tujuan
                                    </h6>
                                    <p class="small text-muted mb-4">Silakan transfer ke salah satu rekening resmi kami di bawah
                                        ini:</p>

                                    <div class="rekening-scroll-container">
                                        @foreach($rekeningTujuan as $rek)
                                            <div class="rekening-item flex-column align-items-stretch p-3">
                                                {{-- Baris 1: Bank & Nama --}}
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="rek-bank text-uppercase">{{ $rek['bank'] }}</div>
                                                    <div class="rek-an small fw-bold text-muted text-truncate ms-2">a.n.
                                                        {{ $rek['atas_nama'] }}</div>
                                                </div>
                                                {{-- Baris 2: No Rek & Copy --}}
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="rek-no fs-6">{{ $rek['no'] }}</div>
                                                    <button type="button" class="btn btn-light btn-xs border shadow-sm"
                                                        onclick="copyToClipboard('{{ str_replace('-', '', $rek['no']) }}', this)"
                                                        title="Salin No. Rekening">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Sisi Kanan: Upload Bukti --}}
                                <div class="col-md-7 ps-md-4">
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-2">Upload Bukti Transfer</h6>
                                        <p class="small text-muted mb-0">Setelah melakukan transfer, upload foto/scan bukti
                                            transfer di sini.</p>
                                        <p class="small text-muted mb-4">Admin akan memverifikasi dalam <span
                                                class="fw-bold text-primary">1×24 jam kerja</span> secara manual.</p>
                                    </div>

                                    <form id="form-konfirmasi-manual">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold">Pilih File Bukti <span
                                                    class="text-danger">*</span></label>
                                            <div class="file-drop-zone" id="file-drop-zone">
                                                <input type="file" id="input-bukti" name="bukti_bayar"
                                                    accept=".jpg,.jpeg,.png,.pdf" class="file-drop-input">
                                                <div class="file-drop-content" id="file-drop-content">
                                                    <i class="bi bi-cloud-arrow-up fs-1 text-primary-emphasis mb-2 d-block"></i>
                                                    <div class="fw-bold text-primary-emphasis">Klik atau Seret Berkas</div>
                                                    <div class="text-muted small x-small">Format: JPG, PNG, PDF (Maks 5MB)</div>
                                                </div>
                                                <div class="file-preview d-none" id="file-preview">
                                                    <i class="bi bi-file-earmark-check-fill fs-1 text-success mb-2 d-block"></i>
                                                    <div class="file-preview-name fw-bold small mb-1" id="file-preview-name">
                                                    </div>
                                                    <div class="file-preview-size text-muted x-small" id="file-preview-size">
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger mt-3 px-3 py-1 fw-bold"
                                                        id="btn-remove-file">
                                                        <i class="bi bi-x-circle me-1"></i>Ganti File
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label small fw-bold" for="input-keterangan">Keterangan Tambahan
                                                <span class="text-muted">(Opsional)</span></label>
                                            <textarea id="input-keterangan" name="keterangan"
                                                class="form-control form-control-sm" rows="3"
                                                placeholder="Tuliskan bank pengirim atau informasi tambahan..."></textarea>
                                        </div>

                                        {{-- Progress --}}
                                        <div id="upload-progress-wrapper" class="d-none mb-4">
                                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                                <div id="upload-progress-bar"
                                                    class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                                    role="progressbar" style="width: 0%"></div>
                                            </div>
                                            <div class="text-center mt-2 small fw-bold text-success" id="upload-pct">0%
                                                Mengupload...</div>
                                        </div>

                                        <div id="manual-result" class="d-none mb-4"></div>

                                            @if($pembayaran && $pembayaran->status === 'pending' && $pembayaran->metode === 'midtrans')
                                                <div class="bg-light border-warning border-start border-4 rounded-3 shadow-sm p-3 mb-4">
                                                    <div class="d-flex align-items-start gap-3">
                                                        <div class="text-warning fs-3">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold mb-1 text-dark">Tagihan Otomatis Aktif</h6>
                                                            <p class="small text-muted mb-3" style="line-height: 1.4;">
                                                                Anda harus membatalkan tagihan Midtrans saat ini terlebih dahulu sebelum dapat menggunakan metode transfer manual.
                                                            </p>
                                                            <button type="button" class="btn btn-warning btn-sm fw-bold px-4 rounded-pill shadow-sm" id="btn-cancel-midtrans">
                                                                Batalkan Tagihan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <button type="submit" id="btn-upload-bukti" class="btn-daftar py-3 shadow-none"
                                                    @if(!$biaya) disabled @endif>
                                                    <i class="bi bi-cloud-upload-fill"></i>
                                                    Kirim Bukti Pembayaran
                                                </button>
                                            @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ══ SECTION: PANDUAN ══ --}}
                <div id="panduan">
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i class="bi bi-question-circle"></i>
                            <span>Panduan & Bantuan</span>
                        </div>
                        <div class="profil-card-body">
                            <div class="row gx-5">
                                <div class="col-md-7">
                                    <div class="mb-4">
                                        <h6 class="fw-bold text-dark"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Pembayaran Otomatis (Midtrans)</h6>
                                        <ul class="text-muted small ps-4 mb-0" style="line-height: 1.6;">
                                            <li>Klik tombol <strong>Bayar Sekarang</strong> dan pilih metode (VA, QRIS, e-Wallet) pada jendela yang muncul.</li>
                                            <li>Sistem akan mendeteksi pelunasan secara <strong>otomatis & instan</strong> tanpa perlu mengunggah bukti pembayaran.</li>
                                        </ul>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-bold text-dark"><i class="bi bi-bank text-primary me-2"></i>Transfer Manual</h6>
                                        <ul class="text-muted small ps-4 mb-0" style="line-height: 1.6;">
                                            <li>Lakukan transfer sesuai nominal ke salah satu rekening resmi yang tertera.</li>
                                            <li>Unggah foto/PDF bukti transfer pada form yang tersedia.</li>
                                            <li>Verifikasi dilakukan secara manual oleh Panitia maksimal <strong>1x24 jam kerja</strong>.</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark"><i class="bi bi-arrow-left-right text-info me-2"></i>Kendala & Ganti Metode</h6>
                                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                                            Jika Anda sudah membuat tagihan otomatis namun ingin beralih ke metode transfer manual, Anda <strong>wajib</strong> membatalkannya terlebih dahulu dengan menekan tombol <span class="badge bg-warning text-dark"><i class="bi bi-x-circle me-1"></i>Batalkan Tagihan Otomatis</span>.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div
                                        class="p-4 rounded-4 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                                        <i class="bi bi-telephone-inbound-fill fs-2 mb-3 text-primary"></i>
                                        <h6 class="fw-bold">Butuh Bantuan?</h6>
                                        <p class="small text-muted mb-4">Jika mengalami kendala pembayaran, silakan hubungi
                                            Panitia kami via WhatsApp.</p>
                                        <a href="https://wa.me/628123456789" target="_blank"
                                            class="btn btn-outline-primary fw-bold px-4 py-2"
                                            style="border-radius: var(--radius-md)">
                                            <i class="bi bi-whatsapp me-2"></i>Hubungi Panitia
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- MODAL PREVIEW DOKUMEN --}}
    <div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="preview-filename">Preview Berkas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="preview-body" style="background-color: #f8f9fa;">
                    {{-- Konten preview diisi oleh JS --}}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    @include('portal.pembayaran.partials._pembayaran_styles')
@endpush

@push('scripts')
    @include('portal.pembayaran.partials._pembayaran_scripts')
@endpush