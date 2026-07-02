@extends('layouts.portal')
@section('title', 'Detail Raport')

@section('content')
<div class="container py-4">

  {{-- Header --}}
  <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-journal-richtext me-2 text-primary"></i>Raport Nilai</h4>
      <div class="text-muted small">
        <span class="me-3"><i class="bi bi-people me-1"></i>Kelas {{ $pengajuan->rombel->tingkat }} – {{ $pengajuan->rombel->nama }}</span>
        <span class="me-3"><i class="bi bi-calendar3 me-1"></i>{{ $pengajuan->semester->nama }}</span>
        <span><i class="bi bi-mortarboard me-1"></i>TP. {{ $pengajuan->tahunPelajaran->nama }}</span>
      </div>
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
      <a href="{{ route('ppdb.raport.download', $pengajuan->id) }}" class="btn btn-success btn-sm">
        <i class="bi bi-download me-1"></i>Unduh PDF
      </a>
      <a href="{{ route('ppdb.raport.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
      </a>
    </div>
  </div>

  {{-- Info Siswa --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-sm-6">
          <div class="small text-muted">Nama Siswa</div>
          <div class="fw-semibold">{{ $peserta->nama_lengkap }}</div>
        </div>
        <div class="col-sm-6">
          <div class="small text-muted">Lembaga</div>
          <div class="fw-semibold">{{ $lembaga?->nama ?? '-' }}</div>
        </div>
        @if($pengajuan->disetujui_at)
        <div class="col-sm-6">
          <div class="small text-muted">Disetujui Pada</div>
          <div class="fw-semibold text-success">
            <i class="bi bi-check-circle-fill me-1"></i>
            {{ $pengajuan->disetujui_at->isoFormat('D MMMM YYYY') }}
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Tabel Nilai --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold py-3">
      <i class="bi bi-table me-2 text-primary"></i>Daftar Nilai Mata Pelajaran
    </div>
    <div class="card-body p-0">
      @if($nilaiRows->isEmpty())
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
          Nilai belum tersedia.
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Mata Pelajaran</th>
                <th class="text-center">Harian</th>
                <th class="text-center">UTS</th>
                <th class="text-center">UAS</th>
                <th class="text-center fw-bold">Nilai Akhir</th>
                <th class="text-center">Predikat</th>
              </tr>
            </thead>
            <tbody>
              @foreach($nilaiRows as $n)
              <tr>
                <td class="ps-4 fw-medium">{{ $n->mataPelajaran->nama ?? '-' }}</td>
                <td class="text-center text-muted">{{ $n->nilai_harian !== null ? number_format($n->nilai_harian, 0) : '—' }}</td>
                <td class="text-center text-muted">{{ $n->nilai_uts !== null ? number_format($n->nilai_uts, 0) : '—' }}</td>
                <td class="text-center text-muted">{{ $n->nilai_uas !== null ? number_format($n->nilai_uas, 0) : '—' }}</td>
                <td class="text-center fw-bold {{ ($n->nilai_akhir ?? 0) >= 70 ? 'text-success' : 'text-danger' }}">
                  {{ $n->nilai_akhir !== null ? number_format($n->nilai_akhir, 0) : '—' }}
                </td>
                <td class="text-center">
                  @php
                    $predikat = $n->predikat ?? '';
                    $badgeClass = match($predikat) {
                      'A' => 'bg-success',
                      'B' => 'bg-primary',
                      'C' => 'bg-warning text-dark',
                      'D','E' => 'bg-danger',
                      default => 'bg-secondary',
                    };
                  @endphp
                  <span class="badge {{ $badgeClass }}">{{ $predikat ?: '—' }}</span>
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td class="ps-4 fw-semibold text-muted" colspan="4">Rata-rata</td>
                <td class="text-center fw-bold text-primary">
                  {{ $nilaiRows->avg('nilai_akhir') !== null ? number_format($nilaiRows->avg('nilai_akhir'), 1) : '—' }}
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- Rekap Absensi --}}
  @if($absensiRekap)
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold py-3">
      <i class="bi bi-calendar-check me-2 text-primary"></i>Rekap Kehadiran
    </div>
    <div class="card-body">
      <div class="row g-3 text-center">
        <div class="col-6 col-sm-3">
          <div class="p-3 rounded-3 bg-success bg-opacity-10">
            <div class="fs-3 fw-bold text-success">{{ $absensiRekap->hadir ?? 0 }}</div>
            <div class="small text-muted">Hadir</div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="p-3 rounded-3 bg-warning bg-opacity-10">
            <div class="fs-3 fw-bold text-warning">{{ $absensiRekap->sakit ?? 0 }}</div>
            <div class="small text-muted">Sakit</div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="p-3 rounded-3 bg-info bg-opacity-10">
            <div class="fs-3 fw-bold text-info">{{ $absensiRekap->izin ?? 0 }}</div>
            <div class="small text-muted">Izin</div>
          </div>
        </div>
        <div class="col-6 col-sm-3">
          <div class="p-3 rounded-3 bg-danger bg-opacity-10">
            <div class="fs-3 fw-bold text-danger">{{ $absensiRekap->alpa ?? 0 }}</div>
            <div class="small text-muted">Alpa</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

</div>
@endsection
