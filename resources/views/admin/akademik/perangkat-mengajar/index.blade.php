@extends('admin.layouts.app')
@section('title', 'Perangkat Mengajar')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-file-earmark-text me-2"></i>Perangkat Mengajar</h5>
                        <small class="text-muted">Kelola perangkat mengajar guru (Silabus, RPP, Prota, Prosem, Modul Ajar).</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.perangkat-mengajar.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Perangkat
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="perangkat-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">Aksi</th>
                                <th width="14%">Guru</th>
                                <th width="13%">Mata Pelajaran</th>
                                <th width="10%">Tahun Pelajaran</th>
                                <th width="8%">Semester</th>
                                <th width="9%">Jenis</th>
                                <th>Judul</th>
                                <th width="8%">Status</th>
                                <th width="9%">File</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah ── --}}
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.akademik.perangkat-mengajar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Perangkat Mengajar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Progress bar --}}
                    <div id="tambah-progress" class="d-none mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Mengunggah file...</small>
                            <small id="tambah-progress-pct" class="text-muted">0%</small>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div id="tambah-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="tambah-lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="tambah-guru_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">
                                        {{ $g->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mata_pelajaran_id" id="tambah-mapel_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" name="semester_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($semesterList as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jenis <span class="text-danger">*</span></label>
                            <select class="form-select" name="jenis" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="RPP">RPP</option>
                                <option value="Silabus">Silabus</option>
                                <option value="Prota">Prota</option>
                                <option value="Prosem">Prosem</option>
                                <option value="Modul Ajar">Modul Ajar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="draft" selected>Draft</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">File</label>
                            <input type="file" class="form-control" name="file" id="tambah-file"
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                            <div class="form-text">PDF, Word, PPT, Excel — maks 10MB</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul" maxlength="255" required
                                   placeholder="Judul dokumen perangkat mengajar">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="2"
                                      placeholder="Keterangan singkat (opsional)"></textarea>
                        </div>
                    </div>
                    <div id="tambah-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-tambah-submit">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Edit ── --}}
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-edit" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Perangkat Mengajar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="edit-lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="edit-guru_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">
                                        {{ $g->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mata_pelajaran_id" id="edit-mapel_id" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="edit-tahun_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" name="semester_id" id="edit-semester_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($semesterList as $s)
                                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jenis <span class="text-danger">*</span></label>
                            <select class="form-select" name="jenis" id="edit-jenis" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="RPP">RPP</option>
                                <option value="Silabus">Silabus</option>
                                <option value="Prota">Prota</option>
                                <option value="Prosem">Prosem</option>
                                <option value="Modul Ajar">Modul Ajar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="draft">Draft</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">File Baru</label>
                            <input type="file" class="form-control" name="file"
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                            <div class="form-text">Biarkan kosong jika tidak diganti</div>
                        </div>
                        <div class="col-md-12" id="edit-file-current-wrap" style="display:none;">
                            <label class="form-label fw-bold">File Saat Ini</label>
                            <div>
                                <a id="edit-file-link" href="#" target="_blank" class="btn btn-sm btn-light-info">
                                    <i class="bi bi-download me-1"></i><span id="edit-file-name"></span>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul" id="edit-judul" maxlength="255" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="2"></textarea>
                        </div>
                    </div>
                    <div id="edit-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn-edit-submit">
                        <i class="bi bi-check-lg me-1"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    var table = $('#perangkat-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.akademik.perangkat-mengajar.list') }}',
        columns: [
            { data: 'DT_RowIndex',   orderable: false, searchable: false },
            { data: 'action',        orderable: false, searchable: false },
            { data: 'guru_nama' },
            { data: 'mapel_nama' },
            { data: 'tahun_nama' },
            { data: 'semester_nama' },
            { data: 'jenis_badge',   orderable: false },
            { data: 'judul' },
            { data: 'status_badge',  orderable: false },
            { data: 'file_link',     orderable: false, searchable: false },
        ],
        language: {
            sEmptyTable: 'Tidak ada data perangkat mengajar',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst:'Pertama', sPrevious:'Sebelumnya', sNext:'Selanjutnya', sLast:'Terakhir' },
        },
    });

    // Filter guru by lembaga on tambah modal
    function filterGuruByLembaga(lembagaId, guruSelectId, mapelSelectId) {
        var $guru = $(guruSelectId);
        var $mapel = $(mapelSelectId);
        $guru.find('option[data-lembaga]').each(function () {
            var opt = $(this);
            if (!lembagaId || opt.data('lembaga') == lembagaId) {
                opt.show();
            } else {
                opt.hide();
                if (opt.is(':selected')) opt.prop('selected', false);
            }
        });
        $mapel.find('option[data-lembaga]').each(function () {
            var opt = $(this);
            if (!lembagaId || opt.data('lembaga') == lembagaId || opt.data('lembaga') === '') {
                opt.show();
            } else {
                opt.hide();
                if (opt.is(':selected')) opt.prop('selected', false);
            }
        });
    }

    $('#tambah-lembaga_id').on('change', function () {
        filterGuruByLembaga($(this).val(), '#tambah-guru_id', '#tambah-mapel_id');
    }).trigger('change');

    // ── Tambah ─────────────────────────────────────────────────────────────
    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn-tambah-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        var formData = new FormData(this);

        // Show progress if file attached
        var hasFile = $('#tambah-file')[0].files.length > 0;
        if (hasFile) {
            $('#tambah-progress').removeClass('d-none');
        }

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        var pct = Math.round((e.loaded / e.total) * 100);
                        $('#tambah-progress-bar').css('width', pct + '%');
                        $('#tambah-progress-pct').text(pct + '%');
                    }
                });
                return xhr;
            },
            success: function (res) {
                if (res.status === 200) {
                    $('#modal-tambah').modal('hide');
                    $('#form-tambah')[0].reset();
                    $('#tambah-progress').addClass('d-none');
                    $('#tambah-progress-bar').css('width', '0%');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                } else {
                    showAlert('#tambah-alert', 'danger', res.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showAlert('#tambah-alert', 'danger', msg);
                $('#tambah-progress').addClass('d-none');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan');
            }
        });
    });

    // ── Edit ───────────────────────────────────────────────────────────────
    window.editPerangkat = function (id) {
        $.get('{{ url('admin/akademik/perangkat-mengajar') }}/' + id, function (res) {
            if (res.status === 200) {
                var d = res.data;
                $('#form-edit').attr('action', '{{ url('admin/akademik/perangkat-mengajar') }}/' + id);
                $('#edit-lembaga_id').val(d.lembaga_id);
                filterGuruByLembaga(d.lembaga_id, '#edit-guru_id', '#edit-mapel_id');
                $('#edit-guru_id').val(d.guru_id);
                $('#edit-mapel_id').val(d.mata_pelajaran_id);
                $('#edit-tahun_id').val(d.tahun_pelajaran_id);
                $('#edit-semester_id').val(d.semester_id);
                $('#edit-jenis').val(d.jenis);
                $('#edit-status').val(d.status);
                $('#edit-judul').val(d.judul);
                $('#edit-deskripsi').val(d.deskripsi);
                if (d.file_path) {
                    $('#edit-file-current-wrap').show();
                    $('#edit-file-name').text(d.file_name ?? 'Unduh File');
                    $('#edit-file-link').attr('href', '/storage/' + d.file_path);
                } else {
                    $('#edit-file-current-wrap').hide();
                }
                $('#edit-alert').addClass('d-none');
                $('#modal-edit').modal('show');
            }
        });
    };

    $('#edit-lembaga_id').on('change', function () {
        filterGuruByLembaga($(this).val(), '#edit-guru_id', '#edit-mapel_id');
    });

    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn-edit-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        var formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.status === 200) {
                    $('#modal-edit').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                } else {
                    showAlert('#edit-alert', 'danger', res.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                showAlert('#edit-alert', 'danger', msg);
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Perbarui');
            }
        });
    });

    // ── Hapus ──────────────────────────────────────────────────────────────
    window.hapusPerangkat = function (id, judul) {
        Swal.fire({
            title: 'Hapus Perangkat?',
            text: '"' + judul + '" akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/akademik/perangkat-mengajar') }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.message, timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menghapus.' });
                }
            });
        });
    };

    function showAlert(selector, type, msg) {
        $(selector).removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type).html(msg);
    }
});
</script>
@endpush
