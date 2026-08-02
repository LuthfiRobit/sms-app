@extends('admin.layouts.app')
@section('title', 'Detail Supervisi RPP')

@section('content')
@php
    $predikatClass = fn ($label) => match ($label) {
        'Sangat Baik' => 'success',
        'Baik' => 'primary',
        'Cukup' => 'warning text-dark',
        default => 'danger',
    };
@endphp

<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-clipboard-check me-2"></i>Supervisi RPP — {{ $supervisi->rpp?->materi }}</h5>
                    <small class="text-muted">{{ $supervisi->rpp?->guru?->nama_lengkap }} — {{ $supervisi->rpp?->mataPelajaran?->nama }} — {{ $supervisi->tanggal_supervisi?->translatedFormat('d F Y') }}</small>
                </div>
                <div>
                    @if($supervisi->file_path)
                        <a href="{{ asset('storage/'.$supervisi->file_path) }}" target="_blank" class="btn btn-sm btn-light-info"><i class="bi bi-file-earmark-pdf me-1"></i>Unduh PDF</a>
                    @endif
                    @if(auth()->user()->hasPermissionTo('admin.akademik.supervisi-rpp.update'))
                        <a href="{{ route('admin.akademik.supervisi-rpp.edit', $supervisi->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                    @endif
                    <a href="{{ route('admin.akademik.supervisi-rpp.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-4">
                    <dt class="col-md-3">Supervisor</dt>
                    <dd class="col-md-9">{{ $supervisi->nama_supervisor ?? $profilSekolah?->kepala_sekolah }} ({{ $supervisi->jabatan_supervisor ?? 'Kepala Madrasah' }})</dd>
                    <dt class="col-md-3">Fase / Kelas</dt>
                    <dd class="col-md-9">{{ $supervisi->rpp?->fase_kelas }}</dd>
                </dl>

                @foreach($hasil as $key => $h)
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="text-uppercase text-primary mb-0">{{ $h['label'] }}</h6>
                        <div>
                            <span class="badge bg-secondary me-1">Skor {{ $h['skor_total'] }}/{{ $h['skor_maksimal'] }}</span>
                            <span class="badge bg-secondary me-1">{{ $h['persen_capaian'] }}%</span>
                            <span class="badge bg-{{ $predikatClass($h['predikat']) }}">{{ $h['predikat'] }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr><th width="40">No</th><th>Komponen</th><th width="80" class="text-center">Skor</th><th>Catatan</th></tr>
                            </thead>
                            <tbody>
                                @php $tahapSaatIni = null; @endphp
                                @foreach($h['kriteria'] as $i => $row)
                                    @if($row['tahap'] && $row['tahap'] !== $tahapSaatIni)
                                        @php $tahapSaatIni = $row['tahap']; @endphp
                                        <tr class="table-light"><td colspan="4" class="fw-bold small text-uppercase">Tahap {{ $tahapSaatIni }}</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td>{{ $row['teks'] }}</td>
                                        <td class="text-center fw-bold">{{ $row['skor'] ?? '—' }}</td>
                                        <td class="text-muted small">{{ $row['catatan'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($supervisi->{"catatan_{$key}"} || $supervisi->{"rtl_{$key}"})
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <strong class="d-block small text-uppercase text-muted">Catatan Khusus</strong>
                            <p class="mb-0">{{ $supervisi->{"catatan_{$key}"} ?: '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="d-block small text-uppercase text-muted">Rencana Tindak Lanjut</strong>
                            <p class="mb-0">{{ $supervisi->{"rtl_{$key}"} ?: '—' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
