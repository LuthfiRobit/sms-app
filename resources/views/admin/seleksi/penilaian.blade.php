@extends('admin.layouts.app')

@section('title', 'Input Penilaian')

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Info Peserta -->
        <div class="card">
            <div class="card-header">
                <h5>Data Peserta</h5>
            </div>
            <div class="card-body text-center">
                @if($pendaftaran->peserta?->foto)
                    <img src="{{ Storage::url($pendaftaran->peserta->foto) }}" alt="Foto Peserta" class="img-thumbnail mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center mb-3 mx-auto border rouned" style="width: 150px; height: 150px;">
                        <span class="text-muted">Tidak Ada Foto</span>
                    </div>
                @endif
                <h5 class="mb-1">{{ $pendaftaran->peserta?->nama_lengkap }}</h5>
                <p class="text-muted mb-0">No: <strong>{{ $pendaftaran->no_pendaftaran }}</strong></p>
                <p class="text-muted"><small>NISN: {{ $pendaftaran->peserta?->nisn ?? '-' }}</small></p>
                <a href="{{ route('admin.seleksi.index', $jalurId) }}" class="btn btn-outline-secondary btn-sm mt-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Form Penilaian -->
        <div class="card">
            <div class="card-header">
                <h5>Input Nilai Seleksi</h5>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.seleksi.nilai.store', $pendaftaran->id) }}" method="POST">
                    @csrf
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="nilaiTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Model Penilaian <span class="text-danger">*</span></th>
                                    <th>Nilai (0-100) <span class="text-danger">*</span></th>
                                    <th>Bobot (0.0 - 1.0) <span class="text-danger">*</span></th>
                                    <th width="50">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $existingNilai = old('nilaiData', $pendaftaran->seleksi->toArray());
                                @endphp

                                @if(empty($existingNilai))
                                    <!-- Default Row -->
                                    <tr class="nilai-row">
                                        <td><input type="text" name="nilaiData[0][model_penilaian]" class="form-control model-input" placeholder="e.g. Tes Tulis" required></td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="nilaiData[0][nilai]" class="form-control nilai-input" required></td>
                                        <td><input type="number" step="0.01" min="0" max="1" name="nilaiData[0][bobot]" class="form-control bobot-input" required></td>
                                        <td><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                @else
                                    @foreach($existingNilai as $index => $item)
                                    <tr class="nilai-row">
                                        <td><input type="text" name="nilaiData[{{ $index }}][model_penilaian]" class="form-control model-input" value="{{ $item['model_penilaian'] ?? '' }}" required></td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="nilaiData[{{ $index }}][nilai]" class="form-control nilai-input" value="{{ $item['nilai'] ?? '' }}" required></td>
                                        <td><input type="number" step="0.01" min="0" max="1" name="nilaiData[{{ $index }}][bobot]" class="form-control bobot-input" value="{{ $item['bobot'] ?? '' }}" required></td>
                                        <td><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <button type="button" class="btn btn-sm btn-info" id="btnAddRow"><i class="bi bi-plus"></i> Tambah Kriteria</button>
                                    </td>
                                </tr>
                                <tr class="table-secondary">
                                    <td class="text-end fw-bold">TOTAL BOBOT:</td>
                                    <td colspan="3"><span id="totalBobot" class="fw-bold">0.00</span> <small class="text-muted">(Harus = 1.0)</small></td>
                                </tr>
                                <tr class="table-primary">
                                    <td class="text-end fw-bold">ESTIMASI TOTAL NILAI:</td>
                                    <td colspan="3"><span id="totalNilai" class="fw-bold" style="font-size: 1.2rem;">0.00</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-save"></i> Simpan Penilaian
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let rowCount = $('#nilaiTable tbody tr').length;

        function calculateTotals() {
            let totalBobot = 0;
            let totalNilai = 0;

            $('.nilai-row').each(function() {
                let nilai = parseFloat($(this).find('.nilai-input').val()) || 0;
                let bobot = parseFloat($(this).find('.bobot-input').val()) || 0;
                
                totalBobot += bobot;
                totalNilai += (nilai * bobot);
            });

            $('#totalBobot').text(totalBobot.toFixed(2));
            $('#totalNilai').text(totalNilai.toFixed(2));

            // Validasi tampilan (toleransi 0.01)
            if (Math.abs(totalBobot - 1) <= 0.01) {
                $('#totalBobot').removeClass('text-danger').addClass('text-success');
                $('#btnSubmit').prop('disabled', false);
            } else {
                $('#totalBobot').removeClass('text-success').addClass('text-danger');
            }
        }

        calculateTotals();

        $('#nilaiTable').on('input', '.nilai-input, .bobot-input', function() {
            calculateTotals();
        });

        $('#btnAddRow').click(function() {
            let newRow = `
                <tr class="nilai-row">
                    <td><input type="text" name="nilaiData[${rowCount}][model_penilaian]" class="form-control model-input" required></td>
                    <td><input type="number" step="0.01" min="0" max="100" name="nilaiData[${rowCount}][nilai]" class="form-control nilai-input" required></td>
                    <td><input type="number" step="0.01" min="0" max="1" name="nilaiData[${rowCount}][bobot]" class="form-control bobot-input" required></td>
                    <td><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="bi bi-trash"></i></button></td>
                </tr>
            `;
            $('#nilaiTable tbody').append(newRow);
            rowCount++;
        });

        $('#nilaiTable').on('click', '.btn-remove', function() {
            if ($('.nilai-row').length > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            } else {
                alert('Minimal harus ada 1 kriteria penilaian.');
            }
        });
    });
</script>
@endpush
