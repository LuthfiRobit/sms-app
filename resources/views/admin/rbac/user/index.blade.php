@extends('admin.layouts.app')
@section('title', 'Data Pengguna')
@push('styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Data Pengguna Terdaftar</h5>
                    @if(auth()->check() && auth()->user()->hasPermissionTo('admin.rbac.user.store'))
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="las la-user-plus"></i> Tambah User
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="user-table" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="120px">Aksi</th>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Roles</th>
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
                    <h5 class="modal-title">Detail User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150px">Nama Lengkap</th>
                            <td id="show-name">: </td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td id="show-username">: </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td id="show-email">: </td>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-create" action="{{ route('admin.rbac.user.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" required
                                    autocomplete="new-password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" name="status" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign Roles</label>
                                <select class="form-control selectpicker" id="create-roles" name="roles[]"
                                    multiple="multiple" data-placeholder="Pilih Role (Bisa lebih dari satu)"
                                    data-live-search="true" data-actions-box="true" style="width: 100%;">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name }} ({{ $role->scope }})</option>
                                    @endforeach
                                </select>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit-name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" id="edit-username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="edit-email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-muted">(Kosongkan jika tidak
                                        diubah)</span></label>
                                <input type="password" class="form-control" name="password" autocomplete="new-password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" name="status" id="edit-status" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign Roles</label>
                                <select class="form-control selectpicker" name="roles[]" id="edit-roles" multiple="multiple"
                                    data-placeholder="Pilih Role" data-live-search="true" data-actions-box="true"
                                    style="width: 100%;">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name }} ({{ $role->scope }})</option>
                                    @endforeach
                                </select>
                            </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function () {
            const table = $('#user-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.rbac.user.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'username', name: 'username' },
                    { data: 'email', name: 'email' },
                    { data: 'roles', name: 'roles', orderable: false },
                    { data: 'status', name: 'status' },
                ]
            });

            setupCrudHandlers({
                tableId: '#user-table',
                createFormId: '#form-create',
                editFormId: '#form-edit',
                createModalId: '#createModal',
                editModalId: '#editModal',
                editUrl: "{{ route('admin.rbac.user.index') }}/{id}",
                updateUrl: "{{ route('admin.rbac.user.index') }}/{id}",
                deleteUrl: "{{ route('admin.rbac.user.index') }}/{id}",
                onEditSuccess: function (response) {
                    // response is custom parsed in controller to wrap user and role_ids
                    let data = response.user;
                    let roleIds = response.role_ids ? response.role_ids.map(id => String(id)) : [];

                    $('#edit-id').val(data.id_user);
                    $('#edit-name').val(data.name);
                    $('#edit-username').val(data.username);
                    $('#edit-email').val(data.email);
                    $('#edit-status').val(data.status);
                    $('#edit-roles').val(roleIds);
                }
            });

            // custom handler trigger toggle status
            $('#user-table').on('click', '.btn-toggle-status', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.rbac.user.toggle', ':id') }}".replace(':id', id);

                ResponseHandler.confirm({
                    text: 'Apakah Anda yakin ingin merubah status user ini?',
                    onConfirm: function () {
                        handleAjax(url, 'POST', {}, function (res) {
                            $('#user-table').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            });

            // Handle Show Detail
            $('#user-table').on('click', '.btn-show', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.rbac.user.show', ':id') }}".replace(':id', id);

                handleAjax(url, 'GET', {}, function (response) {
                    let data = response.data;
                    $('#show-name').text(': ' + data.name);
                    $('#show-username').text(': ' + data.username);
                    $('#show-email').text(': ' + data.email);
                    $('#show-status').html(': ' + (data.status == 'active' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>'));
                    $('#showModal').modal('show');
                });
            });
        });
    </script>
@endpush