@extends('admin.layouts.app')
@section('title', 'Rekap Nilai')

@section('content')
<div class="row">
  <div class="col-xl-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary"><i class="bi bi-table me-2"></i>Rekap Nilai Siswa</h5>
            <small class="text-muted">Daftar nilai seluruh mata pelajaran per kelas dan semester.</small>
          </div>
          <div class="flex-shrink-0">
            <a href="{{ route('admin.akademik.nilai.index') }}" class="btn btn-sm btn-light">
              <i class="bi bi-arrow-left me-1"></i>Input Nilai
            </a>
          </div>
        </div>
      </div>

      {{-- Filter --}}
      <div class="card-body border-bottom bg-light">
        <form method="GET" action="{{ route('admin.akademik.nilai.rekap') }}" class="row g-2 align-items-end">
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
            <label class="form-label form-label-sm fw-semibold">Semester</label>
            <select name="semester_id" class="form-select form-select-sm" required>
              <option value="">-- Pilih Semester --</option>
              @foreach($semesterList as $s)
              <option value="{{ $s->id }}" @selected($semesterId == $s->id)>{{ $s->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label form-label-sm fw-semibold">Tahun Pelajaran</label>
            <select name="tahun_pelajaran_id" class="form-select form-select-sm" required>
              <option value="">-- Pilih Tahun --</option>
              @foreach($tahunList as $t)
              <option value="{{ $t->id }}" @selected($tahunId == $t->id)>{{ $t->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill">
              <i class="bi bi-search me-1"></i>Tampilkan
            </button>
          </div>
        </form>
      </div>

      <div class="card-body">
        @if($rekap->isEmpty() && !$rombelId)
          <div class="text-center py-5 text-muted">
            <i class="bi bi-funnel fs-1 d-block mb-2 opacity-25"></i>
            Pilih kelas, semester, dan tahun pelajaran untuk menampilkan rekap nilai.
          </div>
        @elseif($rekap->isEmpty())
          <div class="alert alert-info">Belum ada data nilai pada filter yang dipilih.</div>
        @else
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h6 class="mb-0">Kelas {{ $rombel?->tingkat }} – {{ $rombel?->nama }}</h6>
              <small class="text-muted">
                {{ $semester?->nama }} &bull; {{ $tahun?->nama }}
                &bull; {{ $rekap->count() }} siswa &bull; {{ $mapelList->count() }} mapel
              </small>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('admin.akademik.nilai.rekap-pdf', request()->query()) }}"
                 class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
              </a>
              <a href="{{ route('admin.akademik.nilai.rekap-excel', request()->query()) }}"
                 class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
              </a>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle" style="font-size:13px;">
              <thead class="table-primary">
                <tr>
                  <th class="text-center" style="width:40px">No</th>
                  <th style="min-width:180px">Nama Siswa</th>
                  @foreach($mapelList as $m)
                  <th class="text-center" style="min-width:80px">{{ $m->nama }}</th>
                  @endforeach
                  <th class="text-center" style="min-width:80px">Rata-rata</th>
                </tr>
              </thead>
              <tbody>
                @foreach($rekap as $s)
                <tr>
                  <td class="text-center text-muted">{{ $s->no_absen }}</td>
                  <td class="fw-medium">{{ $s->nama }}</td>
                  @foreach($mapelList as $m)
                  @php $n = $s->nilai[$m->id] ?? null; $akhir = $n?->akhir; @endphp
                  <td class="text-center {{ $akhir !== null && $akhir < 70 ? 'text-danger' : '' }}">
                    @if($akhir !== null)
                      {{ number_format($akhir, 0) }}
                      <small class="text-muted d-block" style="font-size:10px">{{ $n->predikat }}</small>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  @endforeach
                  <td class="text-center fw-semibold {{ ($s->rata_rata ?? 0) < 70 ? 'text-danger' : 'text-success' }}">
                    {{ $s->rata_rata !== null ? number_format($s->rata_rata, 1) : '—' }}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Nilai yang ditampilkan adalah <strong>nilai akhir</strong> (nilai harian × bobot + UTS × bobot + UAS × bobot).
            Merah = di bawah KKM 70.
          </small>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
