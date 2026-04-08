@extends('admin.layouts.app')

@section('title', 'Hasil Seleksi & Ranking')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Hasil Seleksi & Ranking: {{ $data['jalur']['nama'] }}</h5>
                <p class="text-muted mb-0">Kuota Tersedia: <strong>{{ $data['jalur']['kuota'] }}</strong></p>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white border-0">
                            <div class="card-body text-center p-3">
                                <h3 class="text-white mb-1">{{ $data['statistik']['total_peserta'] }}</h3>
                                <span>Total Peserta Diperingkat</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white border-0">
                            <div class="card-body text-center p-3">
                                <h3 class="text-white mb-1">{{ $data['statistik']['total_lulus'] }}</h3>
                                <span>Lulus</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white border-0">
                            <div class="card-body text-center p-3">
                                <h3 class="text-white mb-1">{{ $data['statistik']['total_cadangan'] }}</h3>
                                <span>Cadangan</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white border-0">
                            <div class="card-body text-center p-3">
                                <h3 class="text-white mb-1">{{ $data['statistik']['total_tidak_lulus'] }}</h3>
                                <span>Tidak Lulus</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="{{ route('admin.seleksi.index', $jalurId) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Seleksi
                        </a>
                    </div>
                    <div>
                        @if($data['sudah_diumumkan'])
                            <span class="badge bg-success me-2 p-2">Sudah Diumumkan: {{ $data['waktu_pengumuman'] }}</span>
                            <a href="{{ route('admin.seleksi.download-pengumuman', $jalurId) }}" class="btn btn-dark" target="_blank">
                                <i class="bi bi-file-pdf"></i> Download PDF Pengumuman
                            </a>
                        @else
                            <form action="{{ route('admin.seleksi.pengumuman', $jalurId) }}" method="POST" class="d-inline" id="form-umumkan-hasil">
                                @csrf
                                <button type="button" class="btn btn-warning fw-bold text-dark" onclick="confirmUmumkanHasil()">
                                    <i class="bi bi-megaphone-fill"></i> Umumkan Hasil
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label>Filter Status:</label>
                    <select id="statusFilter" class="form-select w-auto d-inline-block ms-2">
                        <option value="">Semua Status</option>
                        <option value="lulus">Lulus</option>
                        <option value="cadangan">Cadangan</option>
                        <option value="tidak_lulus">Tidak Lulus</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="rankingTable">
                        <thead>
                            <tr class="table-dark">
                                <th>Peringkat</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Peserta</th>
                                <th>NISN</th>
                                <th>Total Nilai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $lulus = collect($data['lulus'])->map(fn($item) => is_array($item) ? $item : $item->toArray())->toArray();
                                $cadangan = collect($data['cadangan'])->map(fn($item) => is_array($item) ? $item : $item->toArray())->toArray();
                                $tidakLulus = collect($data['tidak_lulus'])->map(fn($item) => is_array($item) ? $item : $item->toArray())->toArray();

                                $allPeserta = array_merge($lulus, $cadangan, $tidakLulus);
                                // Sort array by peringkat
                                usort($allPeserta, function($a, $b) {
                                    return $a['peringkat'] <=> $b['peringkat'];
                                });
                            @endphp

                            @foreach($allPeserta as $p)
                                <tr>
                                    <td class="text-center fw-bold fs-5">{{ $p['peringkat'] }}</td>
                                    <td>{{ $p['no_pendaftaran'] }}</td>
                                    <td>{{ $p['nama_peserta'] }}</td>
                                    <td>{{ $p['nisn'] }}</td>
                                    <td class="text-end fw-bold">{{ number_format($p['total_nilai'], 2) }}</td>
                                    <td class="text-center">
                                        @if($p['status_kelulusan'] == 'lulus')
                                            <span class="badge bg-success status-badge" data-status="lulus">LULUS</span>
                                        @elseif($p['status_kelulusan'] == 'cadangan')
                                            <span class="badge bg-warning text-dark status-badge" data-status="cadangan">CADANGAN</span>
                                        @else
                                            <span class="badge bg-danger status-badge" data-status="tidak_lulus">TIDAK LULUS</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($data['sudah_diumumkan'] && in_array($p['status_kelulusan'], ['lulus', 'cadangan']))
                                            <a href="{{ route('admin.seleksi.download-kartu', $p['pendaftaran_id']) }}" class="btn btn-sm btn-info" title="Download Kartu Peserta" target="_blank">
                                                <i class="bi bi-file-earmark-person"></i>
                                            </a>
                                        @else
                                            <span class="text-muted"><small>-</small></span>
                                        @endif
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
        var table = $('#rankingTable').DataTable({
            "order": [[ 0, "asc" ]] // Sort by peringkat
        });

        $('#statusFilter').on('change', function() {
            var val = $(this).val();
            if (val === 'lulus') {
                table.search('LULUS', true, false).draw();
            } else if (val === 'cadangan') {
                table.search('CADANGAN', true, false).draw();
            } else if (val === 'tidak_lulus') {
                table.search('TIDAK LULUS', true, false).draw();
            } else {
                table.search('').draw();
            }
        });
    });

    function confirmUmumkanHasil() {
        Swal.fire({
            title: 'PERHATIAN!',
            text: 'Apakah Anda yakin ingin mempublikasikan hasil ini? Tindakan ini akan mengupdate status pendaftaran, mengirim notifikasi ke peserta, dan menggenerate PDF. Data yang sudah diumumkan akan sulit dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f3c74d',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Umumkan Hasil!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form
                document.getElementById('form-umumkan-hasil').submit();
                
                // Show loading state
                Swal.fire({
                    title: 'Mempublikasikan Pengumuman...',
                    text: 'Sedang memproses kelulusan, men-generate PDF, dan mengirim notifikasi.',
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
