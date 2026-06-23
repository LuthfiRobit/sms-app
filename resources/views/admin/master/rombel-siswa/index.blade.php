@extends('admin.layouts.app')
@section('title', 'Pengelolaan Siswa per Rombel')

@section('content')
<div class="row g-3">
    {{-- Panel Filter & Info --}}
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="bi bi-people me-2"></i>Pengelolaan Siswa per Rombel</h5>
                <small class="text-muted">Assign peserta diterima ke rombel/kelas.</small>
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Lembaga</label>
                        <select class="form-select form-select-sm" id="filter-lembaga">
                            <option value="">-- Semua Lembaga --</option>
                            @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                    [{{ $l->kode }}] {{ $l->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">Rombel <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="filter-rombel">
                            <option value="">-- Pilih Rombel --</option>
                            @foreach($rombelList as $r)
                                <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                    Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary w-100" id="btn-load">
                            <i class="bi bi-arrow-down-circle me-1"></i>Muat Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Siswa Panel (hidden sampai rombel dipilih) --}}
    <div id="panel-siswa" class="col-xl-12 d-none">
        <div class="row g-3">
            {{-- Panel Kiri: Siswa Terdaftar --}}
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-2 d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-success"><i class="bi bi-person-check me-1"></i>Siswa Terdaftar di Rombel</h6>
                            <small class="text-muted" id="info-rombel"></small>
                        </div>
                        @if(auth()->user()->hasPermissionTo('admin.master.rombel-siswa.update-absen'))
                        <button class="btn btn-sm btn-outline-primary ms-2" id="btn-save-absen">
                            <i class="bi bi-save me-1"></i>Simpan No Absen
                        </button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="tbl-assigned">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="8%">No Absen</th>
                                        <th>Nama Siswa</th>
                                        <th width="15%">NISN</th>
                                        @if(auth()->user()->hasPermissionTo('admin.master.rombel-siswa.unassign'))
                                        <th width="8%">Hapus</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="body-assigned">
                                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada siswa</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Kanan: Siswa Tersedia --}}
            @if(auth()->user()->hasPermissionTo('admin.master.rombel-siswa.assign'))
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-2 d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-info"><i class="bi bi-person-plus me-1"></i>Peserta Tersedia</h6>
                            <small class="text-muted">Peserta diterima yang belum di rombel manapun</small>
                        </div>
                        <button class="btn btn-sm btn-success ms-2" id="btn-assign-selected">
                            <i class="bi bi-arrow-left me-1"></i>Assign Terpilih
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <input type="text" class="form-control form-control-sm mb-2" id="cari-available" placeholder="Cari nama...">
                        <div class="table-responsive" style="max-height: 420px; overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0" id="tbl-available">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th width="8%"><input type="checkbox" id="chk-all"></th>
                                        <th>Nama</th>
                                        <th width="17%">NISN</th>
                                    </tr>
                                </thead>
                                <tbody id="body-available">
                                    <tr><td colspan="3" class="text-center text-muted py-3">Muat rombel terlebih dahulu</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var _currentRombelId = null;

    // Filter rombel by lembaga
    $('#filter-lembaga').on('change', function () {
        var lembagaId = $(this).val();
        $('#filter-rombel option[data-lembaga]').each(function () {
            var show = !lembagaId || $(this).data('lembaga') == lembagaId;
            $(this).prop('hidden', !show);
        });
        $('#filter-rombel').val('');
    }).trigger('change');

    // Muat data rombel
    $('#btn-load').on('click', function () {
        var rombelId = $('#filter-rombel').val();
        if (!rombelId) {
            Swal.fire({ icon: 'warning', text: 'Pilih rombel terlebih dahulu.', timer: 2000, showConfirmButton: false });
            return;
        }
        _currentRombelId = rombelId;
        loadSiswa(rombelId);
    });

    function loadSiswa(rombelId) {
        var $btn = $('#btn-load').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Memuat...');
        $.get('{{ url('admin/master/rombel-siswa') }}/' + rombelId + '/siswa', function (res) {
            if (res.status === 200) {
                var d = res.data;
                $('#info-rombel').text('Rombel: Kelas ' + d.rombel.tingkat + ' - ' + d.rombel.nama);
                renderAssigned(d.assigned);
                renderAvailable(d.available);
                $('#panel-siswa').removeClass('d-none');
            }
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-arrow-down-circle me-1"></i>Muat Data');
        });
    }

    function renderAssigned(list) {
        var $body = $('#body-assigned').empty();
        if (!list.length) {
            $body.append('<tr><td colspan="4" class="text-center text-muted py-3">Belum ada siswa di rombel ini</td></tr>');
            return;
        }
        list.forEach(function (s) {
            $body.append(
                '<tr data-peserta-id="' + s.id + '">' +
                '<td><input type="text" class="form-control form-control-sm no-absen-input" value="' + (s.no_absen || '') + '" maxlength="5" style="width:60px"></td>' +
                '<td>' + e(s.nama) + '</td>' +
                '<td>' + (s.nisn || '—') + '</td>' +
                '<td><button class="btn btn-xs btn-icon btn-light-danger btn-hapus-siswa" data-id="' + s.id + '" data-nama="' + e(s.nama) + '" title="Keluarkan"><i class="bi bi-person-dash"></i></button></td>' +
                '</tr>'
            );
        });
    }

    function renderAvailable(list) {
        var $body = $('#body-available').empty();
        if (!list.length) {
            $body.append('<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada peserta tersedia</td></tr>');
            return;
        }
        list.forEach(function (s) {
            $body.append(
                '<tr data-peserta-id="' + s.id + '">' +
                '<td><input type="checkbox" class="chk-siswa" value="' + s.id + '"></td>' +
                '<td>' + e(s.nama) + '</td>' +
                '<td>' + (s.nisn || '—') + '</td>' +
                '</tr>'
            );
        });
    }

    // Cari available
    $('#cari-available').on('input', function () {
        var q = $(this).val().toLowerCase();
        $('#body-available tr').each(function () {
            $(this).toggle(!q || $(this).text().toLowerCase().includes(q));
        });
    });

    // Select all checkbox
    $('#chk-all').on('change', function () {
        $('.chk-siswa:visible').prop('checked', $(this).is(':checked'));
    });

    // Assign terpilih
    $('#btn-assign-selected').on('click', function () {
        if (!_currentRombelId) return;
        var ids = $('.chk-siswa:checked').map(function () { return $(this).val(); }).get();
        if (!ids.length) {
            Swal.fire({ icon: 'warning', text: 'Pilih siswa yang ingin di-assign.', timer: 2000, showConfirmButton: false });
            return;
        }
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: '{{ url('admin/master/rombel-siswa') }}/' + _currentRombelId + '/assign',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', 'peserta_ids[]': ids },
            success: function (res) {
                if (res.status === 200) {
                    loadSiswa(_currentRombelId);
                    Swal.fire({ icon: 'success', text: res.data || 'Siswa berhasil di-assign.', timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', text: res.data || res.message });
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal assign.' });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // Hapus siswa dari rombel
    $(document).on('click', '.btn-hapus-siswa', function () {
        if (!_currentRombelId) return;
        var pesertaId = $(this).data('id');
        var nama = $(this).data('nama');
        Swal.fire({
            title: 'Keluarkan Siswa?',
            text: '"' + nama + '" akan dikeluarkan dari rombel ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Keluarkan',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/master/rombel-siswa') }}/' + _currentRombelId + '/unassign',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', peserta_id: pesertaId },
                success: function (res) {
                    loadSiswa(_currentRombelId);
                    Swal.fire({ icon: 'success', text: res.data || 'Siswa dikeluarkan.', timer: 2000, showConfirmButton: false });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal.' });
                }
            });
        });
    });

    // Simpan nomor absen
    $('#btn-save-absen').on('click', function () {
        if (!_currentRombelId) return;
        var data = [];
        $('#body-assigned tr[data-peserta-id]').each(function () {
            data.push({
                peserta_id: $(this).data('peserta-id'),
                no_absen: $(this).find('.no-absen-input').val() || null,
            });
        });
        if (!data.length) return;
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: '{{ url('admin/master/rombel-siswa') }}/' + _currentRombelId + '/update-absen',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ _token: '{{ csrf_token() }}', data: data }),
            success: function (res) {
                Swal.fire({ icon: 'success', text: res.data || 'No absen disimpan.', timer: 2000, showConfirmButton: false });
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal menyimpan.' });
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    function e(str) {
        return $('<span>').text(str).html();
    }
});
</script>
@endpush
