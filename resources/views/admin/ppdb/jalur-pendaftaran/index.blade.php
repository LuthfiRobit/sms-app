@extends('admin.layouts.app')
@section('title', 'Jalur Pendaftaran')

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
                        <h5 class="mb-0 text-primary"><i class="bi bi-diagram-3 me-2"></i> Jalur Pendaftaran</h5>
                        <small class="text-muted">Kelola jalur pendaftaran per pembukaan PPDB.</small>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light border-bottom">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Pilih Pembukaan PPDB:</label>
                        <select class="form-control selectpicker" id="filter-pembukaan" data-live-search="true">
                            <option value="">-- Pilih Pembukaan --</option>
                            @foreach($pembukaanPpdb as $pembukaan)
                                <option value="{{ $pembukaan->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $pembukaan->nama }} ({{ $pembukaan->status }})</option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.jalur.store'))
                    <div class="col-md-7 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah" id="btn-tambah-jalur">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Jalur
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="jalur-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Aksi</th>
                                <th width="15%">Kode Jalur</th>
                                <th>Nama</th>
                                <th width="10%">Kuota</th>
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
            <form id="form-tambah" action="{{ route('admin.ppdb.jalur.store') }}" method="POST">
                @csrf
                <input type="hidden" name="pembukaan_ppdb_id" id="tambah-pembukaan_ppdb_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Tambah Jalur Pendaftaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Jalur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_jalur" placeholder="Contoh: ZONASI" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Jalur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" placeholder="Contoh: Jalur Zonasi" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kuota (%)</label>
                            <input type="number" class="form-control" name="kuota" min="0" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Urutan</label>
                            <input type="number" class="form-control" name="urutan" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
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
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Jalur Pendaftaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Jalur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_jalur" id="edit-kode_jalur" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Jalur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kuota (%)</label>
                            <input type="number" class="form-control" name="kuota" id="edit-kuota" min="0" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Urutan</label>
                            <input type="number" class="form-control" name="urutan" id="edit-urutan" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="status" id="edit-status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
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
                <h5 class="modal-title text-white"><i class="bi bi-info-circle me-1"></i> Detail Jalur Pendaftaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <tr>
                        <th width="40%" class="ps-3">Kode Jalur</th>
                        <td id="show-kode_jalur"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Nama</th>
                        <td id="show-nama"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Kuota</th>
                        <td id="show-kuota"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Urutan</th>
                        <td id="show-urutan"></td>
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
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function () {
        let currentPembukaanId = $('#filter-pembukaan').val();

        // Initialize DataTables
        const table = $('#jalur-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.ppdb.jalur.list') }}",
                data: function(d) {
                    d.pembukaan_ppdb_id = $('#filter-pembukaan').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'kode_jalur', name: 'kode_jalur' },
                { data: 'nama', name: 'nama' },
                { data: 'kuota', name: 'kuota', render: function(data){ return data ? data + '%' : '-'; } },
                { data: 'status', name: 'status', orderable: false, searchable: false }
            ]
        });

        // Filter on change
        $('#filter-pembukaan').on('change', function() {
            currentPembukaanId = $(this).val();
            table.ajax.reload();
        });

        // Set pembukaan ID before showing modal
        $('#btn-tambah-jalur').on('click', function(e) {
            if(!currentPembukaanId) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Silahkan pilih Pembukaan PPDB terlebih dahulu!'
                });
                return false;
            }
            $('#tambah-pembukaan_ppdb_id').val(currentPembukaanId);
        });

        // Use global CRUD handlers
        setupCrudHandlers({
            tableId: '#jalur-table',
            createFormId: '#form-tambah',
            editFormId: '#form-edit',
            createModalId: '#modal-tambah',
            editModalId: '#modal-edit',
            editUrl: "{{ route('admin.ppdb.jalur.index') }}/{id}",
            updateUrl: "{{ route('admin.ppdb.jalur.index') }}/{id}",
            deleteUrl: "{{ route('admin.ppdb.jalur.index') }}/{id}",
            onEditSuccess: function(data) {
                ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');
                if ($.fn.selectpicker) {
                    $('#edit-status').selectpicker('refresh');
                }
            }
        });

        $('#jalur-table').on('click', '.btn-show', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.ppdb.jalur.index') }}/" + id;
            
            AjaxHandler.sendGetRequest(url, function(response) {
                if(response.status === 200) {
                    let d = response.data;
                    $('#show-kode_jalur').text(d.kode_jalur);
                    $('#show-nama').text(d.nama);
                    $('#show-kuota').text(d.kuota ? d.kuota + '%' : '-');
                    $('#show-urutan').text(d.urutan || '-');
                    
                    let statusBadge = d.status === 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>';
                    $('#show-status').html(statusBadge);
                    $('#show-deskripsi').text(d.deskripsi || '-');
                    
                    $('#modal-show').modal('show');
                }
            });
        });
    });
</script>
@endpush
