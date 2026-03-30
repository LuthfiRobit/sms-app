@extends('admin.layouts.app')
@section('title', 'Manajemen Kurikulum')

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
                        <h5 class="mb-0 text-primary"><i class="bi bi-journal-text me-2"></i> Daftar Kurikulum</h5>
                        <small class="text-muted">Kelola standar kurikulum yang digunakan dalam proses pembelajaran.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.kurikulum.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Kurikulum
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="kurikulum-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Aksi</th>
                                <th>Nama Kurikulum</th>
                                <th>Versi</th>
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
            <form id="form-tambah" action="{{ route('admin.master.kurikulum.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i> Tambah Kurikulum</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kurikulum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kurikulum" placeholder="Contoh: Kurikulum Merdeka" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Versi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="versi" placeholder="Contoh: Rev. 2024" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Deskripsi singkat kurikulum..."></textarea>
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
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Kurikulum</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kurikulum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kurikulum" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Versi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="versi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-control selectpicker" name="status" id="edit-status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3"></textarea>
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
                <h5 class="modal-title text-white"><i class="bi bi-info-circle me-1"></i> Detail Kurikulum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-striped mb-0">
                    <tr>
                        <th width="40%" class="ps-3">Nama Kurikulum</th>
                        <td id="show-nama_kurikulum"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Versi</th>
                        <td id="show-versi"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Status</th>
                        <td id="show-status"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Keterangan</th>
                        <td id="show-keterangan"></td>
                    </tr>
                    <tr>
                        <th class="ps-3">Dibuat Pada</th>
                        <td id="show-created_at"></td>
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
        // Initialize DataTables
        const table = $('#kurikulum-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.master.kurikulum.list') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'nama_kurikulum', name: 'nama_kurikulum' },
                { data: 'versi', name: 'versi' },
                { data: 'status', name: 'status', orderable: false, searchable: false }
            ]
        });

        // Use global CRUD handlers
        setupCrudHandlers({
            tableId: '#kurikulum-table',
            createFormId: '#form-tambah',
            editFormId: '#form-edit',
            createModalId: '#modal-tambah',
            editModalId: '#modal-edit',
            editUrl: "{{ route('admin.master.kurikulum.index') }}/{id}",
            updateUrl: "{{ route('admin.master.kurikulum.index') }}/{id}",
            deleteUrl: "{{ route('admin.master.kurikulum.index') }}/{id}",
            onEditSuccess: function(data) {
                ResponseHandler.handleResponse({ status: 200, data: data }, '#form-edit');
                if ($.fn.selectpicker) {
                    $('#edit-status').selectpicker('refresh');
                }
            }
        });

        $('#kurikulum-table').on('click', '.btn-show', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.master.kurikulum.index') }}/" + id;
            
            AjaxHandler.sendGetRequest(url, function(response) {
                if(response.status === 200) {
                    let d = response.data;
                    $('#show-nama_kurikulum').text(d.nama_kurikulum);
                    $('#show-versi').text(d.versi);
                    $('#show-status').html(d.status === 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>');
                    $('#show-keterangan').text(d.keterangan || '-');
                    $('#show-created_at').text(new Date(d.created_at).toLocaleString('id-ID'));
                    $('#modal-show').modal('show');
                }
            });
        });

        // Toggle Status Handler
        $('#kurikulum-table').on('click', '.btn-toggle-status', function() {
            let id = $(this).data('id');
            let url = "{{ route('admin.master.kurikulum.index') }}/" + id + "/toggle-status";
            
            handleAjax(url, 'POST', {}, function(response) {
                table.ajax.reload(null, false);
            });
        });
    });
</script>
@endpush
