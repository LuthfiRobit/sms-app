@extends('admin.layouts.app')
@section('title', 'Manajemen Pendaftaran')

@push('styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Daftar Pendaftaran</h4>
                    <button class="btn btn-success btn-sm" id="btnExport" style="display: none;">
                        <i class="ri-file-excel-2-line"></i> Ekspor ke CSV
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-control selectpicker" id="filterStatus" multiple data-live-search="true"
                                data-selected-text-format="count > 2" title="Semua Status">
                                <option value="draft">Draft</option>
                                <option value="submit">Submit</option>
                                <option value="verifikasi">Verifikasi</option>
                                <option value="lulus">Lulus</option>
                                <option value="tidak_lulus">Tidak Lulus</option>
                                <option value="daftar_ulang">Daftar Ulang</option>
                                <option value="siswa_tetap">Siswa Tetap</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jalur Pendaftaran</label>
                            <select class="form-control selectpicker" id="filterJalur" data-live-search="true">
                                <option value="">Semua Jalur</option>
                                @foreach($jalur as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun Pelajaran</label>
                            <select class="form-control selectpicker" id="filterTahun" data-live-search="true">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunPelajaran as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Nama Peserta</label>
                            <input type="text" class="form-control" id="filterNama" placeholder="Cari nama...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="tablePendaftaran" class="table table-bordered table-striped align-middle nowrap w-100">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                    </th>
                                    <th>No</th>
                                    <th>Peserta & No. Pendaftaran</th>
                                    <th>Jalur</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function () {
            let table = $('#tablePendaftaran').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.pendaftaran.list') }}",
                    data: function (d) {
                        d.status = $('#filterStatus').val();
                        d.jalur_id = $('#filterJalur').val();
                        d.tahun_id = $('#filterTahun').val();
                        d.nama_peserta = $('#filterNama').val();
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'peserta', orderable: false, searchable: false },
                    { data: 'jalur', orderable: false, searchable: false },
                    { data: 'tanggal_daftar', name: 'tanggal_daftar', searchable: false },
                    { data: 'status_badge', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']]
            });

            $('#filterJalur, #filterTahun').change(function () {
                table.draw();
            });

            // Using select2 for multi status if available, or simple change event
            $('#filterStatus').change(function () {
                table.draw();
            });

            $('#filterNama').keyup(function () {
                table.draw();
            });

            // Checkall
            $('#checkAll').change(function () {
                $('.bulk-select').prop('checked', $(this).prop('checked'));
                toggleExportBtn();
            });

            $(document).on('change', '.bulk-select', function () {
                toggleExportBtn();
            });

            function toggleExportBtn() {
                if ($('.bulk-select:checked').length > 0) {
                    $('#btnExport').show();
                } else {
                    $('#btnExport').hide();
                }
            }
        });
    </script>
@endpush