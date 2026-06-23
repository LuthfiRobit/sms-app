<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Raport – {{ $siswa?->nama_lengkap ?? 'Siswa' }}</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1c1c1c;
            background: #ffffff;
            line-height: 1.4;
        }

        /* ── HEADER ──────────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #1c3d6e;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-logo-cell {
            width: 70px;
            vertical-align: middle;
            padding-right: 10px;
        }
        .logo-box {
            width: 60px;
            height: 60px;
            border: 2px solid #1c3d6e;
            border-radius: 4px;
            text-align: center;
            vertical-align: middle;
            color: #1c3d6e;
            font-weight: 700;
            font-size: 10pt;
            line-height: 60px;
        }
        .header-center-cell {
            text-align: center;
            vertical-align: middle;
        }
        .header-title {
            font-size: 13pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1c3d6e;
        }
        .header-sekolah {
            font-size: 11pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #1c1c1c;
            margin-top: 2px;
        }
        .header-alamat {
            font-size: 8pt;
            color: #555555;
            margin-top: 2px;
        }
        .header-right-cell {
            width: 110px;
            text-align: right;
            vertical-align: middle;
            font-size: 8pt;
            color: #444444;
        }
        .header-right-cell .semester-label {
            font-size: 7.5pt;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-right-cell .semester-value {
            font-size: 9pt;
            font-weight: 700;
            color: #1c3d6e;
            display: block;
            margin-bottom: 4px;
        }

        /* ── SECTION HEADING ─────────────────────────────────── */
        .section-heading {
            background-color: #1c3d6e;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 3px 8px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        /* ── IDENTITAS TABLE ─────────────────────────────────── */
        .identitas-table {
            width: 100%;
            border-collapse: collapse;
        }
        .identitas-table tr td {
            padding: 2.5px 4px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .identitas-table .id-label {
            width: 20%;
            font-weight: 600;
            color: #444444;
        }
        .identitas-table .id-colon {
            width: 2%;
            color: #444444;
        }
        .identitas-table .id-value {
            width: 28%;
            color: #1c1c1c;
        }

        /* ── NILAI TABLE ─────────────────────────────────────── */
        .nilai-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 4px;
        }
        .nilai-table th {
            background-color: #2a4f85;
            color: #ffffff;
            text-align: center;
            padding: 4px 3px;
            border: 1px solid #1c3d6e;
            font-weight: 600;
            font-size: 7.5pt;
        }
        .nilai-table th.th-mapel {
            text-align: left;
            padding-left: 6px;
        }
        .nilai-table td {
            border: 1px solid #d0d7e6;
            padding: 3px 4px;
            vertical-align: middle;
        }
        .nilai-table td.td-no {
            text-align: center;
            color: #777;
            font-size: 7.5pt;
        }
        .nilai-table td.td-mapel {
            padding-left: 6px;
        }
        .nilai-table td.td-center {
            text-align: center;
        }
        .nilai-table td.td-na {
            text-align: center;
            font-weight: 700;
        }
        .row-lulus {
            background-color: #ffffff;
        }
        .row-tidak-lulus {
            background-color: #fff0f0;
        }
        .row-alt {
            background-color: #f5f7fb;
        }
        .predikat-a { color: #1a6b36; font-weight: 700; }
        .predikat-b { color: #1a4d8f; font-weight: 700; }
        .predikat-c { color: #8a6200; font-weight: 700; }
        .predikat-d { color: #b35900; font-weight: 700; }
        .predikat-e { color: #b00020; font-weight: 700; }

        /* ── ABSENSI TABLE ───────────────────────────────────── */
        .absensi-table {
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 4px;
        }
        .absensi-table th {
            background-color: #e8eef6;
            border: 1px solid #b8c8df;
            padding: 4px 12px;
            text-align: center;
            font-size: 8pt;
            font-weight: 600;
            color: #1c3d6e;
        }
        .absensi-table td {
            border: 1px solid #b8c8df;
            padding: 4px 12px;
            text-align: center;
            font-size: 9pt;
            font-weight: 700;
        }
        .absensi-total {
            background-color: #2a4f85;
            color: #ffffff;
        }

        /* ── CATATAN WALI KELAS ──────────────────────────────── */
        .catatan-box {
            border: 1px solid #c8d5e8;
            border-radius: 3px;
            padding: 8px 10px;
            min-height: 42px;
            font-size: 8.5pt;
            color: #333333;
            background: #fafcff;
            margin-top: 4px;
        }

        /* ── PENGESAHAN ──────────────────────────────────────── */
        .pengesahan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 8.5pt;
        }
        .pengesahan-table td {
            vertical-align: top;
            padding: 4px 8px;
            text-align: center;
        }
        .sign-line {
            border-bottom: 1px solid #1c1c1c;
            margin: 40px auto 4px auto;
            width: 80%;
        }
        .sign-name {
            font-weight: 700;
            font-size: 8.5pt;
            text-decoration: underline;
        }
        .sign-role {
            font-size: 7.5pt;
            color: #555555;
        }

        /* ── FOOTER ──────────────────────────────────────────── */
        .page-footer {
            border-top: 1px solid #c8d5e8;
            margin-top: 14px;
            padding-top: 5px;
            text-align: center;
            font-size: 7pt;
            color: #999999;
        }
    </style>
</head>
<body>

{{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
<table class="header-table">
    <tr>
        {{-- Logo / kode lembaga --}}
        <td class="header-logo-cell">
            <div class="logo-box">
                {{ strtoupper($lembaga?->kode ?? '—') }}
            </div>
        </td>

        {{-- Center: judul rapor --}}
        <td class="header-center-cell">
            <div class="header-title">Rapor Peserta Didik</div>
            <div class="header-sekolah">{{ strtoupper($lembaga?->nama ?? '') }}</div>
            @if(!empty($lembaga?->alamat))
                <div class="header-alamat">{{ $lembaga->alamat }}</div>
            @endif
        </td>

        {{-- Right: semester & tahun --}}
        <td class="header-right-cell">
            <span class="semester-label">Semester</span>
            <span class="semester-value">{{ $pengajuan->semester->nama }}</span>
            <span class="semester-label">Tahun Pelajaran</span>
            <span class="semester-value">{{ $pengajuan->tahunPelajaran->nama }}</span>
        </td>
    </tr>
</table>

{{-- ── SECTION 1: IDENTITAS PESERTA DIDIK ──────────────────────────────── --}}
<div class="section-heading">Identitas Peserta Didik</div>

<table class="identitas-table">
    <tr>
        <td class="id-label">Nama Lengkap</td>
        <td class="id-colon">:</td>
        <td class="id-value"><strong>{{ $siswa?->nama_lengkap ?? '-' }}</strong></td>
        <td class="id-label">Kelas</td>
        <td class="id-colon">:</td>
        <td class="id-value">
            {{ $pengajuan->rombel->tingkat ?? '' }} – {{ $pengajuan->rombel->nama }}
        </td>
    </tr>
    <tr>
        <td class="id-label">NISN</td>
        <td class="id-colon">:</td>
        <td class="id-value">{{ $siswa?->nisn ?? '-' }}</td>
        <td class="id-label">Wali Kelas</td>
        <td class="id-colon">:</td>
        <td class="id-value">{{ $pengajuan->rombel->wali_kelas ?? '-' }}</td>
    </tr>
</table>

{{-- ── SECTION 2: CAPAIAN HASIL BELAJAR ───────────────────────────────── --}}
<div class="section-heading">Capaian Hasil Belajar</div>

<table class="nilai-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th class="th-mapel">Mata Pelajaran</th>
            <th width="6%">KKM</th>
            <th width="9%">Nilai<br/>Harian</th>
            <th width="9%">Nilai<br/>UTS</th>
            <th width="9%">Nilai<br/>UAS</th>
            <th width="9%">Nilai<br/>Akhir</th>
            <th width="7%">Predi<br/>kat</th>
            <th width="7%">Ket</th>
        </tr>
    </thead>
    <tbody>
        @forelse($nilaiRows as $no => $nilai)
            @php
                // Use a sensible KKM fallback (model may not have a kkm field — use default 70)
                $kkm   = $nilai->kkm ?? 70;
                $na    = $nilai->nilai_akhir;
                $lulus = $na !== null && $na >= $kkm;
                $rowClass = $lulus ? (($no % 2 === 0) ? 'row-lulus' : 'row-alt') : 'row-tidak-lulus';
                $predikat = $nilai->predikat ?? null;
                $predikatClass = match($predikat) {
                    'A' => 'predikat-a',
                    'B' => 'predikat-b',
                    'C' => 'predikat-c',
                    'D' => 'predikat-d',
                    default => 'predikat-e',
                };
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="td-no">{{ $no + 1 }}</td>
                <td class="td-mapel">{{ $nilai->mataPelajaran?->nama ?? '-' }}</td>
                <td class="td-center">{{ $kkm }}</td>
                <td class="td-center">
                    {{ $nilai->nilai_harian !== null ? number_format($nilai->nilai_harian, 2) : '–' }}
                </td>
                <td class="td-center">
                    {{ $nilai->nilai_uts !== null ? number_format($nilai->nilai_uts, 2) : '–' }}
                </td>
                <td class="td-center">
                    {{ $nilai->nilai_uas !== null ? number_format($nilai->nilai_uas, 2) : '–' }}
                </td>
                <td class="td-na {{ $na !== null ? ($lulus ? 'predikat-a' : 'predikat-e') : '' }}">
                    {{ $na !== null ? number_format($na, 2) : '–' }}
                </td>
                <td class="td-center {{ $predikat ? $predikatClass : '' }}">
                    {{ $predikat ?? '–' }}
                </td>
                <td class="td-center" style="font-size:7.5pt;">
                    @if($na !== null)
                        {{ $lulus ? 'Tuntas' : 'Blm' }}
                    @else
                        –
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;padding:8px;color:#999;">
                    Belum ada data nilai.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ── SECTION 3: REKAP KEHADIRAN ──────────────────────────────────────── --}}
<div class="section-heading">Rekap Kehadiran</div>

<table class="absensi-table">
    <thead>
        <tr>
            <th>Hadir</th>
            <th>Sakit</th>
            <th>Izin</th>
            <th>Tanpa Keterangan</th>
            <th class="absensi-total">Total Absen</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $absensiRekap?->hadir ?? 0 }}</td>
            <td>{{ $absensiRekap?->sakit ?? 0 }}</td>
            <td>{{ $absensiRekap?->izin ?? 0 }}</td>
            <td>{{ $absensiRekap?->alpa ?? 0 }}</td>
            <td class="absensi-total">
                {{ ($absensiRekap?->sakit ?? 0) + ($absensiRekap?->izin ?? 0) + ($absensiRekap?->alpa ?? 0) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- ── SECTION 4: CATATAN WALI KELAS ──────────────────────────────────── --}}
<div class="section-heading">Catatan Wali Kelas</div>

<div class="catatan-box">
    @php
        $catatan = $nilaiRows->whereNotNull('catatan_guru')->pluck('catatan_guru')->filter()->first();
    @endphp
    {{ $catatan ?? '' }}
</div>

{{-- ── SECTION 5: PENGESAHAN ───────────────────────────────────────────── --}}
<div class="section-heading">Pengesahan</div>

<table class="pengesahan-table">
    <tr>
        {{-- Orang tua --}}
        <td style="width:33%;">
            <div>Mengetahui,</div>
            <div style="font-weight:600;">Orang Tua / Wali</div>
            <div class="sign-line"></div>
            <div class="sign-name">&nbsp;</div>
            <div class="sign-role">Orang Tua / Wali Murid</div>
        </td>

        {{-- Wali kelas --}}
        <td style="width:33%;">
            <div>{{ $lembaga?->alamat ? explode(',', $lembaga->alamat)[0] : 'Yogyakarta' }},
                {{ now()->translatedFormat('d F Y') }}</div>
            <div style="font-weight:600;">Wali Kelas</div>
            <div class="sign-line"></div>
            <div class="sign-name">{{ $pengajuan->rombel->wali_kelas ?? '—' }}</div>
            <div class="sign-role">Wali Kelas {{ $pengajuan->rombel->nama }}</div>
        </td>

        {{-- Kepala sekolah --}}
        <td style="width:33%;">
            <div>&nbsp;</div>
            <div style="font-weight:600;">Kepala Sekolah</div>
            <div class="sign-line"></div>
            <div class="sign-name">{{ $lembaga?->kepala_sekolah ?? $pengajuan->disetujuiOleh?->name ?? '—' }}</div>
            <div class="sign-role">Kepala {{ $lembaga?->nama ?? 'Sekolah' }}</div>
        </td>
    </tr>
</table>

{{-- ── PAGE FOOTER ──────────────────────────────────────────────────────── --}}
<div class="page-footer">
    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
    &nbsp;·&nbsp;
    Dokumen resmi {{ $lembaga?->nama ?? 'sekolah' }}
    &nbsp;·&nbsp;
    Semester {{ $pengajuan->semester->nama }} / T.P. {{ $pengajuan->tahunPelajaran->nama }}
</div>

</body>
</html>
