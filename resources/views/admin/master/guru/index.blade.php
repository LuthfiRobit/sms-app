@extends('admin.layouts.app')
@section('title', 'Manajemen Data Guru')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-person-badge me-2"></i>Data Guru</h5>
                        <small class="text-muted">Kelola data guru dan tenaga pengajar per lembaga.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.guru.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Guru
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="guru-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Aksi</th>
                                <th width="15%">Lembaga</th>
                                <th>Nama Lengkap</th>
                                <th width="12%">NIP</th>
                                <th width="14%">NUPTK</th>
                                <th width="8%">L/P</th>
                                <th width="10%">Status</th>
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
            <form id="form-tambah" action="{{ route('admin.master.guru.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Guru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Progress bar simpan --}}
                <div id="tambah-progress-wrap" style="height:4px;background:#e9ecef;display:none;overflow:hidden;">
                    <div id="tambah-progress-bar"
                         style="height:100%;width:0%;background:var(--bs-primary);transition:width .4s ease;"></div>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}"
                                        @if(app('active_lembaga_id') == $l->id) selected @endif>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Gelar Depan</label>
                            <input type="text" class="form-control form-control-sm" name="gelar_depan"
                                   placeholder="Cth: Dr." maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama"
                                   placeholder="Nama tanpa gelar" maxlength="255" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Gelar Belakang</label>
                            <input type="text" class="form-control form-control-sm" name="gelar_belakang"
                                   placeholder="Cth: M.Pd." maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIP</label>
                            <input type="text" class="form-control" name="nip"
                                   placeholder="Nomor Induk Pegawai" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NUPTK</label>
                            <input type="text" class="form-control" name="nuptk"
                                   placeholder="16 digit NUPTK" maxlength="16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="tambah-btn-batal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="tambah-btn-simpan">
                        <i class="bi bi-save me-1"></i>Simpan
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
                @csrf @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="edit-lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Gelar Depan</label>
                            <input type="text" class="form-control form-control-sm" name="gelar_depan"
                                   id="edit-gelar_depan" maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama"
                                   id="edit-nama" maxlength="255" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Gelar Belakang</label>
                            <input type="text" class="form-control form-control-sm" name="gelar_belakang"
                                   id="edit-gelar_belakang" maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIP</label>
                            <input type="text" class="form-control" name="nip"
                                   id="edit-nip" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NUPTK</label>
                            <input type="text" class="form-control" name="nuptk"
                                   id="edit-nuptk" maxlength="16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin" id="edit-jenis_kelamin">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#guru-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.master.guru.list') }}',
        columns: [
            { data: 'DT_RowIndex',       orderable: false, searchable: false },
            { data: 'action',            orderable: false, searchable: false },
            { data: 'lembaga_nama' },
            { data: 'nama_lengkap' },
            { data: 'nip' },
            { data: 'nuptk' },
            { data: 'jenis_kelamin_label', orderable: false },
            { data: 'status_badge',        orderable: false },
        ],
        order: [[3, 'asc']],
        language: { url: '/assets/datatables-id.json' },
    });

    // ── Helpers progress bar ──────────────────────────────────
    const $progressWrap = $('#tambah-progress-wrap');
    const $progressBar  = $('#tambah-progress-bar');
    const $btnSimpan    = $('#tambah-btn-simpan');
    const $btnBatal     = $('#tambah-btn-batal');
    let progressTimer   = null;
    let _successMsg     = null;

    function resetProgress() {
        clearInterval(progressTimer);
        $progressBar.css({ width: '0%', background: 'var(--bs-primary)' });
        $progressWrap.hide();
        $btnSimpan.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
        $btnBatal.prop('disabled', false);
    }

    function startProgress() {
        _successMsg = null;
        $progressWrap.show();
        $progressBar.css('width', '0%');
        $btnSimpan.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
        $btnBatal.prop('disabled', true);

        let pct = 0;
        progressTimer = setInterval(function () {
            pct = Math.min(pct + (Math.random() * 12 + 4), 80);
            $progressBar.css('width', pct + '%');
            if (pct >= 80) clearInterval(progressTimer);
        }, 150);
    }

    function finishProgress(success, message) {
        clearInterval(progressTimer);
        if (success) {
            _successMsg = message || 'Data berhasil disimpan.';
            $progressBar.css({ width: '100%', background: '#198754' });
            setTimeout(function () {
                table.ajax.reload();
                const modalEl = document.getElementById('modal-tambah');
                const inst = bootstrap.Modal.getInstance(modalEl);
                if (inst) {
                    inst.hide();
                } else {
                    new bootstrap.Modal(modalEl).hide();
                }
            }, 450);
        } else {
            resetProgress();
        }
    }

    $('#modal-tambah').on('hidden.bs.modal', function () {
        const msg = _successMsg;
        resetProgress();
        $('#form-tambah')[0].reset();
        if (msg) {
            _successMsg = null;
            Swal.fire({ icon: 'success', title: msg, timer: 2000, showConfirmButton: false });
        }
    });

    // ── Tambah ───────────────────────────────────────────────
    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        startProgress();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 200) {
                    finishProgress(true, res.data);
                } else {
                    finishProgress(false);
                    Swal.fire({ icon: 'warning', title: res.message || 'Gagal menyimpan.' });
                }
            },
            error: function (xhr) {
                finishProgress(false);
                const errors = xhr.responseJSON?.errors;
                const msg = errors ? Object.values(errors).flat().join('<br>') : 'Terjadi kesalahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });

    // ── Edit ─────────────────────────────────────────────────
    window.editGuru = function (id) {
        $.get(`{{ url('admin/master/guru') }}/${id}`, function (res) {
            const d = res.data;
            $('#form-edit').attr('action', `{{ url('admin/master/guru') }}/${id}`);
            $('#edit-lembaga_id').val(d.lembaga_id ?? '');
            $('#edit-gelar_depan').val(d.gelar_depan ?? '');
            $('#edit-nama').val(d.nama);
            $('#edit-gelar_belakang').val(d.gelar_belakang ?? '');
            $('#edit-nip').val(d.nip ?? '');
            $('#edit-nuptk').val(d.nuptk ?? '');
            $('#edit-jenis_kelamin').val(d.jenis_kelamin ?? '');
            $('#edit-status').val(d.status);
            $('#modal-edit').modal('show');
        });
    };

    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 200) {
                    $('#modal-edit').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                const msg = errors ? Object.values(errors).flat().join('<br>') : 'Terjadi kesalahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });

    // ── Hapus ─────────────────────────────────────────────────
    window.hapusGuru = function (id, nama) {
        Swal.fire({
            title: 'Hapus Guru?',
            html: `Data guru <strong>${nama}</strong> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `{{ url('admin/master/guru') }}/${id}`,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.' });
                }
            });
        });
    };
});
</script>
@endpush
