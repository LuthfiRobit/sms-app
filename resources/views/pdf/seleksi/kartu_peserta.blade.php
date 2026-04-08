<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Kartu Peserta – {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page {
            margin: 0;
            size: A5 portrait;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #1a1a2e;
            background: #ffffff;
            width: 148mm;
            height: 210mm;
            position: relative;
        }

        /* ── OUTER WRAPPER ──────────────────────────────── */
        .card-wrapper {
            border: 2px solid #1a56db;
            border-radius: 8px;
            margin: 8mm;
            padding: 0;
            overflow: hidden;
            min-height: 190mm;
        }

        /* ── HEADER STRIP ───────────────────────────────── */
        .card-header {
            background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
            color: #ffffff;
            padding: 10px 14px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 12pt;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .card-header h2 {
            font-size: 9pt;
            font-weight: 400;
            opacity: 0.85;
            margin-top: 2px;
        }
        .card-header .no-daftar {
            font-size: 11pt;
            font-weight: 700;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 4px;
            display: inline-block;
            padding: 3px 12px;
            margin-top: 6px;
            letter-spacing: 2px;
        }

        /* ── BODY ───────────────────────────────────────── */
        .card-body {
            display: table;
            width: 100%;
            padding: 14px;
        }

        /* ── KOLOM FOTO ─────────────────────────────────── */
        .col-foto {
            display: table-cell;
            width: 90px;
            vertical-align: top;
            padding-right: 10px;
        }
        .foto-box {
            width: 80px;
            height: 100px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .foto-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .foto-placeholder {
            width: 80px;
            height: 100px;
            border: 2px dashed #9ca3af;
            border-radius: 4px;
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            color: #9ca3af;
            font-size: 7.5pt;
            line-height: 1.3;
        }

        /* ── KOLOM DATA ─────────────────────────────────── */
        .col-data {
            display: table-cell;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table tr td {
            padding: 2.5px 0;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .data-table tr td.label {
            font-weight: 600;
            color: #374151;
            width: 38%;
        }
        .data-table tr td.colon {
            width: 4%;
            color: #374151;
            padding-left: 2px;
            padding-right: 4px;
        }
        .data-table tr td.value {
            color: #1a1a2e;
            font-weight: 400;
        }
        .data-table tr td.value.strong {
            font-weight: 700;
        }

        /* ── DIVIDER ─────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px dashed #d1d5db;
            margin: 10px 14px;
        }

        /* ── STATUS BADGE ────────────────────────────────── */
        .status-row {
            text-align: center;
            padding: 8px 14px 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 18px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .status-lulus    { background: #d1fae5; color: #065f46; border: 1.5px solid #6ee7b7; }
        .status-cadangan { background: #fef3c7; color: #92400e; border: 1.5px solid #fcd34d; }
        .status-tidak    { background: #fee2e2; color: #991b1b; border: 1.5px solid #fca5a5; }
        .status-pending  { background: #e0e7ff; color: #3730a3; border: 1.5px solid #a5b4fc; }

        /* ── QR CODE AREA ────────────────────────────────── */
        .qr-section {
            display: table;
            width: 100%;
            padding: 8px 14px 10px;
        }
        .qr-image {
            display: table-cell;
            vertical-align: middle;
            width: 70px;
        }
        .qr-image img {
            width: 65px;
            height: 65px;
        }
        .qr-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }
        .qr-text p {
            font-size: 7.5pt;
            color: #6b7280;
            line-height: 1.5;
        }
        .qr-text strong {
            font-size: 8.5pt;
            color: #374151;
        }

        /* ── FOOTER STRIPE ───────────────────────────────── */
        .card-footer {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            padding: 6px 14px;
            font-size: 7.5pt;
            color: #9ca3af;
        }
    </style>
</head>
<body>

<div class="card-wrapper">

    {{-- ── HEADER ──────────────────────────────────────────── --}}
    <div class="card-header">
        <h1>Kartu Peserta PPDB</h1>
        <h2>
            {{ $jalur->nama ?? 'Jalur PPDB' }}
            · T.P. {{ $tahun->kode_tahun ?? now()->year }}
        </h2>
        <div class="no-daftar">{{ $pendaftaran->no_pendaftaran }}</div>
    </div>

    {{-- ── BODY ────────────────────────────────────────────── --}}
    <div class="card-body">

        {{-- FOTO --}}
        <div class="col-foto">
            @if(!empty($foto_base64))
                <div class="foto-box">
                    <img src="{{ $foto_base64 }}" alt="Foto Peserta" />
                </div>
            @else
                <div class="foto-placeholder">
                    Foto<br/>Peserta
                </div>
            @endif
        </div>

        {{-- DATA PESERTA --}}
        <div class="col-data">
            <table class="data-table">
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="colon">:</td>
                    <td class="value strong">{{ $peserta?->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NISN</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peserta?->nisn ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NIK</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $peserta?->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tempat, Tgl Lahir</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $peserta?->tempat_lahir ?? '-' }},
                        {{ $peserta?->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $peserta?->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Jalur Seleksi</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $jalur?->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Daftar</td>
                    <td class="colon">:</td>
                    <td class="value">
                        {{ $pendaftaran->tanggal_daftar?->translatedFormat('d F Y') ?? '-' }}
                    </td>
                </tr>
            </table>
        </div>

    </div><!-- /card-body -->

    <hr class="divider" />

    {{-- ── STATUS KELULUSAN ────────────────────────────────── --}}
    <div class="status-row">
        @if($hasil)
            <div style="font-size:7.5pt;color:#6b7280;margin-bottom:4px;">
                Peringkat ke-<strong>{{ $hasil->peringkat }}</strong>
                &nbsp;|&nbsp;
                Total Nilai: <strong>{{ number_format((float) $hasil->total_nilai, 2) }}</strong>
            </div>
            @php
                $statusClass = match($hasil->status_kelulusan) {
                    'lulus'       => 'status-lulus',
                    'cadangan'    => 'status-cadangan',
                    'tidak_lulus' => 'status-tidak',
                    default       => 'status-pending',
                };
                $statusLabel = match($hasil->status_kelulusan) {
                    'lulus'       => 'LULUS',
                    'cadangan'    => 'CADANGAN',
                    'tidak_lulus' => 'TIDAK LULUS',
                    default       => 'MENUNGGU',
                };
            @endphp
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        @else
            <span class="status-badge status-pending">PROSES SELEKSI</span>
        @endif
    </div>

    <hr class="divider" />

    {{-- ── QR CODE ──────────────────────────────────────────── --}}
    <div class="qr-section">
        <div class="qr-image">
            @if(!empty($qr_base64))
                <img src="{{ $qr_base64 }}" alt="QR Code Verifikasi" />
            @else
                <div style="width:65px;height:65px;background:#f3f4f6;border:1px dashed #d1d5db;
                            display:table-cell;vertical-align:middle;text-align:center;
                            font-size:7pt;color:#9ca3af;">QR N/A</div>
            @endif
        </div>
        <div class="qr-text">
            <strong>Scan untuk Verifikasi</strong>
            <p>
                Kartu ini merupakan dokumen resmi PPDB.<br />
                Scan QR code untuk memverifikasi keaslian<br />
                kartu peserta dengan nomor:<br />
                <strong>{{ $pendaftaran->no_pendaftaran }}</strong>
            </p>
        </div>
    </div>

    {{-- ── FOOTER ───────────────────────────────────────────── --}}
    <div class="card-footer">
        Dicetak: {{ $tanggal_cetak }} &nbsp;·&nbsp;
        Dokumen resmi Sistem PPDB {{ now()->year }}
    </div>

</div><!-- /card-wrapper -->

</body>
</html>
