<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Absensi</title>
<style>
  body { font-family: 'Arial', sans-serif; font-size: 9pt; color: #1a1a1a; margin: 0; }
  .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #1a56db; padding-bottom: 8px; }
  .header h2 { font-size: 13pt; margin: 0 0 2px; color: #1a56db; }
  .header p  { font-size: 8pt; margin: 0; color: #555; }
  .meta { display: flex; justify-content: space-between; font-size: 8pt; margin-bottom: 10px; color: #444; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #1a56db; color: #fff; padding: 5px 7px; font-size: 8pt; text-align: center; border: 1px solid #1a56db; }
  td { padding: 4px 7px; border: 1px solid #d1d5db; font-size: 8pt; }
  tr:nth-child(even) td { background: #f0f5ff; }
  .text-center { text-align: center; }
  .text-right  { text-align: right; }
  .badge-ok  { background: #dcfce7; color: #166534; padding: 1px 5px; border-radius: 3px; }
  .badge-bad { background: #fef2f2; color: #991b1b; padding: 1px 5px; border-radius: 3px; }
  .footer { margin-top: 12px; font-size: 7.5pt; color: #888; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h2>REKAP KEHADIRAN SISWA</h2>
  <p>{{ $rombel?->lembaga?->nama ?? 'Lembaga' }} &mdash; Kelas {{ $rombel?->tingkat }} {{ $rombel?->nama }}</p>
  <p>Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->isoFormat('D MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->isoFormat('D MMMM YYYY') }}</p>
</div>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Siswa</th>
      <th>Hadir</th>
      <th>Sakit</th>
      <th>Izin</th>
      <th>Alpa</th>
      <th>Total</th>
      <th>% Hadir</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rekap as $r)
    <tr>
      <td class="text-center">{{ $r->no_absen }}</td>
      <td>{{ $r->nama }}</td>
      <td class="text-center">{{ $r->hadir }}</td>
      <td class="text-center">{{ $r->sakit }}</td>
      <td class="text-center">{{ $r->izin }}</td>
      <td class="text-center">{{ $r->alpa }}</td>
      <td class="text-center">{{ $r->total }}</td>
      <td class="text-center">
        <span class="{{ $r->persen_hadir >= 75 ? 'badge-ok' : 'badge-bad' }}">{{ $r->persen_hadir }}%</span>
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center">Belum ada data absensi.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">Dicetak: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</div>
</body>
</html>
