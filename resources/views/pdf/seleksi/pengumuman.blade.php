<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pengumuman Hasil Seleksi – {{ $jalur->nama ?? 'PPDB' }}</title>
    <style>
        @page { margin: 2cm 2cm 2cm 2cm; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1a1a2e;
            line-height: 1.5;
        }

        /* ── HEADER ───────────────────────────────────── */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #1a56db;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-logo {
            width: 70px;
            height: 70px;
            margin-right: 16px;
        }
        .header-text h1 {
            font-size: 14pt;
            font-weight: 700;
            color: #1a56db;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text h2 {
            font-size: 11pt;
            font-weight: 600;
            color: #374151;
            margin-top: 2px;
        }
        .header-text p {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ── JUDUL PENGUMUMAN ─────────────────────────── */
        .title-block {
            text-align: center;
            margin: 16px 0 12px;
        }
        .title-block h3 {
            font-size: 13pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #1a1a2e;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .title-block p {
            font-size: 10pt;
            color: #374151;
            margin-top: 4px;
        }

        /* ── INFO BOX ─────────────────────────────────── */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f0f4ff;
            border-radius: 4px;
            padding: 10px 14px;
        }
        .info-grid .row {
            display: table-row;
        }
        .info-grid .label {
            display: table-cell;
            font-weight: 600;
            width: 35%;
            padding: 2px 0;
            color: #374151;
        }
        .info-grid .colon {
            display: table-cell;
            width: 2%;
            color: #374151;
            padding: 2px 4px;
        }
        .info-grid .value {
            display: table-cell;
            color: #1a1a2e;
            padding: 2px 0;
        }

        /* ── BADGE STATUS ─────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8.5pt;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .badge-lulus    { background: #d1fae5; color: #065f46; }
        .badge-cadangan { background: #fef3c7; color: #92400e; }

        /* ── TABEL ────────────────────────────────────── */
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9pt;
        }
        .ranking-table thead tr {
            background-color: #1a56db;
            color: #ffffff;
        }
        .ranking-table thead th {
            padding: 7px 8px;
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .ranking-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .ranking-table tbody tr:hover {
            background-color: #e0e7ff;
        }
        .ranking-table tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .ranking-table tbody td.center { text-align: center; }
        .ranking-table tbody td.right  { text-align: right; }

        /* ── FOOTER ───────────────────────────────────── */
        .footer {
            margin-top: 24px;
            border-top: 1px solid #d1d5db;
            padding-top: 14px;
            display: table;
            width: 100%;
        }
        .footer .section-ttd {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 12px;
        }
        .footer .section-ttd p { font-size: 9pt; color: #374151; }
        .footer .ttd-blank { height: 55px; }
        .footer .ttd-name {
            font-weight: 700;
            border-top: 1px solid #374151;
            padding-top: 4px;
            display: inline-block;
            min-width: 160px;
        }

        .page-number {
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 10px;
        }

        .divider-section {
            font-size: 10pt;
            font-weight: 700;
            color: #1a56db;
            margin: 16px 0 6px;
            border-left: 4px solid #1a56db;
            padding-left: 8px;
        }
    </style>
</head>
<body>

    {{-- ── HEADER ──────────────────────────────────────── --}}
    <div class="header">
        <div class="header-text">
            <h1>Pengumuman Hasil Seleksi PPDB</h1>
            <h2>Tahun Pelajaran {{ $jalur->pembukaanPpdb?->tahunPelajaran?->kode_tahun ?? now()->year }}/{{ now()->year + 1 }}</h2>
            <p>Dicetak: {{ $tanggal_cetak }}</p>
        </div>
    </div>

    {{-- ── JUDUL ────────────────────────────────────────── --}}
    <div class="title-block">
        <h3>Pengumuman Kelulusan</h3>
        <p>{{ $jalur->nama ?? 'Jalur PPDB' }}</p>
    </div>

    {{-- ── INFO GRID ────────────────────────────────────── --}}
    <div class="info-grid">
        <div class="row">
            <div class="label">Jalur Pendaftaran</div>
            <div class="colon">:</div>
            <div class="value"><strong>{{ $jalur->nama }}</strong></div>
        </div>
        <div class="row">
            <div class="label">Kuota</div>
            <div class="colon">:</div>
            <div class="value">{{ $jalur->kuota }} peserta</div>
        </div>
        <div class="row">
            <div class="label">Total Diterima</div>
            <div class="colon">:</div>
            <div class="value">{{ $total_lulus }} peserta lulus + {{ $total_cadangan }} cadangan</div>
        </div>
        <div class="row">
            <div class="label">Tanggal Pengumuman</div>
            <div class="colon">:</div>
            <div class="value">{{ $tanggal_cetak }}</div>
        </div>
    </div>

    {{-- ── TABEL LULUS ──────────────────────────────────── --}}
    <div class="divider-section">A. Peserta Diterima (Lulus)</div>

    <table class="ranking-table">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:10%">No. Daftar</th>
                <th style="width:30%">Nama Peserta</th>
                <th style="width:14%">NISN</th>
                <th style="width:8%" class="center">Peringkat</th>
                <th style="width:10%" class="right">Total Nilai</th>
                <th style="width:12%" class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($hasil_list->where('status_kelulusan', 'lulus') as $item)
                <tr>
                    <td class="center">{{ $no++ }}</td>
                    <td class="center">{{ $item->pendaftaran?->no_pendaftaran ?? '-' }}</td>
                    <td>{{ $item->pendaftaran?->peserta?->nama_lengkap ?? '-' }}</td>
                    <td class="center">{{ $item->pendaftaran?->peserta?->nisn ?? '-' }}</td>
                    <td class="center"><strong>{{ $item->peringkat }}</strong></td>
                    <td class="right">{{ number_format((float) $item->total_nilai, 2) }}</td>
                    <td class="center">
                        <span class="badge badge-lulus">LULUS</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center" style="color:#9ca3af;padding:12px;">
                        Tidak ada peserta lulus.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── TABEL CADANGAN ───────────────────────────────── --}}
    @if($hasil_list->where('status_kelulusan', 'cadangan')->isNotEmpty())
        <div class="divider-section" style="margin-top:18px;">B. Peserta Cadangan</div>

        <table class="ranking-table">
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th style="width:10%">No. Daftar</th>
                    <th style="width:30%">Nama Peserta</th>
                    <th style="width:14%">NISN</th>
                    <th style="width:8%" class="center">Peringkat</th>
                    <th style="width:10%" class="right">Total Nilai</th>
                    <th style="width:12%" class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($hasil_list->where('status_kelulusan', 'cadangan') as $item)
                    <tr>
                        <td class="center">{{ $no++ }}</td>
                        <td class="center">{{ $item->pendaftaran?->no_pendaftaran ?? '-' }}</td>
                        <td>{{ $item->pendaftaran?->peserta?->nama_lengkap ?? '-' }}</td>
                        <td class="center">{{ $item->pendaftaran?->peserta?->nisn ?? '-' }}</td>
                        <td class="center"><strong>{{ $item->peringkat }}</strong></td>
                        <td class="right">{{ number_format((float) $item->total_nilai, 2) }}</td>
                        <td class="center">
                            <span class="badge badge-cadangan">CADANGAN</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── FOOTER TTD ───────────────────────────────────── --}}
    <div class="footer">
        <div class="section-ttd">
            <p>Mengetahui,</p>
            <p>Kepala Sekolah</p>
            <div class="ttd-blank"></div>
            <p><span class="ttd-name">( _________________________ )</span></p>
        </div>
        <div class="section-ttd">
            <p>Ditetapkan di, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Panitia PPDB {{ now()->year }}</p>
            <div class="ttd-blank"></div>
            <p><span class="ttd-name">( _________________________ )</span></p>
        </div>
    </div>

    <p class="page-number">
        Dokumen resmi – Dicetak otomatis oleh Sistem PPDB {{ now()->year }}
    </p>

</body>
</html>
