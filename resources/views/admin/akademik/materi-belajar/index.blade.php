@extends('admin.layouts.app')
@section('title', 'Materi Belajar')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-book me-2"></i>Materi Belajar</h5>
                        <small class="text-muted">Kelola materi belajar yang dibagikan guru ke siswa per rombel.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.materi-belajar.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Materi
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="materi-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">Aksi</th>
                                <th>Judul</th>
                                <th width="14%">Guru</th>
                                <th width="12%">Mata Pelajaran</th>
                                <th width="13%">Rombel</th>
                                <th width="9%">Tanggal</th>
                                <th width="8%">Status</th>
                                <th width="12%">File/Link</th>
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
            <form id="form-tambah" action="{{ route('admin.akademik.materi-belajar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Materi Belajar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="tambah-lembaga" required>
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
                            <select class="form-select" name="guru_id" id="tambah-guru" required>
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
                            <select class="form-select" name="mata_pelajaran_id" id="tambah-mapel" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rombel</label>
                            <select class="form-select" name="rombel_id" id="tambah-rombel">
                                <option value="">-- Semua Rombel --</option>
                                @foreach($rombelList as $r)
                                    <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                        Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Pertemuan Ke</label>
                            <input type="number" class="form-control" name="pertemuan_ke" min="1" max="100" placeholder="Contoh: 1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="pending" selected>Menunggu Verifikasi</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul" maxlength="255" required placeholder="Judul materi">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi / RPP Terstruktur</label>
                            <textarea class="form-control" name="deskripsi" id="tambah-deskripsi" rows="4" placeholder="Ketik ringkasan materi atau RPP pertemuan ini"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">File Materi</label>
                            <input type="file" class="form-control" name="file" id="tambah-file">
                            <div class="form-text">Semua format, maks 20MB</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">URL Eksternal</label>
                            <input type="url" class="form-control" name="url_eksternal" placeholder="https://drive.google.com/...">
                        </div>
                    </div>
                    <div id="tambah-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-tambah">
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
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Materi Belajar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="edit-lembaga" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="edit-guru" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mata_pelajaran_id" id="edit-mapel" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rombel</label>
                            <select class="form-select" name="rombel_id" id="edit-rombel">
                                <option value="">-- Semua Rombel --</option>
                                @foreach($rombelList as $r)
                                    <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                        Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="edit-tahun" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Pertemuan Ke</label>
                            <input type="number" class="form-control" name="pertemuan_ke" id="edit-pertemuan" min="1" max="100" placeholder="Contoh: 1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" id="edit-tanggal" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="pending">Menunggu Verifikasi</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul" id="edit-judul" maxlength="255" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi / RPP Terstruktur</label>
                            <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">File Baru</label>
                            <input type="file" class="form-control" name="file">
                            <div class="form-text">Biarkan kosong jika tidak diganti</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">URL Eksternal</label>
                            <input type="url" class="form-control" name="url_eksternal" id="edit-url" placeholder="https://...">
                        </div>
                    </div>
                    <div id="edit-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn-edit">
                        <i class="bi bi-check-lg me-1"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
$(document).ready(function () {
    var _successMsg = null;
    let tambahEditor;
    let editEditor;

    // Initialize CKEditor 5 for Tambah
    ClassicEditor
        .create(document.querySelector('#tambah-deskripsi'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ]
        })
        .then(editor => {
            tambahEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    // Initialize CKEditor 5 for Edit
    ClassicEditor
        .create(document.querySelector('#edit-deskripsi'), {
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ]
        })
        .then(editor => {
            editEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    var table = $('#materi-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.akademik.materi-belajar.list') }}',
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false },
            { data: 'action',       orderable: false, searchable: false },
            { data: 'judul' },
            { data: 'guru_nama' },
            { data: 'mapel_nama' },
            { data: 'rombel_nama' },
            { data: 'tanggal_fmt' },
            { data: 'status_badge', orderable: false },
            { data: 'file_link',    orderable: false, searchable: false },
        ],
        language: {
            sEmptyTable: 'Tidak ada data materi',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst:'Pertama', sPrevious:'Sebelumnya', sNext:'Selanjutnya', sLast:'Terakhir' },
        },
    });

    function filterByLembaga(lembagaId, prefix) {
        ['guru', 'rombel'].forEach(function (key) {
            var $sel = $('#' + prefix + '-' + key);
            var selectedBefore = $sel.val();
            $sel.find('option[data-lembaga]').each(function () {
                var show = !lembagaId || $(this).data('lembaga') == lembagaId;
                $(this).prop('hidden', !show);
                if (!show && $(this).is(':selected')) $sel.val('');
            });
            if ($sel.val() !== selectedBefore) {
                $sel.trigger('change');
            }
        });
    }

    // Load mata pelajaran by guru (dependent dropdown)
    function loadMapelByGuru(guruId, targetSelectId, selectedMapelId = null) {
        var $mapel = $(targetSelectId);
        $mapel.empty().append('<option value="">-- Memuat Mata Pelajaran... --</option>').prop('disabled', true);
        
        if (!guruId) {
            $mapel.empty().append('<option value="">-- Pilih Guru Terlebih Dahulu --</option>').prop('disabled', true);
            return;
        }

        var url = '{{ route("admin.akademik.perangkat-mengajar.guru-mapel", ":guruId") }}'.replace(':guruId', guruId);

        return $.ajax({
            url: url,
            type: 'GET',
            success: function (res) {
                $mapel.empty().append('<option value="">-- Pilih Mata Pelajaran --</option>').prop('disabled', false);
                if (res.status === 200) {
                    var data = res.data;
                    if (data.length === 0) {
                        $mapel.empty().append('<option value="">-- Guru tidak memiliki jadwal mengajar --</option>').prop('disabled', true);
                        return;
                    }
                    $.each(data, function (i, item) {
                        var selected = (selectedMapelId && item.id == selectedMapelId) ? 'selected' : '';
                        $mapel.append('<option value="' + item.id + '" ' + selected + '>' + item.nama + '</option>');
                    });
                } else {
                    $mapel.empty().append('<option value="">-- Gagal memuat data --</option>');
                }
            },
            error: function () {
                $mapel.empty().append('<option value="">-- Gagal memuat data --</option>').prop('disabled', true);
            }
        });
    }

    $('#tambah-lembaga').on('change', function () {
        filterByLembaga($(this).val(), 'tambah');
    }).trigger('change');

    $('#tambah-guru').on('change', function () {
        loadMapelByGuru($(this).val(), '#tambah-mapel');
    });

    // ── Tambah ─────────────────────────────────────────────────────────────
    $('#modal-tambah').on('hidden.bs.modal', function () {
        if (_successMsg) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: _successMsg, timer: 2500, showConfirmButton: false });
            _successMsg = null;
        }
    });

    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        
        // Sync CKEditor data to textarea
        if (tambahEditor) {
            $('#tambah-deskripsi').val(tambahEditor.getData());
        }

        var $btn = $('#btn-tambah').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.status === 200) {
                    _successMsg = res.message || 'Materi berhasil ditambahkan.';
                    bootstrap.Modal.getInstance(document.getElementById('modal-tambah')).hide();
                    document.getElementById('form-tambah').reset();
                    if (tambahEditor) {
                        tambahEditor.setData('');
                    }
                    table.ajax.reload();
                } else {
                    showAlert('#tambah-alert', 'danger', res.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                showAlert('#tambah-alert', 'danger', msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan');
            }
        });
    });

    // ── Edit ───────────────────────────────────────────────────────────────
    var _editSuccessMsg = null;
    $('#modal-edit').on('hidden.bs.modal', function () {
        if (_editSuccessMsg) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: _editSuccessMsg, timer: 2500, showConfirmButton: false });
            _editSuccessMsg = null;
        }
    });

    window.editMateri = function (id) {
        $.get('{{ url('admin/akademik/materi-belajar') }}/' + id, function (res) {
            if (res.status === 200) {
                var d = res.data;
                $('#form-edit').attr('action', '{{ url('admin/akademik/materi-belajar') }}/' + id);
                $('#edit-lembaga').val(d.lembaga_id);
                filterByLembaga(d.lembaga_id, 'edit');
                setTimeout(function () {
                    $('#edit-guru').val(d.guru_id);
                    loadMapelByGuru(d.guru_id, '#edit-mapel', d.mata_pelajaran_id);
                    $('#edit-rombel').val(d.rombel_id);
                }, 50);
                $('#edit-tahun').val(d.tahun_pelajaran_id);
                $('#edit-tanggal').val(d.tanggal ? d.tanggal.substring(0, 10) : '');
                $('#edit-status').val(d.status);
                $('#edit-judul').val(d.judul);
                $('#edit-pertemuan').val(d.pertemuan_ke);
                $('#edit-url').val(d.url_eksternal);

                // Populate CKEditor
                if (editEditor) {
                    editEditor.setData(d.deskripsi ?? '');
                } else {
                    $('#edit-deskripsi').val(d.deskripsi);
                }

                $('#edit-alert').addClass('d-none');
                $('#modal-edit').modal('show');
            }
        });
    };

    $('#edit-lembaga').on('change', function () {
        filterByLembaga($(this).val(), 'edit');
    });

    $('#edit-guru').on('change', function () {
        loadMapelByGuru($(this).val(), '#edit-mapel');
    });

    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        
        // Sync CKEditor data to textarea
        if (editEditor) {
            $('#edit-deskripsi').val(editEditor.getData());
        }

        var $btn = $('#btn-edit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
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
                    _editSuccessMsg = res.message || 'Materi berhasil diperbarui.';
                    bootstrap.Modal.getInstance(document.getElementById('modal-edit')).hide();
                    table.ajax.reload();
                } else {
                    showAlert('#edit-alert', 'danger', res.message);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                showAlert('#edit-alert', 'danger', msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Perbarui');
            }
        });
    });

    // ── Hapus ──────────────────────────────────────────────────────────────
    window.hapusMateri = function (id, judul) {
        Swal.fire({
            title: 'Hapus Materi?',
            text: '"' + judul + '" akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/akademik/materi-belajar') }}/' + id,
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

    function showAlert(sel, type, msg) {
        $(sel).removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).html(msg);
    }
});
</script>
@endpush
