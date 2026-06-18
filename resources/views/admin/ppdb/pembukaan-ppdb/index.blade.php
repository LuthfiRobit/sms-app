@extends('admin.layouts.app')
@section('title', 'Pembukaan PPDB')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-door-open me-2"></i> Pembukaan PPDB</h5>
                        <small class="text-muted">Kelola jadwal pembukaan pendaftaran siswa baru per tahun pelajaran.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Pembukaan
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="pembukaan-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Aksi</th>
                                <th>Nama</th>
                                <th>Lembaga</th>
                                <th>Tahun Pelajaran</th>
                                <th>Periode</th>
                                <th width="10%">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.ppdb.pembukaan.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Tambah Pembukaan PPDB</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="lembaga_id" data-live-search="true" required>
                            <option value="">Pilih Lembaga</option>
                            @foreach($lembaga as $lb)
                                <option value="{{ $lb->id }}">{{ $lb->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" placeholder="Contoh: Gelombang 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="tahun_pelajaran_id" data-live-search="true" required>
                            <option value="">Pilih Tahun Pelajaran</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Awal</label>
                        <select class="form-control selectpicker" name="status">
                            <option value="draft">Draft</option>
                            <option value="tutup" selected>Tutup</option>
                            <option value="buka">Buka</option>
                        </select>
                        <small class="text-muted text-warning d-block mt-1">Hanya boleh ada 1 pembukaan berstatus "Buka" per lembaga per tahun pelajaran.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi opsional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Pembukaan PPDB</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lembaga</label>
                        <input type="text" class="form-control" id="edit-lembaga-display" readonly disabled
                               style="background:#f8f9fa;cursor:default">
                        <small class="text-muted">Lembaga tidak dapat diubah setelah pembukaan dibuat.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="tahun_pelajaran_id" id="edit-tahun_pelajaran_id" data-live-search="true" required>
                            <option value="">Pilih Tahun Pelajaran</option>
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" id="edit-mulai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" id="edit-selesai" required>
                        </div>
                    </div>
                    <!-- Status dinonaktifkan di edit, gunakan tombol toggle -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show -->
<div class="modal fade" id="modal-show" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-info-circle me-1"></i> Detail Pembukaan PPDB</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <tr>
                        <th width="40%" class="ps-3">Lembaga</th>
                        <td id="show-lembaga"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Nama</th>
                        <td id="show-nama"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Tahun Pelajaran</th>
                        <td id="show-tahun_pelajaran"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Periode</th>
                        <td id="show-periode"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Status</th>
                        <td id="show-status"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Deskripsi</th>
                        <td id="show-deskripsi"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer bg-light text-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Duplikasi -->
<div class="modal fade" id="modal-duplikasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-duplikasi" method="POST">
                @csrf
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-copy me-1"></i> Duplikasi Pembukaan PPDB</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Info sumber --}}
                    <div class="alert alert-info d-flex align-items-start gap-2 py-2 mb-4">
                        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Sumber:</strong> <span id="duplikasi-sumber-nama">—</span><br>
                            <small class="text-muted">Semua jalur, jadwal, syarat, formulir, dan biaya akan disalin.
                            <strong>Kuota jurusan tidak ikut disalin</strong> (perlu diisi manual setelah duplikasi).</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga Tujuan <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="lembaga_id" id="duplikasi-lembaga_id" data-live-search="true" required>
                                <option value="">Pilih Lembaga Tujuan</option>
                                @foreach($lembaga as $lb)
                                    <option value="{{ $lb->id }}">{{ $lb->nama }} ({{ $lb->jenis }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="tahun_pelajaran_id" id="duplikasi-tahun_pelajaran_id" data-live-search="true" required>
                                <option value="">Pilih Tahun Pelajaran</option>
                                @foreach($tahunPelajaran as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nama Pembukaan Baru <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="duplikasi-nama" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" id="duplikasi-mulai" required>
                            <small class="text-muted">Jadwal akan digeser otomatis mengikuti selisih tanggal ini.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" id="duplikasi-selesai" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status Awal</label>
                            <select class="form-control selectpicker" name="status" id="duplikasi-status">
                                <option value="tutup" selected>Tutup (aman, aktifkan manual)</option>
                                <option value="draft">Draft</option>
                                <option value="buka">Buka Langsung</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-copy me-1"></i> Duplikasi Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize DataTables
        const table = $('#pembukaan-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.ppdb.pembukaan.list') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'nama', name: 'nama' },
                { data: 'lembaga', name: 'lembaga', orderable: false, searchable: false },
                { data: 'tahun_pelajaran', name: 'tahun_pelajaran', orderable: false, searchable: false },
                { data: 'periode', name: 'periode', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false }
            ]
        });

        // Use global CRUD handlers
        setupCrudHandlers({
            tableId: '#pembukaan-table',
            createFormId: '#form-tambah',
            editFormId: '#form-edit',
            createModalId: '#modal-tambah',
            editModalId: '#modal-edit',
            editUrl: "{{ route('admin.ppdb.pembukaan.index') }}/{id}",
            updateUrl: "{{ route('admin.ppdb.pembukaan.index') }}/{id}",
            deleteUrl: "{{ route('admin.ppdb.pembukaan.index') }}/{id}",
            onEditSuccess: function(data) {
                ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');

                // Tampilkan nama lembaga (read-only, tidak bisa diubah)
                $('#edit-lembaga-display').val(data.lembaga ? data.lembaga.nama : '-');

                if ($.fn.selectpicker) {
                    $('#edit-tahun_pelajaran_id').selectpicker('refresh');
                }

                // Format dates for input type date
                if(data.mulai) {
                    let dMulai = new Date(data.mulai);
                    $('#edit-mulai').val(dMulai.toISOString().split('T')[0]);
                }
                if(data.selesai) {
                    let dSelesai = new Date(data.selesai);
                    $('#edit-selesai').val(dSelesai.toISOString().split('T')[0]);
                }
            }
        });

        $('#pembukaan-table').on('click', '.btn-show', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.ppdb.pembukaan.index') }}/" + id;
            
            AjaxHandler.sendGetRequest(url, function(response) {
                if(response.status === 200) {
                    let d = response.data;
                    $('#show-lembaga').text(d.lembaga ? d.lembaga.nama : '-');
                    $('#show-nama').text(d.nama);
                    $('#show-tahun_pelajaran').text(d.tahun_pelajaran ? d.tahun_pelajaran.nama : '-');
                    
                    let mulai = d.mulai ? new Date(d.mulai).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '-';
                    let selesai = d.selesai ? new Date(d.selesai).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '-';
                    $('#show-periode').text(mulai + ' s/d ' + selesai);
                    
                    let statusBadge = '';
                    if(d.status === 'buka') statusBadge = '<span class="badge bg-success">Buka</span>';
                    else if(d.status === 'tutup') statusBadge = '<span class="badge bg-danger">Tutup</span>';
                    else statusBadge = '<span class="badge bg-secondary">' + (d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : '') + '</span>';
                    
                    $('#show-status').html(statusBadge);
                    $('#show-deskripsi').text(d.deskripsi || '-');
                    
                    $('#modal-show').modal('show');
                }
            });
        });

        // Toggle Status Handler
        $('#pembukaan-table').on('click', '.btn-toggle-status', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.ppdb.pembukaan.index') }}/" + id + "/toggle-status";
            
            Swal.fire({
                title: 'Ubah Status?',
                text: "Apakah anda yakin ingin mengubah status pembukaan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleAjax(url, 'POST', {}, function(response) {
                        table.ajax.reload(null, false);
                    });
                }
            });
        });

        // ── Duplikasi Handler ────────────────────────────────────────────────
        $('#pembukaan-table').on('click', '.btn-duplikasi', function () {
            const id      = $(this).data('id');
            const nama    = $(this).data('nama');
            const mulai   = $(this).data('mulai');
            const selesai = $(this).data('selesai');
            const ta      = $(this).data('ta');
            const url     = "{{ route('admin.ppdb.pembukaan.index') }}/" + id + "/duplikasi";

            // Isi form dengan data sumber
            $('#duplikasi-sumber-nama').text(nama);
            $('#duplikasi-nama').val(nama + ' (Duplikasi)');
            $('#duplikasi-mulai').val(mulai ? mulai.substring(0, 10) : '');
            $('#duplikasi-selesai').val(selesai ? selesai.substring(0, 10) : '');

            // Set tahun pelajaran default sama dengan sumber
            $('#duplikasi-tahun_pelajaran_id').val(ta);
            if ($.fn.selectpicker) {
                $('#duplikasi-lembaga_id').selectpicker('refresh');
                $('#duplikasi-tahun_pelajaran_id').selectpicker('val', ta).selectpicker('refresh');
                $('#duplikasi-status').selectpicker('refresh');
            }

            // Kosongkan lembaga tujuan (wajib dipilih manual)
            $('#duplikasi-lembaga_id').val('');
            if ($.fn.selectpicker) $('#duplikasi-lembaga_id').selectpicker('val', '').selectpicker('refresh');

            // Set action form
            $('#form-duplikasi').attr('action', url);

            $('#modal-duplikasi').modal('show');
        });

        // Submit duplikasi via AJAX
        $('#form-duplikasi').on('submit', function (e) {
            e.preventDefault();
            const url  = $(this).attr('action');
            const data = $(this).serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.status === 200) {
                        $('#modal-duplikasi').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: response.message });
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });

    });
</script>
@endpush
