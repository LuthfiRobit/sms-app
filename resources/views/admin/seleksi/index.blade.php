@extends('admin.layouts.app')

@section('title', 'Verifikasi & Seleksi PPDB')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Verifikasi & Seleksi: {{ $jalur->nama }}</h5>
                    <small class="text-muted">
                        Kuota: <strong>{{ $kuota }}%</strong>
                        ({{ count($pendaftaran) > 0 ? round($kuota / 100 * count($pendaftaran)) : 0 }} dari {{ count($pendaftaran) }} peserta) |
                        Total Pendaftar: <strong>{{ count($pendaftaran) }}</strong>
                    </small>
                </div>
                <div>
                    <a href="{{ route('admin.seleksi.hasil', $jalurId) }}" class="btn btn-outline-info">
                        <i class="bi bi-trophy"></i> Lihat Hasil Ranking
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="bi bi-info-circle me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border border-primary shadow-none">
                            <div class="card-body">
                                <h6>Progress Penilaian</h6>
                                <div class="progress mb-2" style="height: 20px;">
                                    @php
                                        $persen = count($pendaftaran) > 0 ? ($sudahDinilai / count($pendaftaran)) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar" role="progressbar" style="width: {{ $persen }}%;" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100">{{ round($persen) }}%</div>
                                </div>
                                <small><strong>{{ $sudahDinilai }}</strong> dari <strong>{{ count($pendaftaran) }}</strong> peserta sudah dinilai.</small>
                                <span class="badge bg-warning ms-2">{{ $belumDinilai }} belum dinilai</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <form action="{{ route('admin.seleksi.hitung-ranking', $jalurId) }}" method="POST" class="d-inline" id="form-hitung-ranking">
                            @csrf
                            <button type="button" class="btn btn-primary mt-3" onclick="confirmHitungRanking()">
                                <i class="bi bi-sort-numeric-down"></i> Hitung Ranking
                            </button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="seleksiTable">
                        <thead>
                            <tr>
                                <th>No Pendaftaran</th>
                                <th>Nama Peserta</th>
                                <th>Status</th>
                                <th>Penilaian</th>
                                <th>Total Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendaftaran as $p)
                                <tr>
                                    <td>{{ $p['no_pendaftaran'] }}</td>
                                    <td>{{ $p['nama_peserta'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $p['status'] == 'verifikasi' ? 'info' : ($p['status'] == 'lulus' ? 'success' : 'secondary') }}">
                                            {{ strtoupper($p['status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(empty($p['nilai']))
                                            <span class="text-danger"><i class="bi bi-x-circle"></i> Belum Dinilai</span>
                                        @else
                                            <ul class="mb-0 ps-3">
                                            @foreach($p['nilai'] as $n)
                                                <li>{{ $n['model_penilaian'] }}: {{ $n['nilai'] }} (bobot: {{ $n['bobot'] }})</li>
                                            @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p['total_nilai'] !== null)
                                            <strong>{{ number_format($p['total_nilai'], 2) }}</strong>
                                            <br><small class="text-muted">Peringkat: {{ $p['peringkat'] ?? '-' }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.seleksi.penilaian', $p['pendaftaran_id']) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> Input Nilai
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#seleksiTable').DataTable();
    });

    function confirmHitungRanking() {
        Swal.fire({
            title: 'Hitung Ranking?',
            text: 'Proses ini akan memperbarui peringkat seluruh pendaftar di jalur ini berdasarkan total nilai yang diinputkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hitung Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form
                document.getElementById('form-hitung-ranking').submit();
                
                // Show loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menghitung ranking untuk semua peserta.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
            }
        });
    }
</script>
@endpush
