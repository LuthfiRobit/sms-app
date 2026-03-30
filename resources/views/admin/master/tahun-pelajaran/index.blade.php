@extends('admin.layouts.app')
@section('title', 'Tahun Ajaran & Semester')

@push('styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
@endpush

@section('content')
    <div class="row">
        <!-- Tahun Pelajaran Card -->
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Data Tahun Pelajaran</h5>
                    @if(auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.store'))
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-lg"></i> Tambah Tahun Pelajaran
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tahun-pelajaran-table" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="120px">Aksi</th>
                                    <th>Kode Tahun</th>
                                    <th>Nama Tahun Pelajaran</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Semester Card -->
        <div class="col-sm-12 mt-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Data Semester</h5>
                    @if(auth()->user()->hasPermissionTo('admin.master.semester.store'))
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createSemesterModal">
                        <i class="bi bi-plus-lg"></i> Tambah Semester
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="semester-table" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="120px">Aksi</th>
                                    <th>Tahun Pelajaran</th>
                                    <th>Nama Semester</th>
                                    <th>Smtr Ke</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
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

    <!-- Tahun Pelajaran Modals -->
    <!-- Show Modal -->
    @if(auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.show'))
    <div class="modal fade" id="showModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tahun Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150px">Kode Tahun</th>
                            <td id="show-kode-tahun">: </td>
                        </tr>
                        <tr>
                            <th>Nama Tahun</th>
                            <td id="show-nama">: </td>
                        </tr>
                        <tr>
                            <th>Mulai</th>
                            <td id="show-mulai">: </td>
                        </tr>
                        <tr>
                            <th>Selesai</th>
                            <td id="show-selesai">: </td>
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
    @endif

    <!-- Create Modal -->
    @if(auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.store'))
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tahun Pelajaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-create" action="{{ route('admin.master.tahun-pelajaran.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Tahun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kode_tahun" placeholder="Contoh: 2026/2027" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Tahun Pelajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Tahun Pelajaran 2026/2027" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Modal -->
    @if(auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.update'))
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tahun Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Tahun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kode_tahun" id="edit-kode-tahun" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Tahun Pelajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="edit-nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" id="edit-mulai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" id="edit-selesai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="status" id="edit-status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Semester Modals -->
    <!-- Show Semester Modal -->
    @if(auth()->user()->hasPermissionTo('admin.master.semester.show'))
    <div class="modal fade" id="showSemesterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="150px">Tahun Pelajaran</th>
                            <td id="show-semester-tp">: </td>
                        </tr>
                        <tr>
                            <th>Nama Semester</th>
                            <td id="show-semester-nama">: </td>
                        </tr>
                        <tr>
                            <th>Semester Ke</th>
                            <td id="show-semester-ke">: </td>
                        </tr>
                        <tr>
                            <th>Mulai</th>
                            <td id="show-semester-mulai">: </td>
                        </tr>
                        <tr>
                            <th>Selesai</th>
                            <td id="show-semester-selesai">: </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="show-semester-status">: </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Semester Modal -->
    @if(auth()->user()->hasPermissionTo('admin.master.semester.store'))
    <div class="modal fade" id="createSemesterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Semester Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-create-semester" action="{{ route('admin.master.semester.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="tahun_pelajaran_id" required data-live-search="true">
                                <option value="">Pilih Tahun Pelajaran</option>
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Semester <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Semester Ganjil" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester Ke <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="semester_ke" required>
                                <option value="1">1 (Ganjil)</option>
                                <option value="2">2 (Genap)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Semester Modal -->
    @if(auth()->user()->hasPermissionTo('admin.master.semester.update'))
    <div class="modal fade" id="editSemesterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit-semester" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="tahun_pelajaran_id" id="edit-semester-tp-id" required data-live-search="true">
                                @foreach($tahunPelajarans as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Semester <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="edit-semester-nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester Ke <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="semester_ke" id="edit-semester-ke" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="mulai" id="edit-semester-mulai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="selesai" id="edit-semester-selesai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="status" id="edit-semester-status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
    <script>
        $(document).ready(function () {
            // Re-initialize selectpicker to be safe
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker();
            }

            // Tahun Pelajaran Logic
            const table = $('#tahun-pelajaran-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master.tahun-pelajaran.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'kode_tahun', name: 'kode_tahun' },
                    { data: 'nama', name: 'nama' },
                    { data: 'mulai', name: 'mulai' },
                    { data: 'selesai', name: 'selesai' },
                    { data: 'status', name: 'status' },
                ]
            });

            setupCrudHandlers({
                tableId: '#tahun-pelajaran-table',
                createFormId: '#form-create',
                editFormId: '#form-edit',
                createModalId: '#createModal',
                editModalId: '#editModal',
                editUrl: "{{ route('admin.master.tahun-pelajaran.index') }}/{id}",
                updateUrl: "{{ route('admin.master.tahun-pelajaran.index') }}/{id}",
                deleteUrl: "{{ route('admin.master.tahun-pelajaran.index') }}/{id}",
                onEditSuccess: function (data) {
                    $('#edit-kode-tahun').val(data.kode_tahun);
                    $('#edit-nama').val(data.nama);
                    $('#edit-mulai').val(data.mulai);
                    $('#edit-selesai').val(data.selesai);
                    if ($.fn.selectpicker) {
                        $('#edit-status').val(data.status).selectpicker('refresh');
                    } else {
                        $('#edit-status').val(data.status);
                    }
                }
            });

            // Toggle Status Tahun Pelajaran
            $('#tahun-pelajaran-table').on('click', '.btn-toggle-status', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.master.tahun-pelajaran.toggle', ':id') }}".replace(':id', id);

                ResponseHandler.confirm({
                    text: 'Apakah Anda yakin ingin merubah status tahun pelajaran ini?',
                    onConfirm: function () {
                        handleAjax(url, 'POST', {}, function (res) {
                            $('#tahun-pelajaran-table').DataTable().ajax.reload(null, false);
                            $('#semester-table').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            });

            // Show Detail Tahun Pelajaran
            $('#tahun-pelajaran-table').on('click', '.btn-show', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.master.tahun-pelajaran.show', ':id') }}".replace(':id', id);

                handleAjax(url, 'GET', {}, function (response) {
                    let data = response.data;
                    $('#show-kode-tahun').text(': ' + data.kode_tahun);
                    $('#show-nama').text(': ' + data.nama);
                    $('#show-mulai').text(': ' + data.mulai);
                    $('#show-selesai').text(': ' + data.selesai);
                    $('#show-status').html(': ' + (data.status == 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>'));
                    $('#showModal').modal('show');
                });
            });

            // Semester Logic
            const semesterTable = $('#semester-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.master.semester.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'tahun_pelajaran', name: 'tahun_pelajaran' },
                    { data: 'nama', name: 'nama' },
                    { data: 'semester_ke', name: 'semester_ke' },
                    { data: 'mulai', name: 'mulai' },
                    { data: 'selesai', name: 'selesai' },
                    { data: 'status', name: 'status' },
                ]
            });

            setupCrudHandlers({
                tableId: '#semester-table',
                createFormId: '#form-create-semester',
                editFormId: '#form-edit-semester',
                createModalId: '#createSemesterModal',
                editModalId: '#editSemesterModal',
                editUrl: "{{ route('admin.master.semester.index') }}/{id}",
                updateUrl: "{{ route('admin.master.semester.index') }}/{id}",
                deleteUrl: "{{ route('admin.master.semester.index') }}/{id}",
                onEditSuccess: function (data) {
                    if ($.fn.selectpicker) {
                        $('#edit-semester-tp-id').val(data.tahun_pelajaran_id).selectpicker('refresh');
                        $('#edit-semester-ke').val(data.semester_ke).selectpicker('refresh');
                        $('#edit-semester-status').val(data.status).selectpicker('refresh');
                    } else {
                        $('#edit-semester-tp-id').val(data.tahun_pelajaran_id);
                        $('#edit-semester-ke').val(data.semester_ke);
                        $('#edit-semester-status').val(data.status);
                    }
                    $('#edit-semester-nama').val(data.nama);
                    $('#edit-semester-mulai').val(data.mulai);
                    $('#edit-semester-selesai').val(data.selesai);
                }
            });

            // Toggle Status Semester
            $('#semester-table').on('click', '.btn-toggle-status-semester', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.master.semester.toggle', ':id') }}".replace(':id', id);

                ResponseHandler.confirm({
                    text: 'Apakah Anda yakin ingin merubah status semester ini?',
                    onConfirm: function () {
                        handleAjax(url, 'POST', {}, function (res) {
                            $('#semester-table').DataTable().ajax.reload(null, false);
                        });
                    }
                });
            });

            // Show Detail Semester
            $('#semester-table').on('click', '.btn-show-semester', function () {
                let id = $(this).data('id');
                let url = "{{ route('admin.master.semester.show', ':id') }}".replace(':id', id);

                handleAjax(url, 'GET', {}, function (response) {
                    let data = response.data;
                    $('#show-semester-tp').text(': ' + (data.tahun_pelajaran ? data.tahun_pelajaran.nama : '-'));
                    $('#show-semester-nama').text(': ' + data.nama);
                    $('#show-semester-ke').text(': ' + data.semester_ke);
                    $('#show-semester-mulai').text(': ' + data.mulai);
                    $('#show-semester-selesai').text(': ' + data.selesai);
                    $('#show-semester-status').html(': ' + (data.status == 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>'));
                    $('#showSemesterModal').modal('show');
                });
            });
        });
    </script>
@endpush
