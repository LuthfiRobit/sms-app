@extends('admin.layouts.app')
@section('title', 'Absensi Siswa')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-clipboard-check me-2"></i>Absensi Siswa</h5>
                        <small class="text-muted">Rekap & input kehadiran siswa per kelas/rombel.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.absensi.store'))
                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Input Absensi
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- Filter --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="filter-rombel">
                            <option value="">-- Semua Rombel --</option>
                            @foreach($rombelList as $r)
                                <option value="{{ $r->id }}">Kelas {{ $r->tingkat }} - {{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-secondary w-100" id="btn-filter">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="absensi-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th width="10%">Aksi</th>
                                <th>Rombel</th>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th width="9%">Tanggal</th>
                                <th width="6%">Jam Ke</th>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-tambah" action="{{ route('admin.akademik.absensi.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-clipboard-plus me-1"></i>Input Absensi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        @if($guruAktif)
                            {{-- Login sebagai guru: Lembaga & Guru otomatis dari identitas akun. --}}
                            <input type="hidden" name="lembaga_id" id="tambah-lembaga" value="{{ $guruAktif->lembaga_id }}">
                            <input type="hidden" name="guru_id" id="tambah-guru" value="{{ $guruAktif->id }}">
                            <div class="col-md-12">
                                <div class="alert alert-light border py-2 mb-0">
                                    <small class="text-muted d-block">Guru</small><strong>{{ $guruAktif->nama_lengkap }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="col-md-4">
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
                        @endif
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mata Pelajaran</label>
                            <select class="form-select" name="mata_pelajaran_id" id="tambah-mapel">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapelList as $m)
                                    <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(!$guruAktif)
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Guru</label>
                            <select class="form-select" name="guru_id" id="tambah-guru">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Jam Ke</label>
                            <input type="number" class="form-control" name="jam_ke" min="1" max="12">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" maxlength="255" placeholder="Opsional">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><i class="bi bi-people me-1"></i>Daftar Siswa</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-load-siswa">
                            <i class="bi bi-arrow-clockwise me-1"></i>Muat Siswa
                        </button>
                    </div>
                    <div id="siswa-placeholder" class="alert alert-light text-muted text-center py-3">
                        Pilih rombel dan klik "Muat Siswa" untuk menampilkan daftar absensi.
                    </div>
                    <div id="siswa-wrap" class="d-none">
                        <div class="mb-2 d-flex gap-2">
                            <button type="button" class="btn btn-xs btn-outline-success" onclick="setAllStatus('hadir')">Semua Hadir</button>
                            <button type="button" class="btn btn-xs btn-outline-warning" onclick="setAllStatus('sakit')">Semua Sakit</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="tbl-siswa">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="7%">No Absen</th>
                                        <th>Nama Siswa</th>
                                        <th width="14%">Status <span class="text-danger">*</span></th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="body-siswa"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="tambah-alert" class="alert d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-tambah">
                        <i class="bi bi-check-lg me-1"></i>Simpan Absensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Detail ── --}}
<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-eye me-1"></i>Detail Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-body">
                <div class="text-center py-3"><i class="bi bi-hourglass-split"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var _successMsg = null;

    var table = $('#absensi-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.akademik.absensi.list') }}',
            data: function (d) {
                d.rombel_id = $('#filter-rombel').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',  orderable: false, searchable: false },
            { data: 'action',       orderable: false, searchable: false },
            { data: 'rombel_nama' },
            { data: 'guru_nama' },
            { data: 'mapel_nama' },
            { data: 'tanggal_fmt' },
            { data: 'jam_ke', defaultContent: '—' },
        ],
        language: {
            sEmptyTable: 'Tidak ada data absensi',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst:'Pertama', sPrevious:'Sebelumnya', sNext:'Selanjutnya', sLast:'Terakhir' },
        },
    });

    $('#btn-filter').on('click', function () { table.ajax.reload(); });

    // ── Filter by lembaga ───────────────────────────────────────────────────
    function filterByLembaga(lembagaId) {
        ['rombel', 'mapel', 'guru'].forEach(function (key) {
            var $sel = $('#tambah-' + key);
            $sel.find('option[data-lembaga]').each(function () {
                var show = !lembagaId || $(this).data('lembaga') == lembagaId;
                $(this).prop('hidden', !show);
                if (!show && $(this).is(':selected')) $sel.val('');
            });
        });
    }
    $('#tambah-lembaga').on('change', function () {
        filterByLembaga($(this).val());
    }).trigger('change');

    // ── Muat Siswa ─────────────────────────────────────────────────────────
    $('#btn-load-siswa').on('click', function () {
        var rombelId = $('#tambah-rombel').val();
        if (!rombelId) {
            Swal.fire({ icon: 'warning', text: 'Pilih rombel terlebih dahulu.', timer: 2000, showConfirmButton: false });
            return;
        }
        var $btn = $(this).prop('disabled', true);
        $.get('{{ url('admin/akademik/absensi/siswa') }}/' + rombelId, function (res) {
            if (res.status === 200) {
                renderSiswa(res.data);
            }
        }).always(function () { $btn.prop('disabled', false); });
    });

    function renderSiswa(list) {
        var $body = $('#body-siswa').empty();
        if (!list.length) {
            $body.append('<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada siswa di rombel ini. Pastikan siswa sudah di-assign.</td></tr>');
        } else {
            list.forEach(function (s, i) {
                $body.append(
                    '<tr>' +
                    '<td>' + (s.no_absen || (i + 1)) + '<input type="hidden" name="detail[' + i + '][peserta_id]" value="' + s.peserta_id + '"></td>' +
                    '<td>' + $('<span>').text(s.nama).html() + '</td>' +
                    '<td>' +
                    '<select class="form-select form-select-sm status-select" name="detail[' + i + '][status]" required>' +
                    '<option value="hadir" selected>Hadir</option>' +
                    '<option value="sakit">Sakit</option>' +
                    '<option value="izin">Izin</option>' +
                    '<option value="alpa">Alpa</option>' +
                    '</select>' +
                    '</td>' +
                    '<td><input type="text" class="form-control form-control-sm" name="detail[' + i + '][keterangan]" maxlength="255"></td>' +
                    '</tr>'
                );
            });
        }
        $('#siswa-placeholder').addClass('d-none');
        $('#siswa-wrap').removeClass('d-none');
    }

    window.setAllStatus = function (status) {
        $('.status-select').val(status);
    };

    // ── Tambah ─────────────────────────────────────────────────────────────
    $('#modal-tambah').on('hidden.bs.modal', function () {
        if (_successMsg) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: _successMsg, timer: 2500, showConfirmButton: false });
            _successMsg = null;
        }
        $('#body-siswa').empty();
        $('#siswa-placeholder').removeClass('d-none');
        $('#siswa-wrap').addClass('d-none');
    });

    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        if (!$('#body-siswa tr[td]').length && $('#siswa-wrap').hasClass('d-none')) {
            Swal.fire({ icon: 'warning', text: 'Muat daftar siswa terlebih dahulu.', timer: 2000, showConfirmButton: false });
            return;
        }
        var $btn = $('#btn-tambah').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 200) {
                    _successMsg = res.message || 'Absensi berhasil disimpan.';
                    bootstrap.Modal.getInstance(document.getElementById('modal-tambah')).hide();
                    document.getElementById('form-tambah').reset();
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
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan Absensi');
            }
        });
    });

    // ── Detail ─────────────────────────────────────────────────────────────
    window.lihatAbsensi = function (id) {
        $('#detail-body').html('<div class="text-center py-3"><i class="bi bi-hourglass-split"></i> Memuat...</div>');
        $('#modal-detail').modal('show');
        $.get('{{ url('admin/akademik/absensi') }}/' + id + '/detail', function (res) {
            if (res.status === 200) {
                var d = res.data;
                var html = '<dl class="row mb-3">' +
                    '<dt class="col-sm-3">Rombel</dt><dd class="col-sm-9">Kelas ' + (d.rombel?.tingkat || '?') + ' - ' + (d.rombel?.nama || '—') + '</dd>' +
                    '<dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">' + (d.tanggal ? d.tanggal.substring(0, 10) : '—') + '</dd>' +
                    '<dt class="col-sm-3">Mata Pelajaran</dt><dd class="col-sm-9">' + (d.mata_pelajaran?.nama || '—') + '</dd>' +
                    '<dt class="col-sm-3">Guru</dt><dd class="col-sm-9">' + (d.guru ? (d.guru.gelar_depan ? d.guru.gelar_depan + ' ' : '') + d.guru.nama + (d.guru.gelar_belakang ? ', ' + d.guru.gelar_belakang : '') : '—') + '</dd>' +
                    '</dl>';
                var statusBadge = { hadir: 'success', sakit: 'warning', izin: 'info', alpa: 'danger' };
                html += '<table class="table table-sm table-bordered"><thead class="bg-light"><tr><th>No</th><th>Nama Siswa</th><th>Status</th><th>Keterangan</th></tr></thead><tbody>';
                (d.detail || []).forEach(function (row, i) {
                    var color = statusBadge[row.status] || 'secondary';
                    html += '<tr><td>' + (i + 1) + '</td><td>' + $('<span>').text(row.peserta?.nama_lengkap || '?').html() + '</td>' +
                        '<td><span class="badge bg-' + color + '">' + row.status + '</span></td><td>' + (row.keterangan || '—') + '</td></tr>';
                });
                html += '</tbody></table>';
                $('#detail-body').html(html);
            }
        });
    };

    // ── Hapus ──────────────────────────────────────────────────────────────
    window.hapusAbsensi = function (id) {
        Swal.fire({
            title: 'Hapus Absensi?',
            text: 'Data absensi ini dan detail kehadiran siswa akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/akademik/absensi') }}/' + id,
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

    window.editAbsensi = function (id) {
        Swal.fire({ icon: 'info', text: 'Fitur edit absensi: gunakan hapus lalu input ulang untuk akurasi data.', timer: 3000, showConfirmButton: false });
    };

    function showAlert(sel, type, msg) {
        $(sel).removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-' + type).html(msg);
    }
});
</script>
@endpush
