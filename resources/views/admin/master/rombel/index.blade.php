@extends('admin.layouts.app')
@section('title', 'Manajemen Rombel / Kelas')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-people me-2"></i>Rombel / Kelas</h5>
                        <small class="text-muted">Kelola rombongan belajar per lembaga dan tahun pelajaran.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.master.rombel.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Rombel
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="rombel-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Aksi</th>
                                <th width="18%">Lembaga</th>
                                <th width="14%">Tahun Pelajaran</th>
                                <th width="7%">Tingkat</th>
                                <th width="12%">Nama Rombel</th>
                                <th width="12%">Jurusan</th>
                                <th>Wali Kelas</th>
                                <th width="8%">Kapasitas</th>
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
            <form id="form-tambah" action="{{ route('admin.master.rombel.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Rombel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- Progress bar --}}
                <div id="tambah-progress-wrap" style="height:4px;background:#e9ecef;display:none;overflow:hidden;">
                    <div id="tambah-progress-bar" style="height:100%;width:0%;background:var(--bs-primary);transition:width .4s ease;"></div>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Lembaga --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="tambah-lembaga" required>
                                <option value="">— Pilih Lembaga —</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}"
                                        data-jenis="{{ $l->jenis }}"
                                        @if(app('active_lembaga_id') == $l->id) selected @endif>
                                        [{{ $l->kode }}] {{ $l->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tahun Pelajaran --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="tambah-tahun" required>
                                <option value="">— Pilih Tahun Pelajaran —</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}" @if($t->id == $tahunAktifId) selected @endif>
                                        {{ $t->nama }}
                                        @if($t->status === 'aktif') (Aktif) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tingkat —— opsi diisi JS sesuai jenis lembaga --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" name="tingkat" id="tambah-tingkat" required disabled>
                                <option value="">— Pilih lembaga dulu —</option>
                            </select>
                            <div class="form-text" id="tambah-tingkat-hint"></div>
                        </div>

                        {{-- Nama Rombel --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Rombel <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="tambah-nama"
                                   placeholder="Cth: VII-A, X IPA 1" maxlength="50" required>
                        </div>

                        {{-- Jurusan —— opsi difilter JS per lembaga --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jurusan / Program</label>
                            <select class="form-select" name="jurusan_id" id="tambah-jurusan">
                                <option value="">— Tidak Ada / Umum —</option>
                            </select>
                            <div class="form-text text-muted" id="tambah-jurusan-hint">Pilih lembaga untuk memuat daftar jurusan.</div>
                        </div>

                        {{-- Wali Kelas --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Wali Kelas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control" name="wali_kelas" id="tambah-wali"
                                       list="tambah-guru-list" placeholder="Ketik nama atau pilih dari daftar guru…"
                                       maxlength="255" autocomplete="off">
                            </div>
                            <datalist id="tambah-guru-list"></datalist>
                            <div class="form-text text-muted" id="tambah-wali-hint">Pilih lembaga untuk memuat daftar guru.</div>
                        </div>

                        {{-- Kapasitas --}}
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Kapasitas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="kapasitas" id="tambah-kapasitas"
                                   value="32" min="1" max="50" required>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-3">
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
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Edit Rombel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="edit-lembaga" required>
                                <option value="">— Pilih Lembaga —</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" data-jenis="{{ $l->jenis }}">[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" id="edit-tahun" required>
                                <option value="">— Pilih —</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}">{{ $t->nama }}@if($t->status === 'aktif') (Aktif)@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" name="tingkat" id="edit-tingkat" required>
                                <option value="">— Pilih —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Rombel <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="edit-nama" maxlength="50" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jurusan / Program</label>
                            <select class="form-select" name="jurusan_id" id="edit-jurusan">
                                <option value="">— Tidak Ada / Umum —</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Wali Kelas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control" name="wali_kelas" id="edit-wali"
                                       list="edit-guru-list" placeholder="Ketik nama atau pilih dari daftar guru…"
                                       maxlength="255" autocomplete="off">
                            </div>
                            <datalist id="edit-guru-list"></datalist>
                            <div class="form-text text-muted" id="edit-wali-hint"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Kapasitas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="kapasitas" id="edit-kapasitas" min="1" max="50" required>
                        </div>
                        <div class="col-md-3">
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
    // ── Data dari PHP ──────────────────────────────────────────
    const LEMBAGA_DATA = @json($lembagaList->keyBy('id'));
    const ALL_JURUSAN  = @json($allJurusan);

    // Map jenis lembaga → range tingkat yang valid
    const TINGKAT_MAP = {
        'MI':  [1,2,3,4,5,6],
        'MTs': [7,8,9],
        'SMP': [7,8,9],
        'MA':  [10,11,12],
        'SMA': [10,11,12],
        'SMK': [10,11,12],
    };
    const TINGKAT_LABEL = { MI:'Kelas 1–6', MTs:'Kelas 7–9', SMP:'Kelas 7–9', MA:'Kelas 10–12', SMA:'Kelas 10–12', SMK:'Kelas 10–12' };
    const ROMAWI = { 1:'I',2:'II',3:'III',4:'IV',5:'V',6:'VI',7:'VII',8:'VIII',9:'IX',10:'X',11:'XI',12:'XII' };

    // ── Helper: isi dropdown tingkat ─────────────────────────
    function buildTingkat($sel, jenis, currentVal) {
        const list = TINGKAT_MAP[jenis] ?? [];
        $sel.empty().prop('disabled', list.length === 0);
        if (!list.length) {
            $sel.append('<option value="">— Pilih lembaga dulu —</option>');
            return;
        }
        $sel.append('<option value="">— Pilih Tingkat —</option>');
        list.forEach(function (k) {
            const label = (k <= 6) ? `Kelas ${k}` : `Kelas ${k} (${ROMAWI[k]})`;
            $sel.append(`<option value="${k}" ${String(k) === String(currentVal) ? 'selected' : ''}>${label}</option>`);
        });
    }

    // ── Helper: isi dropdown jurusan per lembaga ──────────────
    function buildJurusan($sel, lembagaId, currentVal, $hint) {
        const filtered = ALL_JURUSAN.filter(j => String(j.lembaga_id) === String(lembagaId));
        $sel.empty().append('<option value="">— Tidak Ada / Umum —</option>');
        if (!filtered.length) {
            if ($hint) $hint.text('Tidak ada jurusan untuk lembaga ini.');
            return;
        }
        filtered.forEach(function (j) {
            $sel.append(`<option value="${j.id}" ${String(j.id) === String(currentVal) ? 'selected' : ''}>[${j.kode}] ${j.nama}</option>`);
        });
        if ($hint) $hint.text(`${filtered.length} jurusan tersedia.`);
    }

    // ── Helper: isi datalist guru via AJAX ────────────────────
    const _guruCache = {};   // cache per lembagaId agar tidak re-fetch

    function buildGuru(lembagaId, datalistId, $hint, currentVal) {
        const $dl = $(`#${datalistId}`);
        if (!lembagaId) {
            $dl.empty();
            if ($hint) $hint.text('Pilih lembaga untuk memuat daftar guru.');
            return;
        }
        if (_guruCache[lembagaId]) {
            _fillGuruDatalist($dl, _guruCache[lembagaId], $hint);
            return;
        }
        if ($hint) $hint.html('<span class="text-muted"><span class="spinner-border spinner-border-sm"></span> Memuat daftar guru…</span>');
        $.getJSON(`{{ url('admin/master/rombel/guru') }}/${lembagaId}`, function (data) {
            _guruCache[lembagaId] = data;
            _fillGuruDatalist($dl, data, $hint);
        }).fail(function () {
            if ($hint) $hint.text('Gagal memuat daftar guru.');
        });
    }

    function _fillGuruDatalist($dl, data, $hint) {
        $dl.empty();
        data.forEach(function (g) {
            const label = g.nip ? `${g.nama} (${g.nip})` : g.nama;
            $dl.append(`<option value="${g.nama}" label="${label}">`);
        });
        if ($hint) {
            $hint.text(data.length
                ? `${data.length} guru tersedia — ketik untuk mencari atau isi manual.`
                : 'Belum ada data guru untuk lembaga ini. Isi manual.');
        }
    }

    // ── DataTable ────────────────────────────────────────────
    const table = $('#rombel-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin.master.rombel.list') }}',
        columns: [
            { data: 'DT_RowIndex',          orderable: false, searchable: false },
            { data: 'action',               orderable: false, searchable: false },
            { data: 'lembaga_nama' },
            { data: 'tahun_pelajaran_nama' },
            { data: 'tingkat' },
            { data: 'nama' },
            { data: 'jurusan_nama',         orderable: false },
            { data: 'wali_kelas' },
            { data: 'kapasitas' },
            { data: 'status_badge',         orderable: false },
        ],
        order: [[4, 'asc'], [5, 'asc']],
        language: { url: '/assets/datatables-id.json' },
    });

    // ── Inisialisasi modal Tambah saat dibuka ─────────────────
    $('#modal-tambah').on('show.bs.modal', function () {
        const $lembaga  = $('#tambah-lembaga');
        const jenis     = $lembaga.find(':selected').data('jenis') ?? '';
        const lembagaId = $lembaga.val();
        buildTingkat($('#tambah-tingkat'), jenis, '');
        buildJurusan($('#tambah-jurusan'), lembagaId, '', $('#tambah-jurusan-hint'));
        buildGuru(lembagaId, 'tambah-guru-list', $('#tambah-wali-hint'));
    });

    // ── Reactive: lembaga tambah berubah ─────────────────────
    $('#tambah-lembaga').on('change', function () {
        const jenis     = $(this).find(':selected').data('jenis') ?? '';
        const lembagaId = $(this).val();
        buildTingkat($('#tambah-tingkat'), jenis, '');
        buildJurusan($('#tambah-jurusan'), lembagaId, '', $('#tambah-jurusan-hint'));
        buildGuru(lembagaId, 'tambah-guru-list', $('#tambah-wali-hint'));

        const hint = TINGKAT_LABEL[jenis];
        $('#tambah-tingkat-hint').text(hint ? `Rentang tingkat untuk ${jenis}: ${hint}.` : '');
        $('#tambah-wali').val('');
        $('#tambah-nama').val('').attr('placeholder', jenis ? `Cth: ${jenis === 'SMK' ? 'X TKJ 1' : (jenis === 'MI' ? 'III A' : 'VIII-B')}` : 'Cth: VII-A, X IPA 1');
    });

    // ── Reactive: lembaga edit berubah ───────────────────────
    $('#edit-lembaga').on('change', function () {
        const jenis     = $(this).find(':selected').data('jenis') ?? '';
        const lembagaId = $(this).val();
        buildTingkat($('#edit-tingkat'), jenis, '');
        buildJurusan($('#edit-jurusan'), lembagaId, '');
        buildGuru(lembagaId, 'edit-guru-list', $('#edit-wali-hint'));
        $('#edit-wali').val('');
    });

    // ── Progress bar (Modal Tambah) ───────────────────────────
    const $pWrap    = $('#tambah-progress-wrap');
    const $pBar     = $('#tambah-progress-bar');
    const $btnSave  = $('#tambah-btn-simpan');
    const $btnBatal = $('#tambah-btn-batal');
    let pTimer      = null;
    let _sukses     = null;

    function resetProgress() {
        clearInterval(pTimer);
        $pBar.css({ width: '0%', background: 'var(--bs-primary)' });
        $pWrap.hide();
        $btnSave.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan');
        $btnBatal.prop('disabled', false);
    }

    function startProgress() {
        _sukses = null;
        $pWrap.show();
        $pBar.css('width', '0%');
        $btnSave.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
        $btnBatal.prop('disabled', true);
        let pct = 0;
        pTimer = setInterval(function () {
            pct = Math.min(pct + (Math.random() * 12 + 4), 80);
            $pBar.css('width', pct + '%');
            if (pct >= 80) clearInterval(pTimer);
        }, 150);
    }

    function finishProgress(success, message) {
        clearInterval(pTimer);
        if (success) {
            _sukses = message;
            $pBar.css({ width: '100%', background: '#198754' });
            setTimeout(function () {
                table.ajax.reload();
                const inst = bootstrap.Modal.getInstance(document.getElementById('modal-tambah'));
                if (inst) inst.hide(); else new bootstrap.Modal(document.getElementById('modal-tambah')).hide();
            }, 450);
        } else {
            resetProgress();
        }
    }

    $('#modal-tambah').on('hidden.bs.modal', function () {
        const msg = _sukses;
        resetProgress();
        $('#form-tambah')[0].reset();
        // Rebuild dropdown kembali ke kondisi awal
        const jenis     = $('#tambah-lembaga').find(':selected').data('jenis') ?? '';
        const lembagaId = $('#tambah-lembaga').val();
        buildTingkat($('#tambah-tingkat'), jenis, '');
        buildJurusan($('#tambah-jurusan'), lembagaId, '', $('#tambah-jurusan-hint'));
        if (msg) {
            _sukses = null;
            Swal.fire({ icon: 'success', title: msg, timer: 2000, showConfirmButton: false });
        }
    });

    // ── Submit Tambah ─────────────────────────────────────────
    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        startProgress();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'success' || res.status === 200) {
                    finishProgress(true, res.data || 'Rombel berhasil ditambahkan.');
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

    // ── Submit Edit ───────────────────────────────────────────
    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'success' || res.status === 200) {
                    $('#modal-edit').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: res.data || 'Berhasil diperbarui.', timer: 2000, showConfirmButton: false });
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                const msg = errors ? Object.values(errors).flat().join('<br>') : 'Terjadi kesalahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });

    // ── Edit: populate form ───────────────────────────────────
    window.editRombel = function (id) {
        $.get(`{{ url('admin/master/rombel') }}/${id}`, function (res) {
            const d = res.data;
            const jenis = LEMBAGA_DATA[d.lembaga_id]?.jenis ?? '';
            $('#form-edit').attr('action', `{{ url('admin/master/rombel') }}/${id}`);
            $('#edit-lembaga').val(d.lembaga_id);
            $('#edit-tahun').val(d.tahun_pelajaran_id);
            buildTingkat($('#edit-tingkat'), jenis, d.tingkat);
            buildJurusan($('#edit-jurusan'), d.lembaga_id, d.jurusan_id);
            // Load guru datalist dulu, lalu set nilai wali kelas setelah selesai
            buildGuru(d.lembaga_id, 'edit-guru-list', $('#edit-wali-hint'), d.wali_kelas);
            $('#edit-wali').val(d.wali_kelas ?? '');
            $('#edit-kapasitas').val(d.kapasitas);
            $('#edit-status').val(d.status);
            $('#modal-edit').modal('show');
        });
    };

    // ── Hapus ─────────────────────────────────────────────────
    window.hapusRombel = function (id, nama) {
        Swal.fire({
            title: 'Hapus Rombel?',
            html: `Rombel <strong>${nama}</strong> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `{{ url('admin/master/rombel') }}/${id}`,
                method: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) {
                    table.ajax.reload();
                    Swal.fire({ icon: 'success', title: res.data || res.message, timer: 2000, showConfirmButton: false });
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
