@extends('admin.layouts.app')
@section('title', 'Role Management')
@push('styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Data Role Akses</h5>
                    @if(auth()->check() && auth()->user()->hasPermissionTo('admin.rbac.role.store'))
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="las la-plus"></i> Tambah Role
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="role-table" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="120px">Aksi</th>
                                    <th>Role Name</th>
                                    <th>Display Name</th>
                                    <th>Scope</th>
                                    <th>Keterangan</th>
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
                    <h5 class="modal-title">Detail Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150px">Role Name</th>
                            <td id="show-name">: </td>
                        </tr>
                        <tr>
                            <th>Display Name</th>
                            <td id="show-display_name">: </td>
                        </tr>
                        <tr>
                            <th>Scope</th>
                            <td id="show-scope">: </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td id="show-description">: </td>
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
                    <h5 class="modal-title">Tambah Role Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-create" action="{{ route('admin.rbac.role.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Role Name (Gunakan underscore, contoh: wali_kelas) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="display_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Scope/Konteks <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="scope" required data-live-search="true">
                                <option value="global">Global (Seluruh Sistem)</option>
                                <option value="unit">Unit / Cabang</option>
                                <option value="class">Kelas</option>
                                <option value="subject">Mata Pelajaran</option>
                                <option value="student">Siswa Spesifik</option>
                                <option value="personal">Personal</option>
                                <option value="process">Proses Bisnis</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
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
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit-name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="display_name" id="edit-display_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Scope <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="scope" id="edit-scope" required
                                data-live-search="true">
                                <option value="global">Global</option>
                                <option value="unit">Unit</option>
                                <option value="class">Kelas</option>
                                <option value="subject">Mata Pelajaran</option>
                                <option value="student">Siswa Spesifik</option>
                                <option value="personal">Personal</option>
                                <option value="process">Proses Bisnis</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" id="edit-description" rows="3"></textarea>
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
            const table = $('#role-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.rbac.role.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'display_name', name: 'display_name' },
                    { data: 'scope', name: 'scope' },
                    { data: 'description', name: 'description' },
                ]
            });

            // Setup CRUD Handlers from global handler
            setupCrudHandlers({
                tableId: '#role-table',
                createFormId: '#form-create',
                editFormId: '#form-edit',
                createModalId: '#createModal',
                editModalId: '#editModal',
                editUrl: "{{ route('admin.rbac.role.index') }}/{id}",
                updateUrl: "{{ route('admin.rbac.role.index') }}/{id}",
                deleteUrl: "{{ route('admin.rbac.role.index') }}/{id}",
                onEditSuccess: function (data) {
                    $('#edit-id').val(data.id);
                    $('#edit-name').val(data.name);
                    $('#edit-display_name').val(data.display_name);
                    $('#edit-scope').val(data.scope);
                    $('#edit-description').val(data.description);
                }
            });

            // Handle Show Detail
            $('#role-table').on('click', '.btn-show', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.rbac.role.show', ':id') }}".replace(':id', id);

                handleAjax(url, 'GET', {}, function (response) {
                    let data = response.data;
                    $('#show-name').text(': ' + data.name);
                    $('#show-display_name').text(': ' + data.display_name);
                    $('#show-scope').text(': ' + data.scope);
                    $('#show-description').text(': ' + (data.description || '-'));
                    $('#showModal').modal('show');
                });
            });
        });
    </script>
@endpush