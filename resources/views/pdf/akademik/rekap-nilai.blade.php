<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Nilai</title>
<style>
  body { font-family: 'Arial', sans-serif; font-size: 8pt; color: #1a1a1a; margin: 0; }
  .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #1a56db; padding-bottom: 8px; }
  .header h2 { font-size: 13pt; margin: 0 0 2px; color: #1a56db; }
  .header p  { font-size: 8pt; margin: 0; color: #555; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #1a56db; color: #fff; padding: 5px 4px; font-size: 7.5pt; text-align: center; border: 1px solid #1a56db; }
  td { padding: 3px 4px; border: 1px solid #d1d5db; font-size: 7.5pt; }
  tr:nth-child(even) td { background: #f0f5ff; }
  .text-center { text-align: center; }
  .text-right  { text-align: right; }
  .lulus  { color: #166534; font-weight: bold; }
  .belum  { color: #991b1b; }
  .footer { margin-top: 12px; font-size: 7.5pt; color: #888; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h2>DAFTAR NILAI SISWA</h2>
  <p>{{ $rombel?->lembaga?->nama ?? 'Lembaga' }} &mdash; Kelas {{ $rombel?->tingkat }} {{ $rombel?->nama }}</p>
  <p>{{ $semester?->nama }} &mdash; Tahun Pelajaran {{ $tahun?->nama }}</p>
</div>

<table>
  <thead>
    <tr>
      <th rowspan="2">No</th>
      <th rowspan="2">Nama Siswa</th>
      @foreach($mapelList as $m)
      <th>{{ $m->nama }}</th>
      @endforeach
      <th rowspan="2">Rata-rata</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rekap as $s)
    <tr>
      <td class="text-center">{{ $s->no_absen }}</td>
      <td>{{ $s->nama }}</td>
      @foreach($mapelList as $m)
      @php $n = $s->nilai[$m->id] ?? null; @endphp
      <td class="text-center {{ ($n?->akhir ?? 0) >= 70 ? 'lulus' : 'belum' }}">
        {{ $n?->akhir !== null ? number_format($n->akhir, 0) : '-' }}
      </td>
      @endforeach
      <td class="text-center {{ ($s->rata_rata ?? 0) >= 70 ? 'lulus' : 'belum' }}">
        {{ $s->rata_rata !== null ? number_format($s->rata_rata, 1) : '-' }}
      </td>
    </tr>
    @empty
    <tr><td colspan="{{ 3 + count($mapelList) }}" class="text-center">Belum ada data nilai.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">Dicetak: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</div>
</body>
</html>
