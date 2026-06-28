<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Laporan Kinerja – {{ $lembaga?->nama ?? 'Sekolah' }}</title>
    <style>
        @page {
            margin: 16mm 14mm 16mm 14mm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            color: #1a1a1a;
            background: #ffffff;
            line-height: 1.45;
        }

        /* ── HEADER ─────────────────────────────────────────────────── */
        .page-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 8px;
        }
        .header-logo-cell {
            width: 60px;
            vertical-align: middle;
            padding-right: 10px;
        }
        .logo-box {
            width: 52px;
            height: 52px;
            border: 2px solid #1e40af;
            border-radius: 4px;
            text-align: center;
            line-height: 52px;
            color: #1e40af;
            font-weight: 700;
            font-size: 9pt;
        }
        .header-center-cell {
            text-align: center;
            vertical-align: middle;
        }
        .header-doc-type {
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1e40af;
            font-weight: 700;
        }
        .header-title {
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a1a1a;
            margin-top: 2px;
        }
        .header-sub {
            font-size: 8pt;
            color: #444;
            margin-top: 2px;
        }

        /* ── METADATA TABLE ─────────────────────────────────────────── */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .meta-table td {
            padding: 5px 10px;
            font-size: 8pt;
            border-right: 1px solid #e2e8f0;
        }
        .meta-table td:last-child { border-right: none; }
        .meta-label {
            font-weight: 700;
            color: #1e40af;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: .05em;
            display: block;
        }
        .meta-val { color: #1a1a1a; }

        /* ── SUMMARY TABLE ──────────────────────────────────────────── */
        .summary-section {
            margin-bottom: 12px;
        }
        .summary-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #1e40af;
            border-bottom: 1.5px solid #1e40af;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th {
            background: #1e40af;
            color: #ffffff;
            padding: 5px 10px;
            font-size: 7.5pt;
            text-align: center;
            font-weight: 700;
        }
        .summary-table td {
            padding: 5px 10px;
            text-align: center;
            font-size: 8.5pt;
            font-weight: 700;
            border: 1px solid #e2e8f0;
        }
        .summary-table tr:nth-child(even) td { background: #f8fafc; }
        .sum-total  { color: #1e40af; background: #eff6ff !important; }
        .sum-hijau  { color: #15803d; background: #f0fdf4 !important; }
        .sum-kuning { color: #b45309; background: #fffbeb !important; }
        .sum-merah  { color: #b91c1c; background: #fef2f2 !important; }

        /* ── CATEGORY SECTION ───────────────────────────────────────── */
        .kat-section {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .kat-header {
            padding: 5px 10px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            border-left: 4px solid currentColor;
        }
        .kat-header-akademik      { background: #eff6ff; color: #1d4ed8; border-left-color: #3b82f6; }
        .kat-header-ppdb          { background: #f0f9ff; color: #0369a1; border-left-color: #0ea5e9; }
        .kat-header-program_kerja { background: #fffbeb; color: #92400e; border-left-color: #f59e0b; }
        .kat-header-kesiswaan     { background: #f0fdf4; color: #166534; border-left-color: #22c55e; }
        .kat-header-sarpras       { background: #f8fafc; color: #475569; border-left-color: #94a3b8; }
        .kat-header-humas         { background: #faf5ff; color: #6d28d9; border-left-color: #a78bfa; }
        .kat-header-umum          { background: #f9fafb; color: #111827; border-left-color: #374151; }

        /* ── KPI TABLE ──────────────────────────────────────────────── */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .kpi-table th {
            background: #f1f5f9;
            color: #334155;
            padding: 4px 8px;
            font-size: 7.5pt;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .kpi-table th.col-nama { text-align: left; }
        .kpi-table td {
            padding: 4px 8px;
            font-size: 8pt;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .kpi-table tr.row-hijau  td { background: #f0fdf4; }
        .kpi-table tr.row-kuning td { background: #fffbeb; }
        .kpi-table tr.row-merah  td { background: #fef2f2; }
        .kpi-table tr.row-nodata td { background: #f9fafb; color: #9ca3af; }
        .kpi-table td.col-pct { text-align: right; font-weight: 700; font-size: 8.5pt; }
        .kpi-table td.col-num { text-align: right; }
        .kpi-table td.col-center { text-align: center; }

        .badge-baik   { color: #15803d; font-weight: 700; }
        .badge-cukup  { color: #b45309; font-weight: 700; }
        .badge-kurang { color: #b91c1c; font-weight: 700; }
        .badge-nodata { color: #9ca3af; }

        /* ── PROGRESS BAR ───────────────────────────────────────────── */
        .prog-wrap {
            background: #e5e7eb;
            height: 6px;
            border-radius: 3px;
            width: 100%;
            overflow: hidden;
        }
        .prog-fill {
            height: 6px;
            border-radius: 3px;
        }
        .prog-hijau  { background: #16a34a; }
        .prog-kuning { background: #d97706; }
        .prog-merah  { background: #dc2626; }
        .prog-none   { background: #d1d5db; }

        /* ── FOOTER ─────────────────────────────────────────────────── */
        .page-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            margin-top: 12px;
            font-size: 7pt;
            color: #6b7280;
        }
        .page-footer table { width: 100%; border-collapse: collapse; }
        .page-footer td { vertical-align: bottom; }

        .no-data-row td {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 8px;
        }
    </style>
</head>
<body>

{{-- ── DOCUMENT HEADER ──────────────────────────────────────────────── --}}
<table class="page-header">
    <tr>
        <td class="header-logo-cell">
            <div class="logo-box">LOGO</div>
        </td>
        <td class="header-center-cell">
            <div class="header-doc-type">Dokumen Resmi</div>
            <div class="header-title">Laporan Kinerja Sekolah</div>
            <div class="header-sub">{{ $lembaga?->nama ?? '—' }}</div>
        </td>
        <td style="width:100px;text-align:right;vertical-align:middle;font-size:7.5pt;color:#444;">
            <div>{{ $lembaga?->npsn ?? '' }}</div>
            <div style="color:#6b7280;">{{ $lembaga?->jenis ?? '' }}</div>
        </td>
    </tr>
</table>

{{-- ── METADATA ─────────────────────────────────────────────────────── --}}
<table class="meta-table">
    <tr>
        <td>
            <span class="meta-label">Tahun Pelajaran</span>
            <span class="meta-val">{{ $tahunPelajaran?->nama ?? '—' }}</span>
        </td>
        <td>
            <span class="meta-label">Periode</span>
            <span class="meta-val">{{ $periode ?? '—' }}</span>
        </td>
        <td>
            <span class="meta-label">Tanggal Cetak</span>
            <span class="meta-val">{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}</span>
        </td>
        <td>
            <span class="meta-label">Dicetak Oleh</span>
            <span class="meta-val">{{ auth()->user()?->name ?? '—' }}</span>
        </td>
    </tr>
</table>

{{-- ── RINGKASAN ─────────────────────────────────────────────────────── --}}
<div class="summary-section">
    <div class="summary-title">Ringkasan Pencapaian KPI</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total KPI</th>
                <th>On Track (≥ 90%)</th>
                <th>Perlu Perhatian (70–89%)</th>
                <th>Kritis (&lt; 70%)</th>
                <th>Belum Ada Data</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="sum-total">{{ $stats['total'] ?? 0 }}</td>
                <td class="sum-hijau">{{ $stats['hijau'] ?? 0 }}</td>
                <td class="sum-kuning">{{ $stats['kuning'] ?? 0 }}</td>
                <td class="sum-merah">{{ $stats['merah'] ?? 0 }}</td>
                <td>{{ $stats['tanpa_data'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── PER-KATEGORI SECTION ─────────────────────────────────────────── --}}
@php
    $kategoriLabels = [
        'akademik'      => 'Akademik',
        'ppdb'          => 'PPDB',
        'program_kerja' => 'Program Kerja',
        'kesiswaan'     => 'Kesiswaan',
        'sarpras'       => 'Sarana & Prasarana',
        'humas'         => 'Humas & Kehumasan',
        'umum'          => 'Umum',
    ];
@endphp

@forelse($grouped as $katKey => $items)
    @php $katLabel = $kategoriLabels[$katKey] ?? ucfirst($katKey); @endphp
    <div class="kat-section">
        <div class="kat-header kat-header-{{ $katKey }}">{{ strtoupper($katLabel) }}</div>
        <table class="kpi-table">
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th class="col-nama">Nama Indikator</th>
                    <th style="width:7%">Satuan</th>
                    <th style="width:8%">Target</th>
                    <th style="width:9%">Realisasi</th>
                    <th style="width:10%">Pencapaian %</th>
                    <th style="width:14%">Progres</th>
                    <th style="width:8%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $kpi)
                    @php
                        $realisasi  = $kpi->latestRealisasi;
                        $pencapaian = $kpi->pencapaian ?? 0;
                        $tl         = $kpi->traffic_light;
                        $hasPct     = $realisasi !== null;
                        $barWidth   = $hasPct ? min(100, max(0, $pencapaian)) : 0;

                        $rowClass   = $hasPct ? 'row-' . $tl : 'row-nodata';
                        $statusText = match(true) {
                            !$hasPct        => 'Belum Ada Data',
                            $pencapaian >= 90 => 'Baik',
                            $pencapaian >= 70 => 'Cukup',
                            default         => 'Kurang',
                        };
                        $statusCls = match($statusText) {
                            'Baik'   => 'badge-baik',
                            'Cukup'  => 'badge-cukup',
                            'Kurang' => 'badge-kurang',
                            default  => 'badge-nodata',
                        };
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="col-center">{{ $idx + 1 }}</td>
                        <td>
                            {{ $kpi->nama_indikator }}
                            @if($kpi->is_auto)
                                <span style="font-size:6.5pt;color:#0891b2;font-style:italic;"> [Auto]</span>
                            @endif
                        </td>
                        <td class="col-center">{{ $kpi->satuan ?? '—' }}</td>
                        <td class="col-num">{{ number_format($kpi->target, 1) }}</td>
                        <td class="col-num">
                            {{ $realisasi ? number_format($realisasi->nilai_realisasi, 1) : '—' }}
                        </td>
                        <td class="col-pct">
                            @if($hasPct)
                                {{ number_format($pencapaian, 1) }}%
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="prog-wrap">
                                <div class="prog-fill prog-{{ $hasPct ? $tl : 'none' }}" style="width:{{ $barWidth }}%"></div>
                            </div>
                        </td>
                        <td class="col-center {{ $statusCls }}">{{ $statusText }}</td>
                    </tr>
                @empty
                    <tr class="no-data-row">
                        <td colspan="8">Tidak ada indikator di kategori ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@empty
    <div style="text-align:center;padding:20px;color:#9ca3af;font-style:italic;">
        Tidak ada data KPI untuk laporan ini.
    </div>
@endforelse

{{-- ── FOOTER ───────────────────────────────────────────────────────── --}}
<div class="page-footer">
    <table>
        <tr>
            <td>
                Dicetak oleh: <strong>{{ auth()->user()?->name ?? '—' }}</strong>
                &nbsp;|&nbsp;
                Tanggal: <strong>{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }}</strong>
                &nbsp;|&nbsp;
                Sistem Informasi Manajemen Sekolah
            </td>
            <td style="text-align:right;">
                Laporan Kinerja — {{ $lembaga?->nama ?? '' }} — {{ $tahunPelajaran?->nama ?? '' }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
