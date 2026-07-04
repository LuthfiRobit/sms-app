@extends('admin.layouts.app')
@section('title', 'Absensi Guru')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="mb-0 text-primary"><i class="bi bi-person-check me-2"></i>Absensi Guru</h5>
                        <small class="text-muted">Rekap absen masuk/pulang guru (GPS + selfie) dari aplikasi mobile.</small>
                    </div>
                    <div class="flex-shrink-0">
                        @if(auth()->user()->hasPermissionTo('admin.akademik.absensi-guru.store'))
                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#modal-tambah">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Manual
                        </button>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.akademik.absensi-guru.export'))
                        <button class="btn btn-sm btn-outline-success me-1" id="btn-export">
                            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                        </button>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.akademik.absensi-guru.rekap-pdf'))
                        <button class="btn btn-sm btn-outline-danger" id="btn-rekap-pdf" title="Pilih lembaga spesifik dulu untuk mengaktifkan (rekap lintas-lembaga tidak punya satu Kepala Sekolah penandatangan)">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Cetak PDF
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="card-body border-bottom bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold">Lembaga</label>
                        <select class="form-select form-select-sm" id="filter-lembaga" {{ $isSuperAdmin ? '' : 'disabled' }}>
                            {{-- "Semua Lembaga" hanya masuk akal untuk super admin — non-super-admin
                                 selalu dipaksa server ke lembaga miliknya, jadi opsi ini akan menyesatkan. --}}
                            @if($isSuperAdmin)
                            <option value="" @selected(!$activeLembagaId)>-- Semua Lembaga --</option>
                            @endif
                            @foreach($lembagaList as $l)
                            <option value="{{ $l->id }}" @selected($activeLembagaId == $l->id)>[{{ $l->kode }}] {{ $l->nama }}</option>
                            @endforeach
                        </select>
                        @unless($isSuperAdmin)
                        <small class="text-muted">Terkunci ke lembaga aktif Anda.</small>
                        @endunless
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold">Guru</label>
                        <select class="form-select form-select-sm" id="filter-guru">
                            <option value="">-- Semua Guru --</option>
                            @foreach($guruList as $g)
                            <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-semibold">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="filter-tanggal-mulai">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-semibold">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="filter-tanggal-akhir">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">-- Semua --</option>
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpa">Alpa</option>
                        </select>
                    </div>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-primary" id="btn-filter">
                        <i class="bi bi-funnel me-1"></i>Terapkan Filter
                    </button>
                    <button class="btn btn-sm btn-light" id="btn-reset-filter">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered w-100" id="absensi-guru-table">
                        <thead class="bg-light">
                            <tr>
                                <th width="4%">No</th>
                                <th>Guru</th>
                                <th>Lembaga</th>
                                <th width="9%">Tanggal</th>
                                <th width="8%">Masuk</th>
                                <th width="8%">Pulang</th>
                                <th width="9%">Status</th>
                                <th width="6%">Flag</th>
                                <th width="8%">Koreksi</th>
                                <th width="8%">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Detail ── --}}
<div class="modal fade" id="modal-detail-guru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-eye me-1"></i>Detail Absensi Guru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-guru-body">
                <div class="text-center py-3"><i class="bi bi-hourglass-split"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah Manual ── --}}
<div class="modal fade" id="modal-tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-tambah">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-lg me-1"></i>Tambah Absensi Manual</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="tambah-lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}" @if(!$isSuperAdmin && $activeLembagaId == $l->id) selected @endif>[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="tambah-guru_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}">{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jam Masuk</label>
                            <input type="time" class="form-control" name="jam_masuk">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jam Pulang</label>
                            <input type="time" class="form-control" name="jam_pulang">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2" maxlength="255"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Edit ── --}}
<div class="modal fade" id="modal-edit-absensi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-edit-absensi">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i>Koreksi Absensi Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3" id="edit-info-guru"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jam Masuk</label>
                            <input type="time" class="form-control" name="jam_masuk" id="edit-jam_masuk">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jam Pulang</label>
                            <input type="time" class="form-control" name="jam_pulang" id="edit-jam_pulang">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="edit-keterangan" rows="2" maxlength="255"></textarea>
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
$(document).ready(function () {
    var table = $('#absensi-guru-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.akademik.absensi-guru.list') }}',
            data: function (d) {
                d.lembaga_id     = $('#filter-lembaga').val();
                d.guru_id        = $('#filter-guru').val();
                d.tanggal_mulai  = $('#filter-tanggal-mulai').val();
                d.tanggal_akhir  = $('#filter-tanggal-akhir').val();
                d.status         = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'guru_nama' },
            { data: 'lembaga_nama' },
            { data: 'tanggal_fmt' },
            { data: 'jam_masuk_fmt' },
            { data: 'jam_pulang_fmt' },
            { data: 'status_badge', orderable: false },
            { data: 'mock_flag', orderable: false, searchable: false },
            { data: 'koreksi_badge', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
        ],
        language: {
            sEmptyTable: 'Tidak ada data absensi guru',
            sLengthMenu: 'Tampilkan _MENU_ entri',
            sZeroRecords: 'Tidak ditemukan data yang sesuai',
            sSearch: 'Cari:',
            oPaginate: { sFirst: 'Pertama', sPrevious: 'Sebelumnya', sNext: 'Selanjutnya', sLast: 'Terakhir' },
        },
    });

    $('#btn-filter').on('click', function () { table.ajax.reload(); });

    $('#btn-reset-filter').on('click', function () {
        $('#filter-lembaga, #filter-guru, #filter-status').val('');
        $('#filter-tanggal-mulai, #filter-tanggal-akhir').val('');
        table.ajax.reload();
    });

    // Filter dropdown Guru mengikuti Lembaga yang dipilih — dijalankan juga saat
    // halaman pertama dimuat, supaya lembaga yang sudah aktif langsung tercermin.
    $('#filter-lembaga').on('change', function () {
        var lembagaId = $(this).val();
        var $guru = $('#filter-guru');
        $guru.find('option[data-lembaga]').each(function () {
            var show = !lembagaId || $(this).data('lembaga') == lembagaId;
            $(this).prop('hidden', !show);
            if (!show && $(this).is(':selected')) $guru.val('');
        });
    }).trigger('change');

    $('#btn-export').on('click', function () {
        var params = $.param({
            lembaga_id: $('#filter-lembaga').val(),
            guru_id: $('#filter-guru').val(),
            tanggal_mulai: $('#filter-tanggal-mulai').val(),
            tanggal_akhir: $('#filter-tanggal-akhir').val(),
            status: $('#filter-status').val(),
        });
        window.location.href = '{{ route('admin.akademik.absensi-guru.export') }}?' + params;
    });

    // Cetak PDF hanya masuk akal untuk 1 lembaga spesifik (butuh 1 Kepala Sekolah
    // penandatangan) — nonaktifkan tombolnya saat masih di mode "Semua Lembaga".
    function refreshTombolRekapPdf() {
        var adaLembaga = !!$('#filter-lembaga').val();
        $('#btn-rekap-pdf').prop('disabled', !adaLembaga);
    }
    $('#filter-lembaga').on('change', refreshTombolRekapPdf);
    refreshTombolRekapPdf();

    $('#btn-rekap-pdf').on('click', function () {
        var params = $.param({
            lembaga_id: $('#filter-lembaga').val(),
            guru_id: $('#filter-guru').val(),
            tanggal_mulai: $('#filter-tanggal-mulai').val(),
            tanggal_akhir: $('#filter-tanggal-akhir').val(),
            status: $('#filter-status').val(),
        });
        window.location.href = '{{ route('admin.akademik.absensi-guru.rekap-pdf') }}?' + params;
    });

    // ── Detail ─────────────────────────────────────────────────────────────
    window.lihatDetailAbsensiGuru = function (id) {
        $('#detail-guru-body').html('<div class="text-center py-3"><i class="bi bi-hourglass-split"></i> Memuat...</div>');
        $('#modal-detail-guru').modal('show');

        $.get('{{ url('admin/akademik/absensi-guru') }}/' + id + '/detail', function (res) {
            if (res.status !== 200) return;
            var d = res.data;

            var statusColor = { hadir: 'success', terlambat: 'warning text-dark', izin: 'info', sakit: 'info', alpa: 'danger' }[d.status] || 'secondary';

            var html = '<dl class="row mb-3">' +
                '<dt class="col-sm-3">Guru</dt><dd class="col-sm-9">' + (d.guru?.nama_lengkap || '—') + '</dd>' +
                '<dt class="col-sm-3">Lembaga</dt><dd class="col-sm-9">' + (d.lembaga?.nama || '—') + '</dd>' +
                '<dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">' + (d.tanggal ? d.tanggal.substring(0, 10) : '—') + '</dd>' +
                '<dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge bg-' + statusColor + '">' + (d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : '—') + '</span>' +
                (d.flag_mock_location ? ' <span class="badge bg-danger ms-1"><i class="bi bi-exclamation-triangle me-1"></i>Lokasi Palsu Terdeteksi</span>' : '') +
                '</dd>' +
                '</dl>';

            html += '<div class="row g-3">';
            html += buildSesiCard('Absen Masuk', d.jam_masuk, d.lat_masuk, d.lng_masuk, d.jarak_masuk_m, d.akurasi_masuk_m, d.selfie_masuk);
            html += buildSesiCard('Absen Pulang', d.jam_pulang, d.lat_pulang, d.lng_pulang, d.jarak_pulang_m, d.akurasi_pulang_m, d.selfie_pulang);
            html += '</div>';

            if (d.keterangan) {
                html += '<div class="alert alert-light mt-3 mb-0"><strong>Keterangan:</strong> ' + $('<span>').text(d.keterangan).html() + '</div>';
            }

            $('#detail-guru-body').html(html);
        });
    };

    function buildSesiCard(judul, jam, lat, lng, jarak, akurasi, selfiePath) {
        if (!jam) {
            return '<div class="col-md-6"><div class="card h-100"><div class="card-body text-center text-muted py-4">' +
                '<i class="bi bi-dash-circle fs-3 d-block mb-2 opacity-50"></i>' + judul + ' belum dilakukan</div></div></div>';
        }
        var mapsUrl = (lat && lng) ? 'https://www.google.com/maps?q=' + lat + ',' + lng : null;
        var fotoUrl = selfiePath ? '{{ url('storage') }}/' + selfiePath : null;

        var html = '<div class="col-md-6"><div class="card h-100">';
        html += '<div class="card-header bg-light py-2"><strong>' + judul + '</strong> — ' + jam.substring(0, 5) + '</div>';
        html += '<div class="card-body">';
        if (fotoUrl) {
            html += '<img src="' + fotoUrl + '" class="img-fluid rounded mb-2" style="max-height:220px;object-fit:cover;width:100%" alt="Selfie ' + judul + '">';
        } else {
            html += '<div class="text-muted small mb-2">Tidak ada foto.</div>';
        }
        html += '<div class="small text-muted">Jarak ke sekolah: <strong>' + (jarak ?? '—') + ' m</strong></div>';
        html += '<div class="small text-muted">Akurasi GPS: <strong>' + (akurasi ? '±' + akurasi + ' m' : '—') + '</strong></div>';
        if (mapsUrl) {
            html += '<a href="' + mapsUrl + '" target="_blank" class="btn btn-xs btn-outline-primary mt-2"><i class="bi bi-geo-alt me-1"></i>Buka Lokasi di Peta</a>';
        }
        html += '</div></div></div>';
        return html;
    }

    // ── Tambah Manual ──────────────────────────────────────────────────────
    $('#tambah-lembaga_id').on('change', function () {
        var lembagaId = $(this).val();
        var $guru = $('#tambah-guru_id');
        $guru.find('option[data-lembaga]').each(function () {
            var show = !lembagaId || $(this).data('lembaga') == lembagaId;
            $(this).prop('hidden', !show);
            if (!show && $(this).is(':selected')) $guru.val('');
        });
    }).trigger('change');

    $('#form-tambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route('admin.akademik.absensi-guru.store') }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                $('#modal-tambah').modal('hide');
                $('#form-tambah')[0].reset();
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
            },
            error: function (xhr) {
                var errors = xhr.responseJSON?.errors;
                var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });

    // ── Edit / Koreksi ───────────────────────────────────────────────────────
    window.editAbsensiGuru = function (id) {
        $.get('{{ url('admin/akademik/absensi-guru') }}/' + id + '/detail', function (res) {
            if (res.status !== 200) return;
            var d = res.data;
            $('#form-edit-absensi').attr('action', '{{ url('admin/akademik/absensi-guru') }}/' + id);
            $('#edit-info-guru').html('<strong>' + (d.guru?.nama_lengkap || '—') + '</strong> — ' + (d.tanggal ? d.tanggal.substring(0, 10) : '—'));
            $('#edit-status').val(d.status);
            $('#edit-jam_masuk').val(d.jam_masuk ? d.jam_masuk.substring(0, 5) : '');
            $('#edit-jam_pulang').val(d.jam_pulang ? d.jam_pulang.substring(0, 5) : '');
            $('#edit-keterangan').val(d.keterangan ?? '');
            $('#modal-edit-absensi').modal('show');
        });
    };

    $('#form-edit-absensi').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (res) {
                $('#modal-edit-absensi').modal('hide');
                table.ajax.reload();
                Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
            },
            error: function (xhr) {
                var errors = xhr.responseJSON?.errors;
                var msg = errors ? Object.values(errors).flat().join('<br>') : (xhr.responseJSON?.message ?? 'Terjadi kesalahan.');
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });

    // ── Hapus ─────────────────────────────────────────────────────────────
    window.hapusAbsensiGuru = function (id) {
        Swal.fire({
            title: 'Hapus Data Absensi?',
            text: 'Data ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Hapus',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/akademik/absensi-guru') }}/' + id,
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
