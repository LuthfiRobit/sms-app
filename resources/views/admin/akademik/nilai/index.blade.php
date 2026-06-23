@extends('admin.layouts.app')
@section('title', 'Input Nilai')

@section('content')
<div class="row g-3">
    {{-- Selector Panel --}}
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="bi bi-journal-text me-2"></i>Input Nilai Siswa</h5>
                <small class="text-muted">Input nilai harian, UTS, dan UAS per rombel dan mata pelajaran.</small>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel-lembaga">
                            <option value="">-- Pilih Lembaga --</option>
                            @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}" {{ app('active_lembaga_id') == $l->id ? 'selected' : '' }}>
                                    [{{ $l->kode }}] {{ $l->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Rombel <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel-rombel">
                            <option value="">-- Pilih Rombel --</option>
                            @foreach($rombelList as $r)
                                <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}">
                                    Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel-mapel">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapelList as $m)
                                <option value="{{ $m->id }}" data-lembaga="{{ $m->lembaga_id }}">{{ $m->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel-semester">
                            <option value="">-- Pilih --</option>
                            @foreach($semesterList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select" id="sel-tahun">
                            <option value="">-- Pilih --</option>
                            @foreach($tahunList as $t)
                                <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                                    {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" id="btn-load-nilai">
                            <i class="bi bi-table me-1"></i>Buka Sheet Nilai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sheet Panel --}}
    <div id="panel-nilai" class="col-xl-12 d-none">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-2 d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="mb-0"><i class="bi bi-table me-1 text-success"></i>Sheet Nilai</h6>
                    <small class="text-muted" id="info-nilai"></small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark border">NA = 40% H + 30% UTS + 30% UAS</span>
                    @if(auth()->user()->hasPermissionTo('admin.akademik.nilai.save'))
                    <button class="btn btn-sm btn-success" id="btn-save-nilai">
                        <i class="bi bi-save me-1"></i>Simpan Semua Nilai
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" id="tbl-nilai">
                        <thead class="bg-light">
                            <tr>
                                <th width="6%">No Absen</th>
                                <th>Nama Siswa</th>
                                <th width="10%" class="text-center">Nilai Harian</th>
                                <th width="10%" class="text-center">Nilai UTS</th>
                                <th width="10%" class="text-center">Nilai UAS</th>
                                <th width="10%" class="text-center bg-light-success">Nilai Akhir</th>
                                <th width="8%" class="text-center">KKM</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="body-nilai"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // Filter by lembaga
    $('#sel-lembaga').on('change', function () {
        var lembagaId = $(this).val();
        ['#sel-rombel', '#sel-mapel'].forEach(function (id) {
            $(id + ' option[data-lembaga]').each(function () {
                var show = !lembagaId || $(this).data('lembaga') == lembagaId;
                $(this).prop('hidden', !show);
                if (!show && $(this).is(':selected')) $(id).val('');
            });
        });
    }).trigger('change');

    // Hitung nilai akhir on-the-fly
    $(document).on('input', '.inp-nilai', function () {
        var $row = $(this).closest('tr');
        var h = parseFloat($row.find('.inp-harian').val()) || null;
        var u = parseFloat($row.find('.inp-uts').val())    || null;
        var a = parseFloat($row.find('.inp-uas').val())    || null;
        if (h !== null && u !== null && a !== null) {
            var na = Math.round((h * 0.4 + u * 0.3 + a * 0.3) * 100) / 100;
            $row.find('.na-display').text(na.toFixed(2));
            var kkm = parseFloat($row.find('.kkm-val').text()) || 70;
            $row.find('.na-display').toggleClass('text-danger', na < kkm).toggleClass('text-success', na >= kkm);
        } else {
            $row.find('.na-display').text('—').removeClass('text-danger text-success');
        }
    });

    // Muat nilai
    $('#btn-load-nilai').on('click', function () {
        var rombelId  = $('#sel-rombel').val();
        var mapelId   = $('#sel-mapel').val();
        var semId     = $('#sel-semester').val();
        if (!rombelId || !mapelId || !semId) {
            Swal.fire({ icon: 'warning', text: 'Pilih rombel, mata pelajaran, dan semester terlebih dahulu.', timer: 2500, showConfirmButton: false });
            return;
        }
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Memuat...');
        $.get('{{ route('admin.akademik.nilai.sheet') }}', {
            rombel_id: rombelId,
            mata_pelajaran_id: mapelId,
            semester_id: semId,
        }, function (res) {
            if (res.status === 200) {
                renderSheet(res.data);
                var rombelText = $('#sel-rombel option:selected').text();
                var mapelText  = $('#sel-mapel option:selected').text();
                var semText    = $('#sel-semester option:selected').text();
                $('#info-nilai').text('Rombel: ' + rombelText + ' | Mapel: ' + mapelText + ' | Semester: ' + semText);
                $('#panel-nilai').removeClass('d-none');
            }
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-table me-1"></i>Buka Sheet Nilai');
        });
    });

    function renderSheet(list) {
        var $body = $('#body-nilai').empty();
        if (!list.length) {
            $body.append('<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada siswa di rombel ini.</td></tr>');
            return;
        }
        list.forEach(function (s, i) {
            var na = (s.nilai_harian !== null && s.nilai_uts !== null && s.nilai_uas !== null)
                ? ((s.nilai_harian * 0.4) + (s.nilai_uts * 0.3) + (s.nilai_uas * 0.3)).toFixed(2)
                : null;
            var naClass = na !== null ? (parseFloat(na) >= (s.kkm || 70) ? 'text-success' : 'text-danger') : '';
            $body.append(
                '<tr>' +
                '<td class="text-center">' + (s.no_absen || (i + 1)) +
                '<input type="hidden" class="peserta-id" value="' + s.peserta_id + '"></td>' +
                '<td>' + $('<span>').text(s.nama).html() + '</td>' +
                '<td><input type="number" class="form-control form-control-sm text-center inp-nilai inp-harian" min="0" max="100" step="0.01" value="' + (s.nilai_harian !== null ? s.nilai_harian : '') + '"></td>' +
                '<td><input type="number" class="form-control form-control-sm text-center inp-nilai inp-uts" min="0" max="100" step="0.01" value="' + (s.nilai_uts !== null ? s.nilai_uts : '') + '"></td>' +
                '<td><input type="number" class="form-control form-control-sm text-center inp-nilai inp-uas" min="0" max="100" step="0.01" value="' + (s.nilai_uas !== null ? s.nilai_uas : '') + '"></td>' +
                '<td class="text-center"><span class="na-display fw-bold ' + naClass + '">' + (na !== null ? na : '—') + '</span></td>' +
                '<td class="text-center"><span class="kkm-val">' + (s.kkm || 70) + '</span></td>' +
                '<td><input type="text" class="form-control form-control-sm inp-catatan" value="' + $('<span>').text(s.catatan || '').html() + '" maxlength="500"></td>' +
                '</tr>'
            );
        });
    }

    // Simpan nilai
    $('#btn-save-nilai').on('click', function () {
        var rows = [];
        $('#body-nilai tr').each(function () {
            var pesertaId = $(this).find('.peserta-id').val();
            if (!pesertaId) return;
            rows.push({
                peserta_id:    parseInt(pesertaId),
                nilai_harian:  $(this).find('.inp-harian').val() || null,
                nilai_uts:     $(this).find('.inp-uts').val()    || null,
                nilai_uas:     $(this).find('.inp-uas').val()    || null,
                catatan:       $(this).find('.inp-catatan').val() || null,
            });
        });
        if (!rows.length) return;

        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url: '{{ route('admin.akademik.nilai.save') }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:             '{{ csrf_token() }}',
                lembaga_id:         $('#sel-lembaga').val(),
                rombel_id:          $('#sel-rombel').val(),
                mata_pelajaran_id:  $('#sel-mapel').val(),
                semester_id:        $('#sel-semester').val(),
                tahun_pelajaran_id: $('#sel-tahun').val(),
                rows:               rows,
            }),
            success: function (res) {
                if (res.status === 200) {
                    Swal.fire({ icon: 'success', text: res.message || 'Nilai berhasil disimpan.', timer: 2500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal menyimpan.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Semua Nilai');
            }
        });
    });
});
</script>
@endpush
