@extends('layouts.portal')
@section('title', 'Raport Online')

@section('content')
<div class="container py-4">

  {{-- Header --}}
  <div class="mb-4">
    <h4 class="fw-bold mb-1"><i class="bi bi-journal-richtext me-2 text-primary"></i>Raport Online</h4>
    <p class="text-muted mb-0">Lihat dan unduh raport hasil belajar Anda yang telah disetujui oleh wali kelas.</p>
  </div>

  @if(!$peserta)
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle me-2"></i>
      Data peserta tidak ditemukan. Pastikan profil Anda sudah dilengkapi.
    </div>
  @elseif($raportList->isEmpty())
    <div class="profil-card text-center py-5">
      <i class="bi bi-journal-x fs-1 text-muted d-block mb-3 opacity-50"></i>
      <h5 class="fw-semibold text-dark">Belum Ada Raport</h5>
      <p class="text-muted">Raport Anda belum tersedia. Raport akan muncul di sini setelah disetujui oleh sekolah.</p>
    </div>
  @else
    <div class="row g-3">
      @foreach($raportList as $raport)
      <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex align-items-start gap-3">
              <div class="flex-shrink-0 rounded-3 bg-primary bg-opacity-10 p-3 text-primary">
                <i class="bi bi-journal-richtext fs-4"></i>
              </div>
              <div class="flex-grow-1 min-w-0">
                <h6 class="fw-bold mb-1 text-truncate">{{ $raport->rombel->nama }}</h6>
                <div class="small text-muted mb-1">Kelas {{ $raport->rombel->tingkat }}</div>
                <div class="small text-muted">{{ $raport->semester->nama }}</div>
                <div class="small text-muted">TP. {{ $raport->tahunPelajaran->nama }}</div>
                @if($raport->lembaga)
                  <div class="small text-muted mt-1">
                    <i class="bi bi-building me-1"></i>{{ $raport->lembaga->nama }}
                  </div>
                @endif
                @if($raport->disetujui_at)
                  <div class="small text-success mt-1">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Disetujui {{ $raport->disetujui_at->isoFormat('D MMM YYYY') }}
                  </div>
                @endif
              </div>
            </div>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <div class="d-flex gap-2">
              <a href="{{ route('ppdb.raport.show', $raport->id) }}"
                 class="btn btn-sm btn-outline-primary flex-fill">
                <i class="bi bi-eye me-1"></i>Lihat
              </a>
              <a href="{{ route('ppdb.raport.download', $raport->id) }}"
                 class="btn btn-sm btn-success flex-fill">
                <i class="bi bi-download me-1"></i>Unduh PDF
              </a>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  @endif

</div>
@endsection
