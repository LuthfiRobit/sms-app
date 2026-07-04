<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Absensi Guru</title>
<style>
  body { font-family: 'Arial', sans-serif; font-size: 9pt; color: #1a1a1a; margin: 0; }
  .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #1a56db; padding-bottom: 8px; }
  .header h2 { font-size: 13pt; margin: 0 0 2px; color: #1a56db; }
  .header p  { font-size: 8pt; margin: 0; color: #555; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th { background: #1a56db; color: #fff; padding: 5px 7px; font-size: 8pt; text-align: center; border: 1px solid #1a56db; }
  td { padding: 4px 7px; border: 1px solid #d1d5db; font-size: 8pt; }
  tr:nth-child(even) td { background: #f0f5ff; }
  .text-center { text-align: center; }
  .badge-ok   { background: #dcfce7; color: #166534; padding: 1px 5px; border-radius: 3px; }
  .badge-warn { background: #fef9c3; color: #854d0e; padding: 1px 5px; border-radius: 3px; }
  .badge-info { background: #dbeafe; color: #1e40af; padding: 1px 5px; border-radius: 3px; }
  .badge-bad  { background: #fef2f2; color: #991b1b; padding: 1px 5px; border-radius: 3px; }
  .footer { margin-top: 12px; font-size: 7.5pt; color: #888; text-align: right; }

  /* ── PENGESAHAN ──────────────────────────────────────── */
  .pengesahan-table { width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 8.5pt; }
  .pengesahan-table td { vertical-align: top; padding: 4px 8px; text-align: center; }
  .sign-line { border-bottom: 1px solid #1c1c1c; margin: 40px auto 4px auto; width: 40%; }
  .sign-name { font-weight: 700; font-size: 8.5pt; text-decoration: underline; }
  .sign-role { font-size: 7.5pt; color: #555555; }
</style>
</head>
<body>
<div class="header">
  <h2>REKAP ABSENSI GURU</h2>
  <p>{{ $lembaga?->nama ?? 'Semua Lembaga' }}</p>
  @if(!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_akhir']))
  <p>Periode: {{ $filters['tanggal_mulai'] ? \Carbon\Carbon::parse($filters['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '—' }} s/d {{ $filters['tanggal_akhir'] ? \Carbon\Carbon::parse($filters['tanggal_akhir'])->isoFormat('D MMMM YYYY') : '—' }}</p>
  @endif
</div>

<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Guru</th>
      <th>Lembaga</th>
      <th>Tanggal</th>
      <th>Jam Masuk</th>
      <th>Jam Pulang</th>
      <th>Status</th>
      <th>Jarak Masuk (m)</th>
      <th>Jarak Pulang (m)</th>
      <th>Lokasi Palsu</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $i => $r)
    @php
      $badgeClass = match ($r->status) {
          'hadir' => 'badge-ok',
          'terlambat' => 'badge-warn',
          'izin', 'sakit' => 'badge-info',
          'alpa' => 'badge-bad',
          default => '',
      };
    @endphp
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td>{{ $r->guru?->nama_lengkap ?? '—' }}</td>
      <td>{{ $r->lembaga?->nama ?? '—' }}</td>
      <td class="text-center">{{ $r->tanggal?->format('d/m/Y') }}</td>
      <td class="text-center">{{ $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '—' }}</td>
      <td class="text-center">{{ $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '—' }}</td>
      <td class="text-center"><span class="{{ $badgeClass }}">{{ ucfirst($r->status) }}</span></td>
      <td class="text-center">{{ $r->jarak_masuk_m ?? '—' }}</td>
      <td class="text-center">{{ $r->jarak_pulang_m ?? '—' }}</td>
      <td class="text-center">{{ $r->flag_mock_location ? 'Ya' : 'Tidak' }}</td>
    </tr>
    @empty
    <tr><td colspan="10" class="text-center">Belum ada data absensi.</td></tr>
    @endforelse
  </tbody>
</table>

@if($lembaga)
<table class="pengesahan-table">
  <tr>
    <td style="width:100%;">
      <div>{{ $lembaga->alamat ? explode(',', $lembaga->alamat)[0] : '' }}, {{ now()->translatedFormat('d F Y') }}</div>
      <div style="font-weight:600;">Kepala Sekolah</div>
      <div class="sign-line"></div>
      <div class="sign-name">{{ $lembaga->kepala_sekolah ?? '—' }}</div>
      <div class="sign-role">Kepala {{ $lembaga->nama }}</div>
    </td>
  </tr>
</table>
@endif

<div class="footer">Dicetak: {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</div>
</body>
</html>
