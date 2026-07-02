@extends('admin.layouts.app')
@section('title', 'Rekap Absensi')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary"><i class="bi bi-clipboard2-data me-2"></i>Rekap Absensi Siswa</h5>
            <small class="text-muted">Rekap kehadiran siswa per kelas dalam rentang tanggal tertentu.</small>
          </div>
          <div class="flex-shrink-0">
            <a href="{{ route('admin.akademik.absensi.index') }}" class="btn btn-sm btn-light">
              <i class="bi bi-arrow-left me-1"></i>Input Absensi
            </a>
          </div>
        </div>
      </div>

      {{-- Filter Form --}}
      <div class="card-body border-bottom bg-light">
        <form method="GET" action="{{ route('admin.akademik.absensi.rekap') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label form-label-sm fw-semibold">Kelas / Rombel</label>
            <select name="rombel_id" class="form-select form-select-sm" required>
              <option value="">-- Pilih Rombel --</option>
              @foreach($rombelList as $r)
              <option value="{{ $r->id }}" @selected($rombelId == $r->id)>
                Kelas {{ $r->tingkat }} – {{ $r->nama }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label form-label-sm fw-semibold">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
              value="{{ $tanggalMulai ?? '' }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label form-label-sm fw-semibold">Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
              value="{{ $tanggalAkhir ?? '' }}" required>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill">
              <i class="bi bi-search me-1"></i>Tampilkan
            </button>
          </div>
        </form>
      </div>

      {{-- Hasil Rekap --}}
      <div class="card-body">
        @if($rekap->isEmpty() && !$rombelId)
          <div class="text-center py-5 text-muted">
            <i class="bi bi-funnel fs-1 d-block mb-2 opacity-25"></i>
            Pilih kelas dan rentang tanggal untuk menampilkan rekap absensi.
          </div>
        @elseif($rekap->isEmpty())
          <div class="alert alert-info">Belum ada data absensi pada periode yang dipilih.</div>
        @else
          {{-- Info kelas + tombol export --}}
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h6 class="mb-0">Kelas {{ $rombel?->tingkat }} – {{ $rombel?->nama }}</h6>
              <small class="text-muted">
                {{ \Carbon\Carbon::parse($tanggalMulai)->isoFormat('D MMM YYYY') }} s/d
                {{ \Carbon\Carbon::parse($tanggalAkhir)->isoFormat('D MMM YYYY') }}
                &bull; {{ $rekap->count() }} siswa
              </small>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('admin.akademik.absensi.rekap-pdf', request()->query()) }}"
                 class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
              </a>
              <a href="{{ route('admin.akademik.absensi.rekap-excel', request()->query()) }}"
                 class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
              </a>
            </div>
          </div>

          {{-- Tabel rekap --}}
          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle">
              <thead class="table-primary">
                <tr>
                  <th class="text-center" style="width:48px">No</th>
                  <th>Nama Siswa</th>
                  <th class="text-center text-success">Hadir</th>
                  <th class="text-center text-warning">Sakit</th>
                  <th class="text-center text-info">Izin</th>
                  <th class="text-center text-danger">Alpa</th>
                  <th class="text-center">Total</th>
                  <th class="text-center" style="width:100px">% Hadir</th>
                </tr>
              </thead>
              <tbody>
                @foreach($rekap as $r)
                <tr>
                  <td class="text-center text-muted">{{ $r->no_absen }}</td>
                  <td class="fw-medium">{{ $r->nama }}</td>
                  <td class="text-center fw-semibold text-success">{{ $r->hadir }}</td>
                  <td class="text-center text-warning">{{ $r->sakit }}</td>
                  <td class="text-center text-info">{{ $r->izin }}</td>
                  <td class="text-center text-danger">{{ $r->alpa }}</td>
                  <td class="text-center text-muted">{{ $r->total }}</td>
                  <td class="text-center">
                    @php $pct = $r->persen_hadir; @endphp
                    <span class="badge {{ $pct >= 90 ? 'bg-success' : ($pct >= 75 ? 'bg-warning text-dark' : 'bg-danger') }}">
                      {{ $pct }}%
                    </span>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-light fw-semibold">
                <tr>
                  <td colspan="2" class="text-end text-muted">Total Kelas</td>
                  <td class="text-center text-success">{{ $rekap->sum('hadir') }}</td>
                  <td class="text-center text-warning">{{ $rekap->sum('sakit') }}</td>
                  <td class="text-center text-info">{{ $rekap->sum('izin') }}</td>
                  <td class="text-center text-danger">{{ $rekap->sum('alpa') }}</td>
                  <td class="text-center">{{ $rekap->sum('total') }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
