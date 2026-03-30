@extends('admin.layouts.app')
@section('title', 'Permission')
@push('css')
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Data Permission (Hak Akses)</h5>
                    <div class="d-flex gap-2">
                        @if(auth()->check() && auth()->user()->hasPermissionTo('admin.system.sync-permissions'))
                        <button type="button" class="btn btn-secondary btn-sm" id="btn-sync-permissions">
                            <i class="las la-sync"></i> Sync
                        </button>
                        @endif
                        @if(auth()->check() && auth()->user()->hasPermissionTo('admin.rbac.permission.store'))
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createModal">
                            <i class="las la-plus"></i> Tambah Permission
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="permission-table" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="120px">Aksi</th>
                                    <th>Route Name</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Show Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150px">Route/Name</th>
                            <td id="show-name">: </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td id="show-description">: </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="show-status">: </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Permission Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-create" action="{{ route('admin.rbac.permission.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Route Name / Permission Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="permission_name"
                                placeholder="contoh: admin.users.index" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" class="form-control" name="permission_description"
                                placeholder="Melihat daftar pengguna">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="is_active_create" checked>
                                <label class="form-check-label" for="is_active_create">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-create">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Route Name / Permission Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="permission_name" id="edit-name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <input type="text" class="form-control" name="permission_description" id="edit-description">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-edit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const table = $('#permission-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.rbac.permission.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'permission_name', name: 'permission_name' },
                    { data: 'permission_description', name: 'permission_description' },
                    { data: 'status', name: 'status' },
                ]
            });

            setupCrudHandlers({
                tableId: '#permission-table',
                createFormId: '#form-create',
                editFormId: '#form-edit',
                createModalId: '#createModal',
                editModalId: '#editModal',
                editUrl: "{{ route('admin.rbac.permission.index') }}/{id}",
                updateUrl: "{{ route('admin.rbac.permission.index') }}/{id}",
                deleteUrl: "{{ route('admin.rbac.permission.index') }}/{id}",
                onEditSuccess: function (data) {
                    $('#edit-id').val(data.id);
                    $('#edit-name').val(data.permission_name);
                    $('#edit-description').val(data.permission_description);
                }
            });

            // Custom handler for toggle status
            $('#permission-table').on('click', '.btn-toggle-status', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.rbac.permission.toggle', ':id') }}".replace(':id', id);

                ResponseHandler.confirm({
                    text: 'Apakah Anda yakin ingin merubah status permission ini?',
                    onConfirm: function () {
                        handleAjax(url, 'POST', {}, function (res) {
                            $('#permission-table').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            });

            // Sync Handler
            $('#btn-sync-permissions').click(function () {
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Syncing...');

                handleAjax("{{ route('admin.system.sync-permissions') }}", 'POST', new FormData(), function (res) {
                    btn.prop('disabled', false).html('<i class="las la-sync"></i> Sync');
                    $('#permission-table').DataTable().ajax.reload();
                }, function () {
                    btn.prop('disabled', false).html('<i class="las la-sync"></i> Sync');
                });
            });

            // Handle Show Detail
            $('#permission-table').on('click', '.btn-show', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.rbac.permission.show', ':id') }}".replace(':id', id);

                handleAjax(url, 'GET', {}, function (response) {
                    let data = response.data;
                    $('#show-name').text(': ' + data.permission_name);
                    $('#show-description').text(': ' + (data.permission_description || '-'));
                    $('#show-status').html(': ' + (data.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>'));
                    $('#showModal').modal('show');
                });
            });
        });
    </script>
@endpush