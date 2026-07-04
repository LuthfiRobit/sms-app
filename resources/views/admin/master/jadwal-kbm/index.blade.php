@extends('admin.layouts.app')
@section('title', 'Jadwal KBM')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-calendar3 me-2"></i>Jadwal KBM</h5>
                        <small class="text-muted">Kelola jadwal kegiatan belajar mengajar per rombel.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.jadwal-kbm.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Jadwal
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- Filter --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-tahun">
                            <option value="">-- Semua Tahun Pelajaran --</option>
                            @foreach($tahunList as $t)
                                <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                                    {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-rombel">
                            <option value="">-- Semua Rombel --</option>
                            @foreach($rombelList as $r)
                                <option value="{{ $r->id }}">Kelas {{ $r->tingkat }} - {{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filter-guru">
                            <option value="">-- Semua Guru --</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g['id'] }}">{{ $g['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-sm btn-secondary w-100" id="btn-filter">
                            <i class="bi bi-funnel me-1"></i>Filter — Lihat Mapel yang Diampu
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="jadwal-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">Aksi</th>
                                <th>Rombel</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th width="9%">Hari</th>
                                <th width="13%">Jam</th>
                                <th width="7%">Jam Ke</th>
                                <th width="9%">Ruangan</th>
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
            <form id="form-tambah" action="{{ route('admin.master.jadwal-kbm.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Jadwal KBM</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="tambah-lembaga" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" data-jenis="{{ $l->jenis }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="tambah-tahun" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                                        {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rombel <span class="text-danger">*</span></label>
                            <select class="form-select" name="rombel_id" id="tambah-rombel" required>
                                <option value="">-- Pilih Rombel --</option>
                                @foreach($rombelList as $r)
                                    <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                        Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="tambah-guru" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g['id'] }}" data-lembaga="{{ $g['lembaga_id'] }}">
                                        {{ $g['nama'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mata_pelajaran_id" id="tambah-mapel" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">
                                        [{{ $m->kode }}] {{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hari <span class="text-danger">*</span></label>
                            <select class="form-select" name="hari" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                                    <option value="{{ $h }}">{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_mulai" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_selesai" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Jam Ke</label>
                            <input type="number" class="form-control" name="jam_ke" min="1" max="12" placeholder="1-12">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ruangan</label>
                            <input type="text" class="form-control" name="ruangan" maxlength="50" placeholder="cth: Lab Komputer">
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
            <form id="form-edit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Jadwal KBM</h5>
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
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="edit-tahun" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rombel <span class="text-danger">*</span></label>
                            <select class="form-select" name="rombel_id" id="edit-rombel" required>
                                <option value="">-- Pilih Rombel --</option>
                                @foreach($rombelList as $r)
                                    <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                        Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="edit-guru" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g['id'] }}" data-lembaga="{{ $g['lembaga_id'] }}">
                                        {{ $g['nama'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mata_pelajaran_id" id="edit-mapel" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">
                                        [{{ $m->kode }}] {{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hari <span class="text-danger">*</span></label>
                            <select class="form-select" name="hari" id="edit-hari" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                                    <option value="{{ $h }}">{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_mulai" id="edit-jam-mulai" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="jam_selesai" id="edit-jam-selesai" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Jam Ke</label>
                            <input type="number" class="form-control" name="jam_ke" id="edit-jam-ke" min="1" max="12">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ruangan</label>
                            <input type="text" class="form-control" name="ruangan" id="edit-ruangan" maxlength="50">
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
<script>
$(document).ready(function () {

    var _successMsg = null;

    var table = $('#jadwal-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.master.jadwal-kbm.list') }}',
            data: function (d) {
                d.rombel_id = $('#filter-rombel').val();
                d.tahun_id  = $('#filter-tahun').val();
                d.guru_id   = $('#filter-guru').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false },
            { data: 'action',       orderable: false, searchable: false },
            { data: 'rombel_nama' },
            { data: 'guru_nama' },
            { data: 'mapel_nama' },
            { data: 'hari' },
            { data: 'jam' },
            { data: 'jam_ke',       defaultContent: '—' },
            { data: 'ruangan',      defaultContent: '—' },
        ],
        language: {
            sEmptyTable: 'Tidak ada data jadwal',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst:'Pertama', sPrevious:'Sebelumnya', sNext:'Selanjutnya', sLast:'Terakhir' },
        },
    });

    $('#btn-filter').on('click', function () {
        table.ajax.reload();
    });

    // ── Filter by lembaga ───────────────────────────────────────────────────
    function filterByLembaga(lembagaId, prefix) {
        ['rombel', 'guru', 'mapel'].forEach(function (key) {
            var $sel = $('#' + prefix + '-' + key);
            var currentVal = $sel.val();
            $sel.find('option[data-lembaga]').each(function () {
                var $opt = $(this);
                var show = !lembagaId || $opt.data('lembaga') == lembagaId;
                $opt.prop('hidden', !show);
                if (!show && $opt.is(':selected')) {
                    $sel.val('');
                }
            });
        });
    }

    $('#tambah-lembaga').on('change', function () {
        filterByLembaga($(this).val(), 'tambah');
    }).trigger('change');

    $('#edit-lembaga').on('change', function () {
        filterByLembaga($(this).val(), 'edit');
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
        var $btn = $('#btn-tambah').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 200) {
                    _successMsg = res.data || 'Jadwal berhasil disimpan.';
                    bootstrap.Modal.getInstance(document.getElementById('modal-tambah')).hide();
                    document.getElementById('form-tambah').reset();
                    $('#tambah-lembaga').trigger('change');
                    table.ajax.reload();
                } else {
                    showAlert('#tambah-alert', 'danger', res.data || res.message);
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

    window.editJadwal = function (id) {
        $.get('{{ url('admin/master/jadwal-kbm') }}/' + id, function (res) {
            if (res.status === 200) {
                var d = res.data;
                $('#form-edit').attr('action', '{{ url('admin/master/jadwal-kbm') }}/' + id);
                $('#edit-lembaga').val(d.lembaga_id).trigger('change');
                setTimeout(function () {
                    $('#edit-rombel').val(d.rombel_id);
                    $('#edit-guru').val(d.guru_id);
                    $('#edit-mapel').val(d.mata_pelajaran_id);
                }, 50);
                $('#edit-tahun').val(d.tahun_pelajaran_id);
                $('#edit-hari').val(d.hari);
                $('#edit-jam-mulai').val(d.jam_mulai ? d.jam_mulai.substring(0, 5) : '');
                $('#edit-jam-selesai').val(d.jam_selesai ? d.jam_selesai.substring(0, 5) : '');
                $('#edit-jam-ke').val(d.jam_ke);
                $('#edit-ruangan').val(d.ruangan);
                $('#edit-alert').addClass('d-none');
                $('#modal-edit').modal('show');
            }
        });
    };

    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn-edit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 200) {
                    _editSuccessMsg = res.data || 'Jadwal berhasil diperbarui.';
                    bootstrap.Modal.getInstance(document.getElementById('modal-edit')).hide();
                    table.ajax.reload();
                } else {
                    showAlert('#edit-alert', 'danger', res.data || res.message);
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
    window.hapusJadwal = function (id) {
        Swal.fire({
            title: 'Hapus Jadwal?',
            text: 'Data jadwal ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/master/jadwal-kbm') }}/' + id,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: res.status === 200 ? 'success' : 'error', text: res.data || res.message, timer: 2000, showConfirmButton: false });
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
