@extends('layouts.portal')

@section('title', 'Pembayaran Pendaftaran')

@section('content')

    @php
        $noPendaftaran = $pendaftaran->no_pendaftaran ?? ('PPDB-' . ($pendaftaran->id ?? '?'));
        $namaLengkap   = $peserta->nama_lengkap ?? auth()->user()->name ?? '—';
        $namaJalur     = $jalur->nama ?? '—';
        $statusDaftar  = $pendaftaran->status ?? '—';

        $statusMap = [
            'draft'       => ['label' => 'Draft',        'color' => 'secondary', 'icon' => 'bi-pencil'],
            'submit'      => ['label' => 'Dikirim',       'color' => 'primary',   'icon' => 'bi-send-fill'],
            'verifikasi'  => ['label' => 'Diverifikasi',  'color' => 'info',      'icon' => 'bi-patch-check-fill'],
            'lulus'       => ['label' => 'Lulus',         'color' => 'success',   'icon' => 'bi-trophy-fill'],
            'tidak_lulus' => ['label' => 'Tidak Lulus',   'color' => 'danger',    'icon' => 'bi-x-circle-fill'],
            'daftar_ulang'=> ['label' => 'Daftar Ulang',  'color' => 'warning',   'icon' => 'bi-arrow-repeat'],
            'siswa_tetap' => ['label' => 'Siswa Tetap',   'color' => 'success',   'icon' => 'bi-person-badge-fill'],
        ];
        $st = $statusMap[$statusDaftar] ?? ['label' => ucfirst($statusDaftar), 'color' => 'secondary', 'icon' => 'bi-person-badge'];

        $nominalBiaya = $biaya ? (float) $biaya->nominal : 0;
        $nominalFmt   = 'Rp ' . number_format($nominalBiaya, 0, ',', '.');

        $statusPembayaranMap = [
            'pending' => ['label' => 'Menunggu',       'color' => 'warning', 'icon' => 'bi-hourglass-split'],
            'paid'    => ['label' => 'Lunas',           'color' => 'success', 'icon' => 'bi-check-circle-fill'],
            'expired' => ['label' => 'Kedaluwarsa',     'color' => 'secondary','icon' => 'bi-clock-history'],
            'failed'  => ['label' => 'Gagal',           'color' => 'danger',  'icon' => 'bi-x-circle-fill'],
            'refund'  => ['label' => 'Refund',          'color' => 'info',    'icon' => 'bi-arrow-counterclockwise'],
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

    {{-- ERROR: Status belum bisa bayar --}}
    @if(isset($error_status))
        <div class="pay-error-screen">
            <span class="pay-error-icon">⚠️</span>
            <h5 class="fw-800 mb-2">Belum Dapat Melakukan Pembayaran</h5>
            <p class="text-muted mb-4" style="max-width:460px;margin:0 auto .5rem">{!! $error_status !!}</p>
            <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id ?? 0) }}"
               class="btn-submit-payment d-inline-flex" style="width:auto;padding:.75rem 2rem">
                <i class="bi bi-pencil-square"></i>Lanjutkan Pengisian Pendaftaran
            </a>
        </div>
    @else

    <div class="row g-4">

        {{-- SIDEBAR --}}
        <div class="col-lg-3 col-md-4">
            @include('portal.pembayaran.partials._pembayaran_sidebar')
        </div>

        {{-- MAIN CONTENT --}}
        <div class="col-lg-9 col-md-8">

            {{-- ═══ 1. RINCIAN BIAYA ═══ --}}
            <div id="biaya">
                {{-- Paid banner --}}
                @if(isset($pembayaran) && $pembayaran?->status === 'paid')
                    <div class="pay-status-banner success mb-3">
                        <i class="bi bi-patch-check-fill pay-status-icon"></i>
                        <div>
                            <div class="psb-title">Pembayaran Dikonfirmasi!</div>
                            <p class="psb-desc">
                                @if($pembayaran->metode === 'midtrans')
                                    Pembayaran terverifikasi otomatis oleh sistem Midtrans.
                                @else
                                    Pembayaran telah diverifikasi oleh Panitia PPDB.
                                @endif
                            </p>
                        </div>
                    </div>
                @elseif(isset($pembayaran) && $pembayaran?->status === 'pending' && $pembayaran->metode === 'midtrans')
                    <div class="pay-status-banner warning mb-3">
                        <i class="bi bi-exclamation-circle-fill pay-status-icon"></i>
                        <div style="flex:1">
                            <div class="psb-title">Tagihan Otomatis Aktif</div>
                            <p class="psb-desc">Selesaikan pembayaran sebelum batas waktu habis.</p>
                            @if(is_array($pembayaran->midtrans_response) && isset($pembayaran->midtrans_response['va_numbers'][0]))
                                @php $va = $pembayaran->midtrans_response['va_numbers'][0]; @endphp
                                <div class="pay-va-box mt-2">
                                    <div class="pay-va-bank">{{ strtoupper($va['bank']) }} Virtual Account</div>
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <div class="pay-va-number">{{ $va['va_number'] }}</div>
                                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold"
                                                onclick="copyToClipboard('{{ $va['va_number'] }}', this)">
                                            <i class="bi bi-clipboard me-1"></i>Salin
                                        </button>
                                    </div>
                                    @if(isset($pembayaran->midtrans_response['expiry_time']))
                                        <div class="pay-va-expiry mt-1">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Batas: {{ \Carbon\Carbon::parse($pembayaran->midtrans_response['expiry_time'])->isoFormat('D MMM YYYY, HH:mm') }} WIB
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="pay-card">
                    <div class="pay-card-header">
                        <div class="pay-card-icon"><i class="bi bi-receipt"></i></div>
                        <h2 class="pay-card-title">Rincian Tagihan</h2>
                    </div>
                    <div class="pay-card-body">
                        @if($biaya)
                            <div class="pay-invoice-row">
                                <div>
                                    <div class="pay-invoice-name">{{ $biaya->nama }}</div>
                                    @if($biaya->deskripsi)
                                        <div class="pay-invoice-desc">{{ $biaya->deskripsi }}</div>
                                    @endif
                                </div>
                                <div class="pay-invoice-amount">{{ $nominalFmt }}</div>
                            </div>
                            <div class="pay-total-row">
                                <div class="pay-total-label">Total Tagihan</div>
                                <div class="pay-total-amount">{{ $nominalFmt }}</div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-info-circle-fill fs-2 text-muted mb-2 d-block"></i>
                                <h6 class="fw-700">Biaya Belum Tersedia</h6>
                                <p class="text-muted small mb-0">Hubungi Panitia PPDB untuk informasi biaya.</p>
                            </div>
                        @endif

                        {{-- Detail record jika sudah ada pembayaran --}}
                        @if(isset($pembayaran) && $pembayaran)
                            <div class="pay-detail-grid">
                                <div class="pay-detail-item">
                                    <div class="pay-detail-key">Order ID</div>
                                    <div class="pay-detail-val font-monospace" style="font-size:.8rem">{{ $pembayaran->order_id ?? '—' }}</div>
                                </div>
                                <div class="pay-detail-item">
                                    <div class="pay-detail-key">Metode</div>
                                    <div class="pay-detail-val">{{ $pembayaran->metode === 'manual' ? 'Transfer Manual' : ucfirst($pembayaran->metode ?? '—') }}</div>
                                </div>
                                <div class="pay-detail-item">
                                    <div class="pay-detail-key">Nominal Dibayar</div>
                                    <div class="pay-detail-val text-success">Rp {{ number_format((float)$pembayaran->amount, 0, ',', '.') }}</div>
                                </div>
                                <div class="pay-detail-item">
                                    <div class="pay-detail-key">Status</div>
                                    <div class="pay-detail-val">
                                        <span class="badge bg-{{ $sp['color'] }} px-2 py-1">
                                            <i class="bi {{ $sp['icon'] }} me-1"></i>{{ $sp['label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if($pembayaran->bukti_bayar)
                                @php
                                    $ext  = pathinfo($pembayaran->bukti_bayar, PATHINFO_EXTENSION);
                                    $type = strtolower($ext) === 'pdf' ? 'pdf' : 'image';
                                    $url  = Storage::url($pembayaran->bukti_bayar);
                                @endphp
                                <div class="mt-3 d-flex align-items-center gap-2">
                                    <span class="text-muted small fw-600">Bukti Bayar:</span>
                                    <button type="button"
                                            onclick="previewDokumen('{{ $url }}', '{{ $type }}', 'Bukti Pembayaran')"
                                            class="btn btn-sm btn-outline-success fw-bold">
                                        <i class="bi bi-eye me-1"></i>Lihat Berkas
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══ 2 & 3. METODE (hanya tampil jika belum paid) ═══ --}}
            @if(!$pembayaran || $pembayaran->status !== 'paid')

            {{-- Midtrans --}}
            <div id="metode">
                <div class="pay-card">
                    <div class="pay-card-header">
                        <div class="pay-card-icon" style="background:#eff6ff;color:#2563eb">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h2 class="pay-card-title" style="color:#1d4ed8">Bayar Cepat via Midtrans</h2>
                        <span class="badge bg-primary ms-auto px-2 py-1" style="font-size:.65rem">Otomatis</span>
                    </div>
                    <div class="pay-card-body">
                        <div class="pay-midtrans-card">
                            <div class="pay-midtrans-header">
                                <div class="pay-midtrans-logo"><i class="bi bi-lightning-charge-fill"></i></div>
                                <div>
                                    <div class="pay-midtrans-title">Midtrans Payment Gateway</div>
                                    <div class="pay-midtrans-sub">Verifikasi instan — tanpa upload bukti</div>
                                </div>
                            </div>
                            <div class="pay-method-badges">
                                @foreach(['QRIS', 'GoPay', 'ShopeePay', 'Dana', 'VA BRI', 'VA BNI', 'VA BSI', 'VA Mandiri', 'Indomaret', 'Kartu Kredit'] as $m)
                                    <span class="pay-method-badge">{{ $m }}</span>
                                @endforeach
                            </div>
                        </div>

                        <button id="btn-bayar-midtrans" class="btn-pay-midtrans" @if(!$biaya) disabled @endif>
                            <i class="bi bi-lightning-charge-fill"></i>
                            Bayar Sekarang &mdash; {{ $nominalFmt }}
                        </button>

                        <div id="midtrans-alert" class="mt-3 d-none">
                            <div class="pay-status-banner warning">
                                <i class="bi bi-info-circle-fill pay-status-icon" style="font-size:1.25rem"></i>
                                <p class="psb-desc mb-0" id="midtrans-alert-msg"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transfer Manual --}}
            <div id="manual">
                <div class="pay-card">
                    <div class="pay-card-header">
                        <div class="pay-card-icon"><i class="bi bi-bank"></i></div>
                        <h2 class="pay-card-title">Transfer Manual</h2>
                    </div>
                    <div class="pay-card-body">

                        <div class="row g-4">
                            {{-- Rekening Tujuan --}}
                            <div class="col-md-5">
                                <h6 class="fw-800 mb-1 small text-uppercase" style="letter-spacing:.5px;color:var(--color-text-muted)">
                                    <i class="bi bi-credit-card me-1"></i>Rekening Tujuan
                                </h6>
                                <p class="small text-muted mb-3">Transfer ke salah satu rekening resmi berikut:</p>

                                @foreach($rekeningTujuan as $rek)
                                    <div class="rek-bank-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="rek-bank-badge">{{ $rek['bank'] }}</span>
                                            <span class="rek-an">a.n. {{ $rek['atas_nama'] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="rek-number">{{ $rek['no'] }}</span>
                                            <button type="button"
                                                    class="btn btn-sm btn-light border shadow-sm fw-bold"
                                                    onclick="copyToClipboard('{{ str_replace('-', '', $rek['no']) }}', this)"
                                                    title="Salin Nomor Rekening">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Upload Bukti --}}
                            <div class="col-md-7">
                                <h6 class="fw-800 mb-1 small text-uppercase" style="letter-spacing:.5px;color:var(--color-text-muted)">
                                    <i class="bi bi-cloud-upload me-1"></i>Upload Bukti Transfer
                                </h6>
                                <p class="small text-muted mb-3">
                                    Setelah transfer, unggah foto/PDF bukti di sini.
                                    Admin memverifikasi dalam <strong class="text-primary">1×24 jam kerja</strong>.
                                </p>

                                @if($pembayaran && $pembayaran->status === 'pending' && $pembayaran->metode === 'midtrans')
                                    <div class="pay-status-banner warning">
                                        <i class="bi bi-exclamation-triangle-fill pay-status-icon" style="font-size:1.25rem"></i>
                                        <div>
                                            <div class="psb-title">Tagihan Otomatis Aktif</div>
                                            <p class="psb-desc mb-2">Batalkan tagihan Midtrans terlebih dahulu sebelum menggunakan metode ini.</p>
                                            <button type="button" id="btn-cancel-midtrans"
                                                    class="btn btn-warning btn-sm fw-bold px-3">
                                                <i class="bi bi-x-circle me-1"></i>Batalkan Tagihan
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <form id="form-konfirmasi-manual">
                                        @csrf

                                        <div class="mb-3">
                                            <div class="pay-upload-zone" id="file-drop-zone">
                                                <input type="file" id="input-bukti" name="bukti_bayar"
                                                       accept=".jpg,.jpeg,.png,.pdf"
                                                       class="pay-upload-input">
                                                <div id="file-drop-content">
                                                    <i class="bi bi-cloud-arrow-up pay-upload-icon"></i>
                                                    <div class="pay-upload-title">Klik atau Seret Berkas</div>
                                                    <div class="pay-upload-hint">JPG, PNG, PDF — maks. 5 MB</div>
                                                </div>
                                                <div class="pay-upload-preview" id="file-preview">
                                                    <i class="bi bi-file-earmark-check-fill pay-upload-icon" style="color:#16a34a"></i>
                                                    <div class="pay-upload-title" id="file-preview-name" style="color:#16a34a"></div>
                                                    <div class="pay-upload-hint" id="file-preview-size"></div>
                                                    <button type="button" id="btn-remove-file"
                                                            class="btn btn-sm btn-outline-danger mt-2 fw-bold px-3">
                                                        <i class="bi bi-x-circle me-1"></i>Ganti File
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-700" for="input-keterangan">
                                                Keterangan <span class="text-muted fw-normal">(Opsional)</span>
                                            </label>
                                            <textarea id="input-keterangan" name="keterangan"
                                                      class="form-control form-control-sm" rows="2"
                                                      placeholder="Bank pengirim atau informasi tambahan..."></textarea>
                                        </div>

                                        <div id="upload-progress-wrapper" class="d-none mb-3">
                                            <div class="progress" style="height:8px;border-radius:4px">
                                                <div id="upload-progress-bar"
                                                     class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                                     role="progressbar" style="width:0%"></div>
                                            </div>
                                            <div class="text-center mt-1 small fw-700 text-success" id="upload-pct">0% Mengupload...</div>
                                        </div>

                                        <div id="manual-result" class="d-none mb-3"></div>

                                        <button type="submit" id="btn-upload-bukti"
                                                class="btn-submit-payment" @if(!$biaya) disabled @endif>
                                            <i class="bi bi-cloud-upload-fill"></i>
                                            Kirim Bukti Pembayaran
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @endif {{-- end if not paid --}}

            {{-- ═══ 4. PANDUAN ═══ --}}
            <div id="panduan">
                <div class="pay-card">
                    <div class="pay-card-header">
                        <div class="pay-card-icon"><i class="bi bi-question-circle-fill"></i></div>
                        <h2 class="pay-card-title">Panduan Pembayaran</h2>
                    </div>
                    <div class="pay-card-body">
                        <div class="row g-4">
                            <div class="col-md-7">
                                {{-- Via Midtrans --}}
                                <div class="pay-guide-item">
                                    <div class="pay-guide-num" style="background:#eff6ff;color:#2563eb">
                                        <i class="bi bi-lightning-charge-fill" style="font-size:.7rem"></i>
                                    </div>
                                    <div>
                                        <div class="pay-guide-title">Bayar via Midtrans</div>
                                        <div class="pay-guide-desc">
                                            Klik <strong>Bayar Sekarang</strong> lalu pilih QRIS, VA, atau e-Wallet.
                                            Pembayaran terverifikasi <strong>otomatis & instan</strong> — tidak perlu upload bukti.
                                        </div>
                                    </div>
                                </div>
                                {{-- Transfer Manual --}}
                                <div class="pay-guide-item">
                                    <div class="pay-guide-num" style="background:var(--color-primary-light);color:var(--color-primary-hover)">
                                        <i class="bi bi-bank" style="font-size:.7rem"></i>
                                    </div>
                                    <div>
                                        <div class="pay-guide-title">Transfer Manual</div>
                                        <div class="pay-guide-desc">
                                            Transfer ke salah satu rekening resmi, lalu upload foto/PDF bukti transfer.
                                            Verifikasi oleh Panitia dalam <strong>1×24 jam kerja</strong>.
                                        </div>
                                    </div>
                                </div>
                                {{-- Ganti Metode --}}
                                <div class="pay-guide-item">
                                    <div class="pay-guide-num" style="background:#fff7ed;color:#c2410c">
                                        <i class="bi bi-arrow-left-right" style="font-size:.7rem"></i>
                                    </div>
                                    <div>
                                        <div class="pay-guide-title">Ganti ke Transfer Manual</div>
                                        <div class="pay-guide-desc">
                                            Jika sudah membuat tagihan Midtrans, batalkan dulu dengan tombol
                                            <strong>Batalkan Tagihan</strong> sebelum memilih metode manual.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pay-help-box">
                                    <div class="help-icon">
                                        <i class="bi bi-telephone-inbound-fill"></i>
                                    </div>
                                    <div class="help-title">Butuh Bantuan?</div>
                                    <div class="help-desc">
                                        Kendala pembayaran? Hubungi Panitia PPDB via WhatsApp — kami siap membantu.
                                    </div>
                                    <a href="https://wa.me/628123456789" target="_blank" class="btn-wa">
                                        <i class="bi bi-whatsapp"></i>Hubungi Panitia
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end col main --}}
    </div>{{-- end row --}}
    @endif

    {{-- MODAL PREVIEW --}}
    <div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="preview-filename">Preview Berkas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="preview-body" style="background:#f8f9fa"></div>
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
