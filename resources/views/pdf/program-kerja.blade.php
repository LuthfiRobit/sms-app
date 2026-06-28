<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Program Kerja &ndash; {{ $prog->nama_program }}</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
            size: A4 portrait;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1c1c1c;
            background: #ffffff;
            line-height: 1.4;
        }

        /* ── HEADER ─────────────────────────────────────────── */
        .header-wrap {
            border-bottom: 2.5px solid #1c3d6e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo-cell {
            width: 65px;
            vertical-align: middle;
            padding-right: 10px;
        }
        .logo-box {
            width: 58px; height: 58px;
            border: 2px solid #1c3d6e; border-radius: 4px;
            text-align: center; line-height: 58px;
            color: #1c3d6e; font-weight: 700; font-size: 10pt;
        }
        .header-center { text-align: center; vertical-align: middle; }
        .header-title {
            font-size: 11pt; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: #1c3d6e;
        }
        .header-sekolah {
            font-size: 13pt; font-weight: 700; text-transform: uppercase;
            color: #1c1c1c; margin-top: 2px;
        }
        .header-sub { font-size: 8pt; color: #555; margin-top: 2px; }
        .header-right {
            width: 110px; text-align: right; vertical-align: middle;
            font-size: 7.5pt; color: #444;
        }

        /* ── SECTION TITLE ──────────────────────────────────── */
        .section-title {
            font-size: 9.5pt; font-weight: 700; color: #1c3d6e;
            margin: 10px 0 5px 0; text-transform: uppercase; letter-spacing: .4px;
        }

        /* ── INFO TABLE ─────────────────────────────────────── */
        .info-table {
            width: 100%; border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 4px 8px; font-size: 8.5pt;
            border: 1px solid #d1d5db;
        }
        .info-table .lbl {
            font-weight: 700; background: #f1f5f9; width: 22%;
            color: #374151;
        }

        /* ── STAT BAR ───────────────────────────────────────── */
        .stat-bar {
            width: 100%; border-collapse: collapse;
            margin-bottom: 10px; border: 1px solid #d1d5db; border-radius: 4px;
        }
        .stat-bar td {
            text-align: center; padding: 6px 4px;
            border-right: 1px solid #d1d5db; font-size: 8pt;
        }
        .stat-bar td:last-child { border-right: none; }
        .stat-val { font-size: 14pt; font-weight: 700; display: block; line-height: 1.1; }
        .stat-label { font-size: 7pt; color: #555; text-transform: uppercase; letter-spacing: .4px; }
        .stat-total  { background: #f8fafc; }
        .stat-selesai { background: #d1fae5; color: #065f46; }
        .stat-proses { background: #fef3c7; color: #92400e; }
        .stat-belum  { background: #f1f5f9; color: #4b5563; }
        .stat-anggaran { background: #eff6ff; color: #1e40af; }
        .stat-persen { background: #1c3d6e; color: #fff; }

        /* ── PROGRESS BAR ───────────────────────────────────── */
        .progress-outer {
            width: 100%; background: #e5e7eb; border-radius: 4px;
            height: 8px; margin-top: 4px;
        }
        .progress-inner {
            height: 8px; border-radius: 4px;
            background: #10b981;
        }

        /* ── MAIN TABLE ─────────────────────────────────────── */
        .main-table {
            width: 100%; border-collapse: collapse;
            margin-bottom: 14px;
        }
        .main-table th {
            background: #1c3d6e; color: #fff;
            font-size: 7.5pt; padding: 5px 4px;
            text-align: center; border: 1px solid #15336e;
        }
        .main-table td {
            font-size: 8pt; padding: 4px 4px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }
        .main-table tr.row-selesai td    { background: #f0fdf4; }
        .main-table tr.row-proses td     { background: #fffbeb; }
        .main-table tr.row-dibatalkan td { background: #fef2f2; }
        .main-table tr.row-belum td      { background: #fff; }
        .main-table td.num { text-align: center; }
        .main-table td.rp  { text-align: right; }
        .status-badge {
            font-size: 7pt; font-weight: 700; padding: 1px 5px;
            border-radius: 3px; display: inline-block;
        }
        .badge-selesai    { background: #dcfce7; color: #166534; }
        .badge-proses     { background: #fef3c7; color: #92400e; }
        .badge-belum      { background: #f1f5f9; color: #374151; }
        .badge-dibatalkan { background: #fee2e2; color: #991b1b; }

        /* ── PENGESAHAN ─────────────────────────────────────── */
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .ttd-table td { width: 50%; padding: 8px 12px; vertical-align: top; }
        .ttd-label { font-size: 8.5pt; font-weight: 700; margin-bottom: 2px; }
        .ttd-space { height: 48px; }
        .ttd-name  {
            font-size: 8.5pt; font-weight: 700;
            border-top: 1px solid #555; padding-top: 3px;
            min-width: 140px; display: inline-block;
        }
        .ttd-nip { font-size: 7.5pt; color: #444; margin-top: 2px; }

        .footer-note {
            font-size: 7pt; color: #888; text-align: center;
            margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 5px;
        }
    </style>
</head>
<body>

{{-- ─── HEADER ───────────────────────────────────────────────────────── --}}
<div class="header-wrap">
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <div class="logo-box">LOGO</div>
            </td>
            <td class="header-center">
                <div class="header-sub">PROGRAM KERJA {{ strtoupper($prog->bidang ?? '') }}</div>
                <div class="header-sekolah">{{ optional($prog->lembaga)->nama ?? '' }}</div>
                <div class="header-title">{{ $prog->nama_program }}</div>
            </td>
            <td class="header-right">
                Tahun Pelajaran<br>
                <strong>{{ optional($prog->tahunPelajaran)->nama ?? '-' }}</strong><br><br>
                Dicetak:<br>
                {{ now()->format('d/m/Y') }}
            </td>
        </tr>
    </table>
</div>

{{-- ─── INFO PROGRAM ──────────────────────────────────────────────────── --}}
<div class="section-title">Informasi Program</div>
<table class="info-table">
    <tr>
        <td class="lbl">Nama Program</td>
        <td>{{ $prog->nama_program }}</td>
        <td class="lbl">Bidang</td>
        <td>{{ \App\Models\ProgramKerja\ProgramKerja::bidangConfig()[$prog->bidang] ?? ucfirst($prog->bidang) }}</td>
    </tr>
    <tr>
        <td class="lbl">Lembaga</td>
        <td>{{ optional($prog->lembaga)->nama ?? '-' }}</td>
        <td class="lbl">Tahun Pelajaran</td>
        <td>{{ optional($prog->tahunPelajaran)->nama ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Status</td>
        <td>{{ ucfirst($prog->status) }}</td>
        <td class="lbl">Disetujui Pada</td>
        <td>{{ $prog->disetujui_at ? $prog->disetujui_at->format('d M Y') : '—' }}</td>
    </tr>
    @if($prog->tujuan)
    <tr>
        <td class="lbl">Tujuan</td>
        <td colspan="3">{{ $prog->tujuan }}</td>
    </tr>
    @endif
    @if($prog->deskripsi)
    <tr>
        <td class="lbl">Keterangan</td>
        <td colspan="3">{{ $prog->deskripsi }}</td>
    </tr>
    @endif
</table>

{{-- ─── RINGKASAN PROGRESS ────────────────────────────────────────────── --}}
<div class="section-title">Ringkasan Progress</div>
<table class="stat-bar">
    <tr>
        <td class="stat-total">
            <span class="stat-val">{{ $summary['total'] }}</span>
            <span class="stat-label">Total Kegiatan</span>
        </td>
        <td class="stat-selesai">
            <span class="stat-val">{{ $summary['selesai'] }}</span>
            <span class="stat-label">Selesai</span>
        </td>
        <td class="stat-proses">
            <span class="stat-val">{{ $summary['proses'] }}</span>
            <span class="stat-label">Proses</span>
        </td>
        <td class="stat-belum">
            <span class="stat-val">{{ $summary['belum'] }}</span>
            <span class="stat-label">Belum Mulai</span>
        </td>
        <td class="stat-anggaran">
            <span class="stat-val" style="font-size:9pt;">Rp {{ number_format($summary['total_anggaran'] ?? 0, 0, ',', '.') }}</span>
            <span class="stat-label">Total Anggaran</span>
        </td>
        <td class="stat-anggaran">
            <span class="stat-val" style="font-size:9pt;">Rp {{ number_format($summary['realisasi_anggaran'] ?? 0, 0, ',', '.') }}</span>
            <span class="stat-label">Realisasi Anggaran</span>
        </td>
        <td class="stat-persen">
            <span class="stat-val">{{ $summary['progress_persen'] ?? 0 }}%</span>
            <span class="stat-label" style="color:#cce;letter-spacing:.4px;">Progress</span>
        </td>
    </tr>
</table>

{{-- ─── TABEL KEGIATAN ────────────────────────────────────────────────── --}}
@php
    $bulanNama = [
        1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun',
        7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des',
    ];
    $statusKegMap = [
        'belum'      => ['badge-belum',      'Belum'],
        'proses'     => ['badge-proses',     'Proses'],
        'selesai'    => ['badge-selesai',    'Selesai'],
        'dibatalkan' => ['badge-dibatalkan', 'Dibatalkan'],
    ];
@endphp

<div class="section-title">Daftar Kegiatan</div>
<table class="main-table">
    <thead>
        <tr>
            <th style="width:4%">No</th>
            <th style="width:22%">Nama Kegiatan</th>
            <th style="width:11%">PJ</th>
            <th style="width:14%">Target</th>
            <th style="width:11%">Anggaran (Rp)</th>
            <th style="width:9%">Bulan</th>
            <th style="width:7%">Real.%</th>
            <th style="width:10%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($prog->kegiatan as $i => $k)
            @php
                $rowClass = 'row-' . ($k->status_kegiatan ?? 'belum');
                [$badgeClass, $badgeLabel] = $statusKegMap[$k->status_kegiatan] ?? ['badge-belum', '-'];
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="num">{{ $i + 1 }}</td>
                <td>
                    {{ $k->nama_kegiatan }}
                    @if($k->indikator)
                        <br><span style="font-size:7pt;color:#555;">{{ $k->indikator }}</span>
                    @endif
                </td>
                <td>{{ $k->penanggung_jawab ?? '—' }}</td>
                <td>{{ $k->target ?? '—' }}</td>
                <td class="rp">
                    {{ $k->anggaran ? number_format($k->anggaran, 0, ',', '.') : '—' }}
                </td>
                <td class="num">
                    {{ $bulanNama[$k->bulan_mulai] ?? '-' }}
                    @if($k->bulan_selesai && $k->bulan_selesai !== $k->bulan_mulai)
                        – {{ $bulanNama[$k->bulan_selesai] ?? '-' }}
                    @endif
                </td>
                <td class="num">{{ $k->realisasi_persen ?? 0 }}%</td>
                <td class="num">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#888; padding:10px;">
                    Tidak ada kegiatan.
                </td>
            </tr>
        @endforelse
        {{-- Totals row --}}
        @if($prog->kegiatan->count() > 0)
            <tr>
                <td colspan="4" style="text-align:right; font-weight:700; background:#f8fafc;">Total</td>
                <td class="rp" style="font-weight:700; background:#f8fafc;">
                    {{ number_format($summary['total_anggaran'] ?? 0, 0, ',', '.') }}
                </td>
                <td colspan="3" style="background:#f8fafc;"></td>
            </tr>
        @endif
    </tbody>
</table>

{{-- ─── PENGESAHAN ────────────────────────────────────────────────────── --}}
<table class="ttd-table">
    <tr>
        <td>
            <div class="ttd-label">Mengetahui,</div>
            <div style="font-size:8pt;color:#555;">Kepala Sekolah</div>
            <div class="ttd-space"></div>
            <div style="text-align:center;">
                <span class="ttd-name">______________________________</span>
            </div>
            <div class="ttd-nip" style="text-align:center;">NIP. ____________________</div>
        </td>
        <td style="text-align:right;">
            <div class="ttd-label">
                {{ optional($prog->lembaga)->nama ?? '' }},
                {{ now()->format('d F Y') }}
            </div>
            <div style="font-size:8pt;color:#555;">Koordinator Bidang {{ ucfirst($prog->bidang ?? '') }}</div>
            <div class="ttd-space"></div>
            <div style="text-align:center;">
                <span class="ttd-name">______________________________</span>
            </div>
            <div class="ttd-nip" style="text-align:center;">NIP. ____________________</div>
        </td>
    </tr>
</table>

<div class="footer-note">
    Dokumen ini dicetak secara otomatis oleh Sistem Manajemen Sekolah &mdash; {{ now()->format('d M Y, H:i') }} WIB
</div>

</body>
</html>
