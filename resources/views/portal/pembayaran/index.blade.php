@extends('layouts.portal')

@section('title', 'Pembayaran Pendaftaran')

@section('content')

@php
    $noPendaftaran = $pendaftaran->no_pendaftaran ?? ('PPDB-' . ($pendaftaran->id ?? '?'));
    $namaLengkap   = $peserta->nama_lengkap ?? auth()->user()->name ?? '—';
    $namaJalur     = $jalur->nama ?? '—';
    $statusDaftar  = $pendaftaran->status ?? '—';

    $statusMap = [
        'draft'        => ['label' => 'Draft',         'color' => 'secondary'],
        'submit'       => ['label' => 'Dikirim',        'color' => 'primary'],
        'verifikasi'   => ['label' => 'Diverifikasi',   'color' => 'info'],
        'lulus'        => ['label' => 'Lulus',          'color' => 'success'],
        'tidak_lulus'  => ['label' => 'Tidak Lulus',    'color' => 'danger'],
        'daftar_ulang' => ['label' => 'Daftar Ulang',   'color' => 'warning'],
        'siswa_tetap'  => ['label' => 'Siswa Tetap',    'color' => 'success'],
    ];
    $st = $statusMap[$statusDaftar] ?? ['label' => ucfirst($statusDaftar), 'color' => 'secondary'];

    $nominalBiaya = $biaya ? (float) $biaya->nominal : 0;
    $nominalFmt   = 'Rp ' . number_format($nominalBiaya, 0, ',', '.');

    // Status pembayaran existing
    $statusPembayaranMap = [
        'pending'  => ['label' => 'Menunggu Konfirmasi', 'color' => 'warning',   'icon' => 'bi-hourglass-split'],
        'paid'     => ['label' => 'Lunas',              'color' => 'success',   'icon' => 'bi-check-circle-fill'],
        'expired'  => ['label' => 'Kedaluwarsa',        'color' => 'secondary', 'icon' => 'bi-clock-history'],
        'failed'   => ['label' => 'Gagal',              'color' => 'danger',    'icon' => 'bi-x-circle-fill'],
        'refund'   => ['label' => 'Refund',             'color' => 'info',      'icon' => 'bi-arrow-counterclockwise'],
    ];
    $sp = isset($pembayaran) && $pembayaran
        ? ($statusPembayaranMap[$pembayaran->status] ?? ['label' => ucfirst($pembayaran->status), 'color' => 'secondary', 'icon' => 'bi-circle'])
        : null;

    $rekeningTujuan = [
        ['bank' => 'BRI',  'no' => '1234-5678-9012-3456', 'atas_nama' => 'SMK Negeri 1 Contoh'],
        ['bank' => 'BNI',  'no' => '9876-5432-1098-7654', 'atas_nama' => 'SMK Negeri 1 Contoh'],
        ['bank' => 'BSI',  'no' => '0011-2233-4455-6677', 'atas_nama' => 'SMK Negeri 1 Contoh'],
    ];
@endphp

{{-- ════════════════════════════════════════════════════════════════════
     BREADCRUMB + PAGE TITLE
════════════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="pay-page-header">
            <div class="pay-page-header-left">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sm mb-2">
                        <li class="breadcrumb-item">
                            <a href="{{ route('ppdb.dashboard') }}">
                                <i class="bi bi-house me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('ppdb.pendaftaran.index') }}">Pendaftaran</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
                    </ol>
                </nav>
                <h1 class="pay-page-title">
                    <i class="bi bi-credit-card-2-front me-2 text-success"></i>Pembayaran Pendaftaran
                </h1>
                <p class="pay-page-subtitle">
                    Selesaikan pembayaran biaya registrasi untuk melanjutkan proses PPDB Anda.
                </p>
            </div>
            <div class="pay-page-header-right d-none d-md-flex">
                <div class="pay-no-badge">
                    <div class="pay-no-label">No. Pendaftaran</div>
                    <div class="pay-no-value">{{ $noPendaftaran }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alert status draft (tidak bisa bayar) --}}
@if(isset($error_status))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-start gap-3 rounded-3 border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill fs-4 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Belum Dapat Melakukan Pembayaran</strong><br>
                <span class="small">{!! $error_status !!}</span>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id ?? 0) }}" class="btn btn-success">
                <i class="bi bi-pencil me-2"></i>Lanjutkan Pengisian Pendaftaran
            </a>
        </div>
    </div>
</div>
@else

<div class="row g-4">

    {{-- ────────── KOLOM KIRI: Ring + Biaya + Midtrans + Rekening ────────── --}}
    <div class="col-lg-7">

        {{-- ══ CARD 1: Ringkasan Pendaftaran ══════════════════════════════════ --}}
        <div class="pay-card mb-4">
            <div class="pay-card-header">
                <span><i class="bi bi-file-earmark-person me-2"></i>Ringkasan Pendaftaran</span>
            </div>
            <div class="pay-card-body">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">No. Pendaftaran</div>
                        <div class="info-val fw-bold font-monospace">{{ $noPendaftaran }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-val fw-semibold">{{ $namaLengkap }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Jalur Pendaftaran</div>
                        <div class="info-val">
                            @if($jalur)
                                <span class="jalur-kode-badge">{{ $jalur->kode_jalur ?? '' }}</span>
                                {{ $namaJalur }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status Pendaftaran</div>
                        <div class="info-val">
                            <span class="badge bg-{{ $st['color'] }} rounded-pill">
                                {{ $st['label'] }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tanggal Daftar</div>
                        <div class="info-val">
                            {{ $pendaftaran->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ CARD 2: Rincian Biaya ════════════════════════════════════════ --}}
        <div class="pay-card mb-4">
            <div class="pay-card-header">
                <span><i class="bi bi-receipt me-2"></i>Rincian Biaya</span>
            </div>
            <div class="pay-card-body">
                @if($biaya)
                    <div class="biaya-list">
                        <div class="biaya-row">
                            <div class="biaya-nama">
                                <div class="biaya-nama-title">{{ $biaya->nama }}</div>
                                @if($biaya->deskripsi)
                                    <div class="biaya-nama-desc">{{ $biaya->deskripsi }}</div>
                                @endif
                            </div>
                            <div class="biaya-nominal">{{ $nominalFmt }}</div>
                        </div>
                    </div>
                    <div class="biaya-total-row">
                        <span class="biaya-total-label">Total yang Harus Dibayar</span>
                        <span class="biaya-total-nominal" id="txt-nominal-total">{{ $nominalFmt }}</span>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                        <p class="mb-0 small">Biaya registrasi untuk jalur ini belum dikonfigurasi.<br>
                            Hubungi panitia PPDB untuk informasi lebih lanjut.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══ CARD 3: Bayar via Midtrans ═════════════════════════════════════ --}}
        <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                data-client-key="{{ config('midtrans.client_key') }}"></script>
        <div class="pay-card mb-4 pay-card-midtrans">
            <div class="pay-card-header">
                <span><i class="bi bi-lightning-charge me-2"></i>Bayar via Payment Gateway</span>
            </div>
            <div class="pay-card-body">
                <div class="midtrans-promo">
                    <div class="midtrans-icons">
                        <span class="midtrans-method">VA</span>
                        <span class="midtrans-method">QRIS</span>
                        <span class="midtrans-method">GoPay</span>
                        <span class="midtrans-method">OVO</span>
                        <span class="midtrans-method">Kartu Kredit</span>
                    </div>
                    <p class="midtrans-desc">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        10+ metode pembayaran tersedia, aman & terpercaya via Midtrans.
                    </p>
                </div>

                {{-- Tombol Bayar Sekarang --}}
                <button id="btn-bayar-midtrans"
                        class="btn btn-pay-midtrans w-100 mt-3"
                        @if(!$biaya) disabled @endif>
                    <i class="bi bi-lightning-charge me-2"></i>Bayar Sekarang
                    @if($biaya) — {{ $nominalFmt }} @endif
                </button>

                {{-- Alert hasil getToken — tersembunyi sampai ada response --}}
                <div id="midtrans-alert" class="alert alert-info mt-3 d-none" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="midtrans-alert-msg"></span>
                </div>

            </div>
        </div>

        {{-- ══ CARD 4: Konfirmasi Transfer Manual ════════════════════════════ --}}
        <div class="pay-card pay-card-manual" id="card-manual">
            <div class="pay-card-header">
                <span><i class="bi bi-bank me-2"></i>Atau Konfirmasi via Transfer Manual</span>
            </div>
            <div class="pay-card-body">

                    {{-- Info Rekening Tujuan --}}
                <div class="rekening-section">
                    <div class="rekening-title">Transfer ke Rekening Berikut</div>
                    @foreach($rekeningTujuan as $rek)
                    <div class="rekening-item">
                        <div class="rek-bank">{{ $rek['bank'] }}</div>
                        <div class="rek-detail">
                            <div class="rek-no" id="rek-no-{{ $loop->index }}">{{ $rek['no'] }}</div>
                            <div class="rek-an">a.n. {{ $rek['atas_nama'] }}</div>
                        </div>
                        <button class="btn-copy-rek"
                                data-clipboard-target="#rek-no-{{ $loop->index }}"
                                onclick="copyToClipboard('{{ str_replace('-', '', $rek['no']) }}', this)"
                                title="Salin nomor rekening">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    @endforeach
                    <div class="rekening-nominal-box">
                        <span class="rek-nominal-label">Jumlah Transfer Tepat</span>
                        <span class="rek-nominal-value">{{ $nominalFmt }}</span>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Form Upload Bukti --}}
                <div class="upload-section">
                    <div class="upload-title">Upload Bukti Transfer</div>
                    <p class="upload-desc text-muted small mb-3">
                        Setelah melakukan transfer, upload foto/scan bukti transfer di sini.
                        Admin akan memverifikasi dalam 1×24 jam kerja.
                    </p>

                    <form id="form-konfirmasi-manual">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="input-bukti">
                                Bukti Transfer <span class="text-danger">*</span>
                            </label>
                            <div class="file-drop-zone" id="file-drop-zone">
                                <input type="file"
                                       id="input-bukti"
                                       name="bukti_bayar"
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       class="file-drop-input">
                                <div class="file-drop-content" id="file-drop-content">
                                    <i class="bi bi-cloud-upload fs-2 text-success"></i>
                                    <div class="mt-2 fw-semibold">Klik atau drag &amp; drop file di sini</div>
                                    <div class="text-muted small mt-1">JPG, PNG, atau PDF — maksimal 5MB</div>
                                </div>
                                <div class="file-preview d-none" id="file-preview">
                                    <i class="bi bi-file-earmark-check fs-2 text-success"></i>
                                    <div class="file-preview-name mt-1" id="file-preview-name"></div>
                                    <div class="file-preview-size text-muted small" id="file-preview-size"></div>
                                    <button type="button" class="btn-remove-file mt-2" id="btn-remove-file">
                                        <i class="bi bi-x-circle me-1"></i>Ganti File
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="input-keterangan">
                                Keterangan <span class="text-muted fw-normal">(opsional)</span>
                            </label>
                            <textarea id="input-keterangan"
                                      name="keterangan"
                                      class="form-control"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Contoh: Transfer via BRI tanggal 8 April 2026 pukul 10.30"></textarea>
                            <div class="form-text">Tambahkan info tambahan seperti tanggal transfer, bank, dan nama pengirim.</div>
                        </div>

                        {{-- Progress Upload --}}
                        <div id="upload-progress-wrapper" class="d-none mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-semibold">Mengupload...</span>
                                <span class="small" id="upload-pct">0%</span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div id="upload-progress-bar"
                                     class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                     role="progressbar" style="width:0%"></div>
                            </div>
                        </div>

                        {{-- Result Message --}}
                        <div id="manual-result" class="d-none mb-3"></div>

                        <button type="submit" id="btn-upload-bukti"
                                class="btn btn-success w-100 fw-semibold py-2"
                                @if(!$biaya) disabled @endif>
                            <i class="bi bi-cloud-upload me-2"></i>Upload Bukti Bayar
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-7 --}}

    {{-- ────────── KOLOM KANAN: Status Pembayaran ────────── --}}
    <div class="col-lg-5">

        {{-- ══ CARD 5: Status Pembayaran (conditional) ═══════════════════════ --}}
        @if(isset($pembayaran) && $pembayaran)
        <div class="pay-card pay-card-status mb-4" id="card-status-pembayaran">
            <div class="pay-card-header">
                <span><i class="bi bi-receipt-cutoff me-2"></i>Status Pembayaran</span>
                <span class="badge bg-{{ $sp['color'] }}">
                    <i class="bi {{ $sp['icon'] }} me-1"></i>{{ $sp['label'] }}
                </span>
            </div>
            <div class="pay-card-body">

                @if($pembayaran->status === 'paid')
                <div class="status-success-banner">
                    <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                    <div class="mt-2 fw-bold fs-5 text-success">Pembayaran Terkonfirmasi!</div>
                    <p class="small text-muted mt-1 mb-0">Pembayaran Anda telah berhasil diverifikasi oleh admin.</p>
                </div>
                @elseif($pembayaran->status === 'pending')
                <div class="status-pending-banner">
                    <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                    <div class="mt-2 fw-semibold text-warning">Menunggu Konfirmasi Admin</div>
                    <p class="small text-muted mt-1 mb-0">Bukti transfer Anda sedang diverifikasi. Proses 1×24 jam kerja.</p>
                </div>
                @endif

                <hr class="my-3">

                <div class="info-grid mt-0">
                    <div class="info-row">
                        <div class="info-label">Nominal</div>
                        <div class="info-val fw-bold">
                            Rp {{ number_format((float) $pembayaran->amount, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Metode</div>
                        <div class="info-val text-capitalize">
                            {{ $pembayaran->metode === 'manual' ? 'Transfer Manual' : ucfirst($pembayaran->metode ?? '—') }}
                        </div>
                    </div>
                    @if($pembayaran->waktu_bayar)
                    <div class="info-row">
                        <div class="info-label">Waktu Bayar</div>
                        <div class="info-val">
                            {{ $pembayaran->waktu_bayar->translatedFormat('d F Y, H:i') }}
                        </div>
                    </div>
                    @endif
                    @if($pembayaran->order_id)
                    <div class="info-row">
                        <div class="info-label">Order ID</div>
                        <div class="info-val font-monospace small">{{ $pembayaran->order_id }}</div>
                    </div>
                    @endif
                    @if($pembayaran->bukti_bayar)
                    <div class="info-row">
                        <div class="info-label">Bukti</div>
                        <div class="info-val">
                            <a href="{{ Storage::url($pembayaran->bukti_bayar) }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-success">
                                <i class="bi bi-eye me-1"></i>Lihat Bukti
                            </a>
                        </div>
                    </div>
                    @endif
                    @if($pembayaran->keterangan)
                    <div class="info-row">
                        <div class="info-label">Keterangan</div>
                        <div class="info-val small text-muted">{{ $pembayaran->keterangan }}</div>
                    </div>
                    @endif
                </div>

                @if($pembayaran->status === 'pending')
                <div class="alert alert-warning alert-sm mt-3 mb-0 rounded-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Jika sudah upload ulang, data di atas akan diperbarui otomatis.
                </div>
                @endif
            </div>
        </div>
        @else
        {{-- Belum ada record pembayaran --}}
        <div class="pay-card pay-card-status mb-4">
            <div class="pay-card-header">
                <span><i class="bi bi-receipt-cutoff me-2"></i>Status Pembayaran</span>
            </div>
            <div class="pay-card-body text-center py-4">
                <div style="font-size:2.5rem">💳</div>
                <p class="text-muted mt-3 mb-0 small">
                    Belum ada transaksi pembayaran yang tercatat.<br>
                    Lakukan pembayaran menggunakan salah satu metode di sebelah kiri.
                </p>
            </div>
        </div>
        @endif

        {{-- ══ CARD INFO: Panduan Pembayaran ══════════════════════════════════ --}}
        <div class="pay-card mb-4">
            <div class="pay-card-header">
                <span><i class="bi bi-question-circle me-2"></i>Panduan Pembayaran</span>
            </div>
            <div class="pay-card-body">
                <ol class="panduan-list">
                    <li>Catat nomor rekening dan jumlah yang harus ditransfer.</li>
                    <li>Lakukan transfer dari bank/e-wallet ke rekening tujuan.</li>
                    <li>Pastikan jumlah transfer <strong>tepat sesuai nominal</strong> (termasuk sen jika ada).</li>
                    <li>Upload bukti transfer melalui form di sebelah kiri.</li>
                    <li>Tunggu konfirmasi admin dalam <strong>1×24 jam kerja</strong>.</li>
                    <li>Status pembayaran akan diperbarui otomatis setelah verifikasi.</li>
                </ol>
                <div class="alert alert-info alert-sm mb-0 rounded-3">
                    <i class="bi bi-telephone me-1"></i>
                    Butuh bantuan? Hubungi panitia PPDB via WhatsApp atau datang langsung ke sekolah.
                </div>
            </div>
        </div>

        {{-- ══ Tombol Kembali ══════════════════════════════════════════════════ --}}
        <a href="{{ route('ppdb.pendaftaran.show', $pendaftaran->id ?? 0) }}"
           class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Detail Pendaftaran
        </a>

    </div>{{-- /col-lg-5 --}}
</div>{{-- /row --}}

@endif {{-- end if error_status --}}

@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════════
   PAY PAGE — CSS Variables & Base
══════════════════════════════════════════════════════════════════════ */
:root {
    --pay-radius: 14px;
    --pay-border: #e5e7eb;
    --pay-shadow: 0 2px 12px rgba(0,0,0,0.06);
    --pay-green:  #15803d;
    --pay-green-light: #dcfce7;
    --pay-header-bg: #f9fafb;
}

/* ── Page Header ─────────────────────────────────────────────────── */
.pay-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 16px;
}
.pay-page-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 4px;
}
.pay-page-subtitle {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0;
}
.pay-no-badge {
    background: var(--pay-green-light);
    border: 1.5px solid #86efac;
    border-radius: 12px;
    padding: 12px 20px;
    text-align: right;
}
.pay-no-label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.pay-no-value  { font-size: 1.1rem; font-weight: 800; color: var(--pay-green); font-family: 'Courier New', monospace; letter-spacing: 1px; }

/* ── Card Base ───────────────────────────────────────────────────── */
.pay-card {
    background: #fff;
    border-radius: var(--pay-radius);
    border: 1px solid var(--pay-border);
    box-shadow: var(--pay-shadow);
    overflow: hidden;
}
.pay-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    background: var(--pay-header-bg);
    border-bottom: 1px solid var(--pay-border);
    font-weight: 700;
    color: #374151;
    font-size: 0.875rem;
}
.pay-card-body { padding: 20px; }

/* Midtrans card accent */
.pay-card-midtrans { border-top: 3px solid #3b82f6; }
.pay-card-manual   { border-top: 3px solid var(--pay-green); }
.pay-card-status   { border-top: 3px solid #8b5cf6; }

/* ── Info Grid ───────────────────────────────────────────────────── */
.info-grid  { display: flex; flex-direction: column; gap: 12px; }
.info-row   { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.info-label { font-size: 0.78rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.4px; flex-shrink: 0; }
.info-val   { font-size: 0.875rem; color: #111827; text-align: right; }

.jalur-kode-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    background: var(--pay-green-light);
    color: var(--pay-green);
    padding: 2px 7px;
    border-radius: 5px;
    margin-right: 4px;
}

/* ── Biaya List ──────────────────────────────────────────────────── */
.biaya-list      { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }
.biaya-row       { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.biaya-nama      { flex: 1; }
.biaya-nama-title { font-weight: 600; color: #111827; font-size: 0.9rem; }
.biaya-nama-desc  { font-size: 0.78rem; color: #9ca3af; margin-top: 2px; }
.biaya-nominal   { font-weight: 700; color: var(--pay-green); font-size: 1rem; flex-shrink: 0; white-space: nowrap; }
.biaya-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 2px solid #e5e7eb;
    padding-top: 14px;
    margin-top: 4px;
}
.biaya-total-label  { font-size: 0.8rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.4px; }
.biaya-total-nominal { font-size: 1.3rem; font-weight: 900; color: var(--pay-green); }

/* ── Midtrans Section ────────────────────────────────────────────── */
.midtrans-promo   { background: #eff6ff; border-radius: 10px; padding: 16px; }
.midtrans-icons   { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.midtrans-method  {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    background: #fff;
    border: 1.5px solid #bfdbfe;
    color: #1d4ed8;
    border-radius: 6px;
    padding: 4px 10px;
    letter-spacing: 0.3px;
}
.midtrans-desc  { font-size: 0.82rem; color: #374151; margin: 0; }

.btn-pay-midtrans {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.2s;
    letter-spacing: 0.3px;
}
.btn-pay-midtrans:hover:not(:disabled) {
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37,99,235,0.35);
    color: #fff;
}
.btn-pay-midtrans:disabled { opacity: 0.55; cursor: not-allowed; }

.midtrans-note { font-size: 0.78rem; color: #9ca3af; text-align: center; margin: 0; }

/* ── Rekening Section ────────────────────────────────────────────── */
.rekening-section { }
.rekening-title { font-weight: 700; color: #374151; font-size: 0.875rem; margin-bottom: 12px; }

.rekening-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 10px;
    transition: border-color 0.2s;
}
.rekening-item:hover { border-color: #86efac; }
.rek-bank {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--pay-green-light);
    color: var(--pay-green);
    border-radius: 6px;
    padding: 4px 9px;
    flex-shrink: 0;
    min-width: 42px;
    text-align: center;
}
.rek-detail { flex: 1; min-width: 0; }
.rek-no { font-weight: 700; font-size: 0.95rem; color: #111827; font-family: 'Courier New', monospace; letter-spacing: 1px; }
.rek-an { font-size: 0.75rem; color: #6b7280; margin-top: 1px; }

.btn-copy-rek {
    background: none;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 6px 10px;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}
.btn-copy-rek:hover { border-color: var(--pay-green); color: var(--pay-green); background: var(--pay-green-light); }
.btn-copy-rek.copied { border-color: var(--pay-green); color: var(--pay-green); background: var(--pay-green-light); }

.rekening-nominal-box {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1.5px solid #86efac;
    border-radius: 10px;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 4px;
}
.rek-nominal-label { font-size: 0.8rem; color: var(--pay-green); font-weight: 600; }
.rek-nominal-value { font-size: 1.1rem; font-weight: 900; color: var(--pay-green); }

/* ── Upload Section ──────────────────────────────────────────────── */
.upload-section { }
.upload-title { font-weight: 700; color: #374151; font-size: 0.875rem; margin-bottom: 6px; }
.upload-desc  { color: #6b7280; font-size: 0.8125rem; }

.file-drop-zone {
    position: relative;
    border: 2.5px dashed #d1d5db;
    border-radius: 12px;
    padding: 32px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}
.file-drop-zone:hover, .file-drop-zone.drag-over {
    border-color: var(--pay-green);
    background: #f0fdf4;
}
.file-drop-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.file-drop-content { pointer-events: none; }

.file-preview { }
.file-preview-name { font-weight: 600; color: #111827; font-size: 0.9rem; word-break: break-all; }
.file-preview-size { font-size: 0.78rem; }

.btn-remove-file {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 0.78rem;
    color: #dc2626;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-remove-file:hover { background: #fecaca; }

/* ── Status Banners ──────────────────────────────────────────────── */
.status-success-banner,
.status-pending-banner {
    text-align: center;
    padding: 20px;
    border-radius: 10px;
}
.status-success-banner { background: #f0fdf4; border: 1.5px solid #86efac; }
.status-pending-banner { background: #fffbeb; border: 1.5px solid #fde68a; }

/* ── Panduan List ────────────────────────────────────────────────── */
.panduan-list {
    padding-left: 20px;
    margin-bottom: 16px;
}
.panduan-list li {
    font-size: 0.82rem;
    color: #374151;
    margin-bottom: 8px;
    line-height: 1.5;
}

/* ── Alert SM ────────────────────────────────────────────────────── */
.alert-sm { padding: 10px 14px; font-size: 0.82rem; }

/* ── Breadcrumb SM ───────────────────────────────────────────────── */
.breadcrumb-sm { font-size: 0.78rem; margin: 0; }
.breadcrumb-sm a { color: var(--pay-green); text-decoration: none; }
.breadcrumb-sm a:hover { text-decoration: underline; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    // ── CSRF Token ─────────────────────────────────────────────────────────
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Endpoints ──────────────────────────────────────────────────────────
    const pendaftaranId  = {{ $pendaftaran->id ?? 'null' }};
    const urlGetToken    = '{{ route('ppdb.pembayaran.token', $pendaftaran->id ?? 0) }}';
    const urlKonfirmasi  = '{{ route('ppdb.pembayaran.konfirmasi-manual', $pendaftaran->id ?? 0) }}';

    // ─────────────────────────────────────────────────────────────────────
    // 1. TOMBOL BAYAR MIDTRANS — Snap.js Flow
    // ─────────────────────────────────────────────────────────────────────
    const btnMidtrans      = document.getElementById('btn-bayar-midtrans');
    const midtransAlert    = document.getElementById('midtrans-alert');
    const midtransAlertMsg = document.getElementById('midtrans-alert-msg');

    if (btnMidtrans) {
        btnMidtrans.addEventListener('click', async function () {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            try {
                const res  = await fetch(urlGetToken, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.success && data.data && data.data.snap_token) {
                    // Buka popup Snap.js
                    snap.pay(data.data.snap_token, {
                        onSuccess: function (result) {
                            showMidtransAlert('Pembayaran berhasil! Halaman akan dimuat ulang...', 'success');
                            setTimeout(() => window.location.reload(), 2000);
                        },
                        onPending: function (result) {
                            showMidtransAlert('Pembayaran pending. Segera selesaikan sesuai instruksi.', 'warning');
                            setTimeout(() => window.location.reload(), 3000);
                        },
                        onError: function (result) {
                            showMidtransAlert(result.status_message ?? 'Terjadi kesalahan pembayaran.', 'danger');
                        },
                        onClose: function () {
                            showMidtransAlert('Popup ditutup. Klik "Bayar Sekarang" untuk mencoba lagi.', 'warning');
                        },
                    });
                } else {
                    showMidtransAlert(data.message ?? 'Gagal mendapatkan token pembayaran.', 'danger');
                }
            } catch (err) {
                showMidtransAlert('Gagal menghubungi server. Silakan coba lagi.', 'danger');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-lightning-charge me-2"></i>Bayar Sekarang{{ $biaya ? ' — ' . $nominalFmt : '' }}';
            }
        });
    }

    function showMidtransAlert(msg, type) {
        if (!midtransAlert || !midtransAlertMsg) return;
        midtransAlert.className = `alert alert-${type} mt-3`;
        midtransAlertMsg.textContent = msg;
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. FILE DROP ZONE — Drag & Drop + Input
    // ─────────────────────────────────────────────────────────────────────
    const dropZone      = document.getElementById('file-drop-zone');
    const inputFile     = document.getElementById('input-bukti');
    const dropContent   = document.getElementById('file-drop-content');
    const filePreview   = document.getElementById('file-preview');
    const filePreviewNm = document.getElementById('file-preview-name');
    const filePreviewSz = document.getElementById('file-preview-size');
    const btnRemoveFl   = document.getElementById('btn-remove-file');

    function showFilePreview(file) {
        if (!file) return;
        const sizeMB = (file.size / 1048576).toFixed(2);
        filePreviewNm.textContent = file.name;
        filePreviewSz.textContent = `${sizeMB} MB`;
        dropContent.classList.add('d-none');
        filePreview.classList.remove('d-none');
        dropZone.style.borderColor = '#15803d';
        dropZone.style.background  = '#f0fdf4';
    }

    function clearFilePreview() {
        if (inputFile) inputFile.value = '';
        dropContent.classList.remove('d-none');
        filePreview.classList.add('d-none');
        dropZone.style.borderColor = '';
        dropZone.style.background  = '';
    }

    if (inputFile) {
        inputFile.addEventListener('change', function () {
            if (this.files && this.files[0]) showFilePreview(this.files[0]);
        });
    }

    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file && inputFile) {
                // Transfer file ke input via DataTransfer
                const dt = new DataTransfer();
                dt.items.add(file);
                inputFile.files = dt.files;
                showFilePreview(file);
            }
        });
    }

    if (btnRemoveFl) {
        btnRemoveFl.addEventListener('click', (e) => {
            e.stopPropagation();
            clearFilePreview();
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. FORM SUBMIT — Upload Bukti Manual via AJAX (XHR + Progress)
    // ─────────────────────────────────────────────────────────────────────
    const formManual     = document.getElementById('form-konfirmasi-manual');
    const btnUpload      = document.getElementById('btn-upload-bukti');
    const progressWrap   = document.getElementById('upload-progress-wrapper');
    const progressBar    = document.getElementById('upload-progress-bar');
    const progressPct    = document.getElementById('upload-pct');
    const resultDiv      = document.getElementById('manual-result');

    if (formManual) {
        formManual.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!inputFile || !inputFile.files || inputFile.files.length === 0) {
                showResult('warning', '<i class="bi bi-exclamation-triangle me-2"></i>Silakan pilih file bukti pembayaran terlebih dahulu.');
                return;
            }

            const formData = new FormData(this);
            formData.append('_token', CSRF);

            // Lock UI
            btnUpload.disabled = true;
            btnUpload.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
            progressWrap.classList.remove('d-none');
            resultDiv.classList.add('d-none');
            progressBar.style.width = '0%';
            progressPct.textContent = '0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', urlKonfirmasi, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF);
            xhr.setRequestHeader('Accept', 'application/json');

            // Progress
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = pct + '%';
                    progressPct.textContent  = pct + '%';
                }
            });

            xhr.onload = function () {
                progressWrap.classList.add('d-none');
                btnUpload.disabled = false;
                btnUpload.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Upload Bukti Bayar';

                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showResult('success',
                            '<i class="bi bi-check-circle me-2"></i>' + (data.message ?? 'Upload berhasil!'));
                        clearFilePreview();
                        // Reload halaman setelah 2 detik untuk update status
                        setTimeout(() => window.location.reload(), 2500);
                    } else {
                        showResult('danger',
                            '<i class="bi bi-exclamation-circle me-2"></i>' + (data.message ?? 'Upload gagal.'));
                    }
                } catch (err) {
                    showResult('danger', '<i class="bi bi-exclamation-circle me-2"></i>Terjadi kesalahan server.');
                }
            };

            xhr.onerror = function () {
                progressWrap.classList.add('d-none');
                btnUpload.disabled = false;
                btnUpload.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Upload Bukti Bayar';
                showResult('danger', '<i class="bi bi-wifi-off me-2"></i>Gagal terhubung ke server. Periksa koneksi Anda.');
            };

            xhr.send(formData);
        });
    }

    function showResult(type, html) {
        if (!resultDiv) return;
        resultDiv.className = `alert alert-${type} rounded-3`;
        resultDiv.innerHTML = html;
        resultDiv.classList.remove('d-none');
        resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. COPY TO CLIPBOARD — Nomor Rekening
    // ─────────────────────────────────────────────────────────────────────
    window.copyToClipboard = function (text, btn) {
        if (!navigator.clipboard) {
            // Fallback
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        } else {
            navigator.clipboard.writeText(text);
        }
        btn.classList.add('copied');
        const origHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(() => {
            btn.innerHTML = origHTML;
            btn.classList.remove('copied');
        }, 2000);
    };

})();
</script>
@endpush
