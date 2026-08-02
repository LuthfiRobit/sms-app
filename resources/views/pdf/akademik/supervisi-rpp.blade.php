<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Supervisi RPP - {{ $supervisi->rpp?->materi }}</title>
<style>
  {{-- DejaVu Sans dipakai eksplisit karena Arial/Helvetica base14 tidak punya
       glyph untuk simbol centang "√" — tanpa ini akan muncul sebagai "?" di PDF. --}}
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9.5pt; color: #1a1a1a; }
  h1 { text-align: center; font-size: 12.5pt; margin: 0 0 4px; text-transform: uppercase; font-weight: bold; }
  h2 { text-align: center; font-size: 11pt; margin: 0 0 14px; font-weight: bold; }
  .instrumen-page { page-break-before: always; }
  .instrumen-page:first-child { page-break-before: avoid; }

  table.identitas { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
  table.identitas td { padding: 2px 4px; vertical-align: top; }
  table.identitas td.label { width: 170px; }
  table.identitas td.sep { width: 12px; }

  table.rubrik { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8.5pt; }
  table.rubrik th, table.rubrik td { border: 1px solid #999; padding: 4px 5px; vertical-align: middle; }
  table.rubrik thead th { background: #f0f5ff; text-align: center; font-weight: bold; }
  table.rubrik td.no { width: 24px; text-align: center; color: #6b7280; }
  table.rubrik td.komponen { text-align: left; }
  table.rubrik td.skor-col { width: 22px; text-align: center; font-weight: bold; }
  table.rubrik td.catatan-col { width: 110px; font-size: 8pt; }
  table.rubrik tr.tahap-row td { background: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 8pt; }

  table.hasil { width: 60%; border-collapse: collapse; margin: 8px 0 14px; font-size: 9pt; }
  table.hasil td { border: 1px solid #999; padding: 4px 8px; }
  table.hasil td.label { font-weight: bold; width: 55%; }

  .keterangan-box { border: 1px solid #999; padding: 8px 10px; font-size: 8pt; margin-bottom: 14px; }
  .keterangan-box .judul { font-weight: bold; margin-bottom: 4px; }

  .catatan-block { margin-bottom: 6px; }
  .catatan-block .label { font-weight: bold; font-size: 9pt; }
  .catatan-block .isi { border-bottom: 1px solid #999; min-height: 16px; padding: 2px 0; font-size: 9pt; }

  table.ttd { width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 9pt; }
  table.ttd td { width: 50%; vertical-align: top; padding: 0 10px; }
  table.ttd .spasi { height: 50px; }
</style>
</head>
<body>

@foreach($hasil as $key => $h)
<div class="instrumen-page">
    <h1>Instrumen Supervisi</h1>
    <h2>{{ $h['label'] }}</h2>

    <table class="identitas">
        <tr><td class="label">Nama Madrasah</td><td class="sep">:</td><td>{{ $profilSekolah?->nama_sekolah ?? $supervisi->rpp?->lembaga?->nama }}</td></tr>
        <tr><td class="label">Nama Guru</td><td class="sep">:</td><td>{{ $supervisi->rpp?->guru?->nama_lengkap }}</td></tr>
        <tr><td class="label">Mata Pelajaran</td><td class="sep">:</td><td>{{ $supervisi->rpp?->mataPelajaran?->nama }}</td></tr>
        <tr><td class="label">Kelas / Semester</td><td class="sep">:</td><td>{{ $supervisi->rpp?->fase_kelas }} / {{ $supervisi->rpp?->semester?->nama }}</td></tr>
        <tr><td class="label">Jumlah Jam Tatap Muka</td><td class="sep">:</td><td>{{ $supervisi->rpp?->alokasi_waktu }}</td></tr>
        <tr><td class="label">Tanggal Supervisi</td><td class="sep">:</td><td>{{ $supervisi->tanggal_supervisi?->translatedFormat('d F Y') }}</td></tr>
    </table>

    <table class="rubrik">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Komponen</th>
                <th colspan="4">Skor</th>
                <th rowspan="2">Catatan</th>
            </tr>
            <tr>
                <th>0</th><th>1</th><th>2</th><th>3</th>
            </tr>
        </thead>
        <tbody>
            @php $tahapSaatIni = null; @endphp
            @foreach($h['kriteria'] as $i => $row)
                @if($row['tahap'] && $row['tahap'] !== $tahapSaatIni)
                    @php $tahapSaatIni = $row['tahap']; @endphp
                    <tr class="tahap-row"><td colspan="7">Tahap {{ $tahapSaatIni }}</td></tr>
                @endif
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td class="komponen">{{ $row['teks'] }}</td>
                    @foreach([0, 1, 2, 3] as $nilai)
                        <td class="skor-col">{{ $row['skor'] === $nilai ? '√' : '' }}</td>
                    @endforeach
                    <td class="catatan-col">{{ $row['catatan'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="hasil">
        <tr><td class="label">Skor Total</td><td>{{ $h['skor_total'] }} / {{ $h['skor_maksimal'] }}</td></tr>
        <tr><td class="label">% Capaian</td><td>{{ $h['persen_capaian'] }}%</td></tr>
        <tr><td class="label">Predikat</td><td>{{ $h['predikat'] }}</td></tr>
    </table>

    <div class="keterangan-box">
        <div class="judul">Keterangan Ketercapaian:</div>
        % Capaian = (Skor Perolehan &divide; Skor Maksimal) &times; 100% &mdash;
        @if($key === 'asesmen')
            91% - 100% = Sangat Baik &nbsp; | &nbsp; 80% - 90% = Baik &nbsp; | &nbsp; 71% - 79% = Cukup &nbsp; | &nbsp; &lt; 71% = Kurang
        @else
            91% - 100% = Sangat Baik &nbsp; | &nbsp; 81% - 90% = Baik &nbsp; | &nbsp; 71% - 80% = Cukup &nbsp; | &nbsp; &lt; 71% = Kurang
        @endif
    </div>

    <div class="catatan-block">
        <div class="label">Catatan Khusus Hasil Supervisi:</div>
        <div class="isi">{{ $supervisi->{"catatan_{$key}"} }}</div>
    </div>
    <div class="catatan-block">
        <div class="label">Rencana Tindak Lanjut:</div>
        <div class="isi">{{ $supervisi->{"rtl_{$key}"} }}</div>
    </div>

    <table class="ttd">
        <tr>
            <td>Guru yang di Supervisi,</td>
            <td>{{ $supervisi->jabatan_supervisor ?? 'Kepala Madrasah' }} / Supervisor,</td>
        </tr>
        <tr><td class="spasi"></td><td class="spasi"></td></tr>
        <tr>
            <td><strong>{{ $supervisi->rpp?->guru?->nama_lengkap }}</strong><br>NIP. {{ $supervisi->rpp?->guru?->nip ?: '-' }}</td>
            <td><strong>{{ $supervisi->nama_supervisor ?? $profilSekolah?->kepala_sekolah }}</strong><br>NIP. {{ $supervisi->nip_supervisor ?? $profilSekolah?->nip_kepsek ?: '-' }}</td>
        </tr>
    </table>
</div>
@endforeach

</body>
</html>
