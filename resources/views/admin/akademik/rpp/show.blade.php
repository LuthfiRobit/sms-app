@extends('admin.layouts.app')
@section('title', 'Detail RPP')

@push('styles')
<style>
/* Tabel yang disisipkan lewat WYSIWYG (mis. rubrik penilaian) tidak bawa
   border sendiri — CKEditor bungkus <table> di dalam <figure class="table">. */
.rpp-rich-content table { width: 100%; border-collapse: collapse; margin: 8px 0; }
.rpp-rich-content table td, .rpp-rich-content table th { border: 1px solid #dee2e6; padding: 6px 8px; }
.rpp-rich-content table th { background: #f8f9fa; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-journal-text me-2"></i>{{ $rpp->materi }}</h5>
                    <small class="text-muted">{{ $rpp->guru?->nama_lengkap }} — {{ $rpp->mataPelajaran?->nama }} — Fase/Kelas {{ $rpp->fase_kelas }}</small>
                </div>
                <div>
                    @php $badge = \App\Models\Akademik\Rpp::statusBadge($rpp->status); @endphp
                    <span class="badge bg-{{ $badge['class'] }} me-2">{{ $badge['label'] }}</span>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.rpp.update'))
                        <a href="{{ route('admin.akademik.rpp.edit', $rpp->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                    @endif
                    <a href="{{ route('admin.akademik.rpp.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                </div>
            </div>
            <div class="card-body">
                @if($rpp->status === 'ditolak' && $rpp->catatan_revisi)
                    <div class="alert alert-danger">
                        <strong>Catatan revisi:</strong> {{ $rpp->catatan_revisi }}
                    </div>
                @endif

                <dl class="row mb-4">
                    <dt class="col-md-3">Tahun Pelajaran / Semester</dt>
                    <dd class="col-md-9">{{ $rpp->tahunPelajaran?->nama }} / {{ $rpp->semester?->nama }}</dd>
                    <dt class="col-md-3">Alokasi Waktu</dt>
                    <dd class="col-md-9">{{ $rpp->alokasi_waktu }}</dd>
                    <dt class="col-md-3">Model Pembelajaran</dt>
                    <dd class="col-md-9">{{ $rpp->modelPembelajaran?->nama }}</dd>
                    @if($rpp->file_path)
                    <dt class="col-md-3">Dokumen PDF</dt>
                    <dd class="col-md-9"><a href="{{ asset('storage/'.$rpp->file_path) }}" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Unduh PDF</a></dd>
                    @endif
                    @if($rpp->submateri->isNotEmpty())
                    <dt class="col-md-3">Submateri</dt>
                    <dd class="col-md-9">
                        <ol class="mb-0">
                            @foreach($rpp->submateri as $sub)
                                <li>{{ $sub->teks }}</li>
                            @endforeach
                        </ol>
                    </dd>
                    @endif
                </dl>

                @foreach($bagianList as $bagian)
                <div class="mb-4">
                    <h6 class="text-uppercase text-primary border-bottom pb-2">{{ $bagian->nama }}</h6>
                    @forelse($bagian->poin as $poin)
                        @include('admin.akademik.rpp._poin-display', ['poin' => $poin, 'rpp' => $rpp])
                    @empty
                        <div class="text-muted small">Belum ada poin di bagian ini.</div>
                    @endforelse
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
