@extends('admin.layouts.app')
@section('title', 'Template Dokumen PPDB')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
    <style>
        .template-aktif-row { background-color: rgba(25, 135, 84, 0.05); }
        .drop-zone {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background-color 0.2s;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.04);
        }
        .file-preview-name {
            font-weight: 600;
            color: #dc3545;
        }
        .badge-tipe-pengumuman { background-color: #0d6efd; }
        .badge-tipe-kartu { background-color: #0dcaf0; color: #000; }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-file-earmark-pdf me-2"></i>Template Dokumen PPDB</h5>
                        <small class="text-muted">Kelola template PDF untuk pengumuman kelulusan dan kartu peserta. Hanya 1 template aktif per tipe.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.template.store'))
                        <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-upload">
                            <i class="bi bi-upload me-1"></i> Upload Template
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 w-100" id="template-table">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Template</th>
                                <th width="18%">Tipe</th>
                                <th width="18%">File</th>
                                <th width="12%">Status</th>
                                <th width="18%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======== MODAL UPLOAD (TAMBAH) ======== --}}
<div class="modal fade" id="modal-upload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="form-upload" method="POST" action="{{ route('admin.ppdb.template.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-upload me-2"></i>Upload Template Dokumen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="upload-nama"
                                   placeholder="Contoh: Pengumuman Lulus Gelombang 1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Tipe <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="tipe" id="upload-tipe" required>
                                <option value="pengumuman">📢 Pengumuman Kelulusan</option>
                                <option value="kartu_peserta">🪪 Kartu Peserta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">File PDF <span class="text-danger">*</span> <span class="text-muted">(Maks. 5MB)</span></label>
                            <div class="drop-zone" id="drop-zone-upload">
                                <i class="bi bi-file-earmark-pdf fs-2 text-danger d-block mb-2"></i>
                                <p class="mb-1">Drag & drop file PDF kesini, atau</p>
                                <label class="btn btn-outline-primary btn-sm" for="file-upload-input">
                                    <i class="bi bi-folder2-open me-1"></i> Pilih File
                                </label>
                                <input type="file" name="file" id="file-upload-input" accept=".pdf" class="d-none" required>
                                <div id="upload-file-preview" class="mt-3 d-none">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    <span class="file-preview-name" id="upload-file-name"></span>
                                    <span class="text-muted" id="upload-file-size"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Deskripsi</label>
                            <textarea class="form-control form-control-sm" name="deskripsi" id="upload-deskripsi"
                                      rows="2" placeholder="Keterangan tambahan tentang template ini..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_aktif" id="upload-is-aktif" value="1">
                                <label class="form-check-label fw-bold small" for="upload-is-aktif">
                                    Langsung jadikan template aktif?
                                    <small class="text-muted fw-normal ms-1">(Akan menonaktifkan template aktif lain dengan tipe yang sama)</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-upload me-1"></i> Upload & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======== MODAL EDIT ======== --}}
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="form-edit" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-id">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil-square me-2"></i>Edit Template</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="edit-nama" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Tipe <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="tipe" id="edit-tipe" required>
                                <option value="pengumuman">📢 Pengumuman Kelulusan</option>
                                <option value="kartu_peserta">🪪 Kartu Peserta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Ganti File PDF <span class="text-muted">(Opsional — Maks. 5MB)</span></label>
                            <div class="drop-zone" id="drop-zone-edit">
                                <i class="bi bi-file-earmark-pdf fs-2 text-danger d-block mb-2"></i>
                                <p class="mb-1 text-muted fst-italic" id="edit-current-file">File saat ini: —</p>
                                <label class="btn btn-outline-secondary btn-sm" for="file-edit-input">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ganti File
                                </label>
                                <input type="file" name="file" id="file-edit-input" accept=".pdf" class="d-none">
                                <div id="edit-file-preview" class="mt-3 d-none">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    <span class="file-preview-name" id="edit-file-name"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Deskripsi</label>
                            <textarea class="form-control form-control-sm" name="deskripsi" id="edit-deskripsi" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_aktif" id="edit-is-aktif" value="1">
                                <label class="form-check-label fw-bold small" for="edit-is-aktif">
                                    Jadikan template aktif?
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning text-white px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
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
    // ============================
    // DATATABLES
    // ============================
    let table = $('#template-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.ppdb.template.list') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama', name: 'nama' },
            { data: 'tipe_badge', name: 'tipe_badge', orderable: false, searchable: false },
            { data: 'file_link', name: 'file_link', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json' },
        createdRow: function (row, data) {
            if (data.is_aktif) $(row).addClass('template-aktif-row');
        }
    });

    // ============================
    // FILE INPUT — UPLOAD MODAL
    // ============================
    function setupDropZone(dropZoneId, inputId, previewId, nameId, sizeId) {
        const $dropZone = $('#' + dropZoneId);
        const $input    = $('#' + inputId);

        $dropZone.on('click', function(e) {
            if (!$(e.target).is('label, button, input')) $input.trigger('click');
        }).on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        }).on('dragleave', function() {
            $(this).removeClass('dragover');
        }).on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $input[0].files = files;
                $input.trigger('change');
            }
        });

        $input.on('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (file.type !== 'application/pdf') {
                Swal.fire('Format Salah', 'Hanya file PDF yang diizinkan.', 'error');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('File Terlalu Besar', 'Ukuran file maksimal 5MB.', 'error');
                this.value = '';
                return;
            }
            $('#' + nameId).text(file.name);
            if (sizeId) $('#' + sizeId).text(' (' + (file.size / 1024).toFixed(1) + ' KB)');
            $('#' + previewId).removeClass('d-none');
        });
    }

    setupDropZone('drop-zone-upload', 'file-upload-input', 'upload-file-preview', 'upload-file-name', 'upload-file-size');
    setupDropZone('drop-zone-edit', 'file-edit-input', 'edit-file-preview', 'edit-file-name', null);

    // ============================
    // FORM UPLOAD SUBMIT (AJAX)
    // ============================
    $('#form-upload').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        // Pastikan checkbox is_aktif terkirim jika tidak dicentang
        if (!$('#upload-is-aktif').is(':checked')) formData.set('is_aktif', '0');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Swal.fire({ title: 'Mengupload...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            },
            success: function (res) {
                $('#modal-upload').modal('hide');
                $('#form-upload')[0].reset();
                $('#upload-file-preview').addClass('d-none');
                $('.selectpicker').selectpicker('refresh');
                table.ajax.reload();
                Swal.fire('Berhasil!', res.message, 'success');
            },
            error: function (err) {
                const msg = err.responseJSON?.message || err.responseJSON?.errors
                    ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Terjadi kesalahan.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    });

    // ============================
    // TOMBOL EDIT
    // ============================
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.get("{{ route('admin.ppdb.template.index') }}/" + id, function (res) {
            const d = res.data;
            $('#edit-id').val(d.id);
            $('#edit-nama').val(d.nama);
            $('#edit-tipe').val(d.tipe).trigger('change');
            $('#edit-deskripsi').val(d.deskripsi);
            $('#edit-is-aktif').prop('checked', !!d.is_aktif);
            $('#edit-current-file').text('File saat ini: ' + (d.file_template?.split('/').pop() ?? '—'));
            $('#edit-file-preview').addClass('d-none');
            $('#file-edit-input').val('');
            if ($.fn.selectpicker) $('#edit-tipe').selectpicker('refresh');

            // Set form action
            $('#form-edit').attr('action', "{{ route('admin.ppdb.template.index') }}/" + d.id);
            $('#modal-edit').modal('show');
        });
    });

    // ============================
    // FORM EDIT SUBMIT (AJAX — multipart)
    // ============================
    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        const id      = $('#edit-id').val();
        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        if (!$('#edit-is-aktif').is(':checked')) formData.set('is_aktif', '0');

        $.ajax({
            url: "{{ route('admin.ppdb.template.index') }}/" + id,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            },
            success: function (res) {
                $('#modal-edit').modal('hide');
                table.ajax.reload();
                Swal.fire('Berhasil!', res.message, 'success');
            },
            error: function (err) {
                const msg = err.responseJSON?.message || 'Terjadi kesalahan.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    });

    // ============================
    // TOMBOL SET AKTIF
    // ============================
    $(document).on('click', '.btn-set-aktif', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Jadikan Template Aktif?',
            text: 'Template aktif lain dengan tipe yang sama akan dinonaktifkan secara otomatis.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url     : "{{ route('admin.ppdb.template.index') }}/" + id + "/set-aktif",
                method  : 'POST',
                data    : { _token: "{{ csrf_token() }}" },
                success : function (res) { table.ajax.reload(); Swal.fire('Berhasil!', res.message, 'success'); },
                error   : function (err) { Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error'); }
            });
        });
    });

    // ============================
    // TOMBOL HAPUS
    // ============================
    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Template?',
            text: 'File PDF dan data template akan dihapus permanen dari server.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url     : "{{ route('admin.ppdb.template.index') }}/" + id,
                method  : 'DELETE',
                data    : { _token: "{{ csrf_token() }}" },
                success : function (res) { table.ajax.reload(); Swal.fire('Dihapus!', res.message, 'success'); },
                error   : function (err) { Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan.', 'error'); }
            });
        });
    });
});
</script>
@endpush
