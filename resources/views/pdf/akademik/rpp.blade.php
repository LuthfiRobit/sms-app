<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>RPP - {{ $rpp->materi }}</title>
<style>
  {{-- DejaVu Sans (bawaan DomPDF) dipakai eksplisit karena Arial/Helvetica
       base14 tidak punya glyph untuk simbol centang "√" di bawah — tanpa ini
       akan muncul sebagai "?" di PDF. --}}
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1a1a1a; }
  h1 { text-align: center; font-size: 13pt; margin: 0 0 12px; text-transform: uppercase; font-weight: bold; }
  table.identitas { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 9.5pt; }
  table.identitas td { padding: 2px 4px; vertical-align: top; }
  table.identitas td.label { width: 150px; }
  table.identitas td.sep { width: 12px; }
  /* Judul bagian — polos & bergaris bawah, meniru "IDENTIFIKASI"/"DESAIN
     PEMBELAJARAN" pada dokumen RPP KBC asli (bukan blok warna). */
  h2.bagian { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin: 16px 0 6px; padding-bottom: 3px; border-bottom: 1.5px solid #1a1a1a; }
  /* Tabel 2 kolom (label | isi) per poin — struktur ini yang meniru layout
     asli dokumen RPP KBC, bukan heading lepas + isi di bawahnya. */
  table.poin-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  table.poin-table > tbody > tr > td { border: 1px solid #bfbfbf; padding: 6px 8px; vertical-align: top; font-size: 9.5pt; }
  table.poin-table > tbody > tr > td.label-col { width: 26%; font-weight: bold; }
  .fase-judul { font-size: 9.5pt; text-transform: uppercase; font-weight: bold; margin: 6px 0 3px; }
  .fase-judul:first-child { margin-top: 0; }
  .sintaks-nama { font-style: italic; margin: 4px 0 2px; }
  p.isi { margin: 2px 0 6px; text-align: justify; }
  p.isi:last-child { margin-bottom: 0; }
  ul.isi { margin: 2px 0 6px; padding-left: 18px; }
  ul.isi:last-child { margin-bottom: 0; }
  ul.isi li { margin-bottom: 2px; }
  table.pasangan, table.sub-checklist { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  table.pasangan th, table.pasangan td, table.sub-checklist td { border: 1px solid #d1d5db; padding: 4px 6px; font-size: 9pt; text-align: left; }
  table.pasangan th { background: #f0f5ff; }
  table.sub-checklist td.no { width: 24px; text-align: center; color: #6b7280; }
  table.sub-checklist td.centang { width: 34px; text-align: center; font-weight: bold; }
  .kosong { color: #888; font-style: italic; }
  /* Isi teks_panjang dari WYSIWYG (CKEditor) — reset margin bawaan <p>/<ul> browser. */
  .rich-content { margin: 0; text-align: justify; }
  .rich-content p { margin: 0 0 6px; }
  .rich-content p:last-child { margin-bottom: 0; }
  .rich-content ul, .rich-content ol { margin: 0 0 6px; padding-left: 18px; }
  /* Tabel yang disisipkan lewat tombol "Sisipkan Tabel" di WYSIWYG (mis. rubrik penilaian). */
  .rich-content table { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 9pt; }
  .rich-content table td, .rich-content table th { border: 1px solid #999; padding: 4px 6px; text-align: left; }
  .rich-content table th { background: #f0f5ff; }
</style>
</head>
<body>

<h1>Perencanaan Pembelajaran Mendalam</h1>

<table class="identitas">
    <tr><td class="label">Madrasah</td><td class="sep">:</td><td>{{ $rpp->lembaga?->nama }}</td></tr>
    <tr><td class="label">Nama Guru</td><td class="sep">:</td><td>{{ $rpp->guru?->nama_lengkap }}</td></tr>
    <tr><td class="label">Mata Pelajaran</td><td class="sep">:</td><td>{{ $rpp->mataPelajaran?->nama }}</td></tr>
    <tr><td class="label">Fase / Kelas</td><td class="sep">:</td><td>{{ $rpp->fase_kelas }}</td></tr>
    <tr><td class="label">Materi</td><td class="sep">:</td><td>{{ $rpp->materi }}</td></tr>
    @if($rpp->submateri->isNotEmpty())
    <tr><td class="label">Submateri</td><td class="sep">:</td><td>
        <ol style="margin:0; padding-left:16px;">
            @foreach($rpp->submateri as $sub)
                <li>{{ $sub->teks }}</li>
            @endforeach
        </ol>
    </td></tr>
    @endif
    <tr><td class="label">Alokasi Waktu</td><td class="sep">:</td><td>{{ $rpp->alokasi_waktu }}</td></tr>
    <tr><td class="label">Model Pembelajaran</td><td class="sep">:</td><td>{{ $rpp->modelPembelajaran?->nama }}</td></tr>
    <tr><td class="label">Tahun Pelajaran / Semester</td><td class="sep">:</td><td>{{ $rpp->tahunPelajaran?->nama }} / {{ $rpp->semester?->nama }}</td></tr>
</table>

@foreach($bagianList as $bagian)
<h2 class="bagian">{{ $bagian->nama }}</h2>

@if($bagian->poin->isEmpty())
    <p class="isi kosong">Belum ada poin di bagian ini.</p>
@else
<table class="poin-table">
<tbody>
@foreach($bagian->poin as $poin)
    @php $nilai = $rpp->nilaiUntuk($poin); @endphp
    <tr>
    <td class="label-col">{{ $poin->label }}</td>
    <td>
    @if($poin->tipe === 'model_pembelajaran')
        @php $intiByFase = $rpp->inti->groupBy(fn ($i) => $i->sintaks?->meta_fase); @endphp
        @foreach(['Memahami', 'Mengaplikasi', 'Merefleksi'] as $fase)
            @if(($intiByFase[$fase] ?? collect())->isNotEmpty())
                <div class="fase-judul">{{ $fase }}</div>
                @foreach($intiByFase[$fase]->sortBy('urutan') as $item)
                    <div class="sintaks-nama">{{ $item->sintaks?->nama_sintaks }}</div>
                    @if(!empty($item->konten))
                        <ul class="isi">
                            @foreach($item->konten as $baris)
                                <li>{{ $baris }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="isi kosong">—</p>
                    @endif
                @endforeach
            @endif
        @endforeach
    @elseif($poin->tipe === 'pasangan_kolom')
        @php $rows = $nilai->value_json ?? []; @endphp
        @if(count($rows))
            <table class="pasangan">
                <thead><tr><th>{{ $poin->kolom1_label }}</th><th>{{ $poin->kolom2_label }}</th></tr></thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr><td>{{ $row['kolom1'] ?? '' }}</td><td>{{ $row['kolom2'] ?? '' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="isi kosong">—</p>
        @endif
    @elseif($poin->tipe === 'pilih_master')
        @php
            $selected = $nilai->value_json ?? [];
            $semuaOpsi = $poin->opsiMaster();
        @endphp
        @if($semuaOpsi->isNotEmpty())
            {{-- Tampilkan seluruh daftar opsi + tanda centang untuk yang dipilih
                 — meniru sub-tabel Dimensi Profil Lulusan/Topik Panca Cinta pada
                 dokumen RPP KBC asli, bukan sekadar daftar yang dipilih saja. --}}
            <table class="sub-checklist">
                <tbody>
                    @foreach($semuaOpsi as $i => $opsi)
                        <tr>
                            <td class="no">{{ $i + 1 }}</td>
                            <td>{{ $opsi->nama }}</td>
                            <td class="centang">{{ in_array($opsi->id, $selected) ? '√' : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="isi kosong">—</p>
        @endif
    @elseif($poin->tipe === 'daftar_poin')
        @if(!empty($nilai->value_json))
            <ul class="isi">
                @foreach($nilai->value_json as $baris)
                    <li>{{ $baris }}</li>
                @endforeach
            </ul>
        @else
            <p class="isi kosong">—</p>
        @endif
    @else
        {{-- teks_panjang disimpan sebagai HTML dari WYSIWYG — render apa adanya. --}}
        @if(! empty($nilai->value_teks))
            <div class="rich-content">{!! $nilai->value_teks !!}</div>
        @else
            <p class="isi kosong">—</p>
        @endif
    @endif
    </td>
    </tr>
@endforeach
</tbody>
</table>
@endif
@endforeach

</body>
</html>
