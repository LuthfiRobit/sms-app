@extends('admin.layouts.app')
@section('title', 'Master Siswa')

@section('content')
<div class="row g-3">

    {{-- ── FILTER ──────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Daftar Siswa Aktif</h5>
                    <small class="text-muted">Siswa yang telah diterima dan terdaftar aktif di lembaga ini.</small>
                </div>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermissionTo('admin.master.siswa.export'))
                    <button class="btn btn-sm btn-outline-success" id="btn-export">
                        <i class="bi bi-download me-1"></i>Export CSV
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body pb-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1 small">Lembaga</label>
                        <select class="form-select form-select-sm" id="fil-lembaga">
                            <option value="">-- Semua Lembaga --</option>
                            @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}"
                                    {{ $activeLembagaId == $l->id ? 'selected' : '' }}>
                                    [{{ $l->kode }}] {{ $l->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1 small">Tahun Pelajaran</label>
                        <select class="form-select form-select-sm" id="fil-tahun">
                            <option value="">-- Semua --</option>
                            @foreach($tahunList as $t)
                                <option value="{{ $t->id }}"
                                    {{ $activeTahun && $activeTahun->id == $t->id ? 'selected' : '' }}>
                                    {{ $t->nama }}{{ $t->status === 'aktif' ? ' ✓' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1 small">Kelas / Rombel</label>
                        <select class="form-select form-select-sm" id="fil-rombel">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($rombelList as $r)
                                <option value="{{ $r->id }}" data-lembaga="{{ $r->lembaga_id }}" data-tahun="{{ $r->tahun_pelajaran_id }}">
                                    Kelas {{ $r->tingkat }} - {{ $r->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1 small">Tingkat</label>
                        <select class="form-select form-select-sm" id="fil-tingkat">
                            <option value="">-- Semua --</option>
                            @foreach(range(1, 13) as $t)
                                <option value="{{ $t }}">Tingkat {{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary w-100" id="btn-filter">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STAT CARDS ───────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="row g-3" id="stat-cards">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-primary fs-4"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="text-muted small">Total Siswa</div>
                            <div class="fw-bold fs-4 lh-1" id="stat-total">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 text-info fs-4"><i class="bi bi-gender-male"></i></div>
                        <div>
                            <div class="text-muted small">Laki-laki</div>
                            <div class="fw-bold fs-4 lh-1" id="stat-l">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3 text-danger fs-4"><i class="bi bi-gender-female"></i></div>
                        <div>
                            <div class="text-muted small">Perempuan</div>
                            <div class="fw-bold fs-4 lh-1" id="stat-p">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 text-warning fs-4"><i class="bi bi-exclamation-circle"></i></div>
                        <div>
                            <div class="text-muted small">Belum di Kelas</div>
                            <div class="fw-bold fs-4 lh-1" id="stat-belum">—</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DISTRIBUSI PER TINGKAT ───────────────────────────────────── --}}
    <div class="col-12" id="card-distribusi" style="display:none">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 text-muted"><i class="bi bi-bar-chart me-1"></i>Distribusi per Tingkat</h6>
            </div>
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2" id="distribusi-content"></div>
            </div>
        </div>
    </div>

    {{-- ── DATA TABLE ───────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="tbl-siswa" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="7%" class="text-center">Aksi</th>
                            <th>Nama Siswa</th>
                            <th width="14%">NISN</th>
                            <th width="22%">Kelas</th>
                            <th>Wali Kelas</th>
                            <th width="15%">Tahun Pelajaran</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    var table = null;
    var currentFilters = {
        lembaga_id: $('#fil-lembaga').val(),
        tahun_pelajaran_id: $('#fil-tahun').val(),
        rombel_id: '',
        tingkat: '',
    };

    // ── Filter rombel options by lembaga + tahun ──────────────────────
    function filterRombelOptions() {
        var lembagaId = $('#fil-lembaga').val();
        var tahunId   = $('#fil-tahun').val();
        $('#fil-rombel option[data-lembaga]').each(function () {
            var matchL = !lembagaId || $(this).data('lembaga') == lembagaId;
            var matchT = !tahunId   || $(this).data('tahun') == tahunId;
            $(this).prop('hidden', !(matchL && matchT));
        });
        if ($('#fil-rombel option:selected').prop('hidden')) {
            $('#fil-rombel').val('');
        }
    }

    $('#fil-lembaga, #fil-tahun').on('change', filterRombelOptions);
    filterRombelOptions();

    // ── Init DataTable ────────────────────────────────────────────────
    function initTable(filters) {
        if (table) { table.destroy(); table = null; }

        table = $('#tbl-siswa').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.master.siswa.list') }}',
                data: function (d) {
                    Object.assign(d, filters);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action',       name: 'action',       orderable: false, searchable: false, className: 'text-center' },
                { data: 'nama_display', name: 'peserta.nama_lengkap' },
                { data: 'nisn',         name: 'peserta.nisn' },
                { data: 'kelas_display', name: 'rombel.nama', orderable: true },
                { data: 'wali_kelas_display', name: 'rombel.wali_kelas' },
                { data: 'tahun_nama',   name: 'tahun_pelajaran.nama' },
            ],
            order: [[4, 'asc'], [2, 'asc']],
            pageLength: 25,
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-4 d-block mb-2"></i>Tidak ada data siswa ditemukan</div>',
                info: 'Menampilkan _START_ – _END_ dari _TOTAL_ siswa',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                paginate: { next: '›', previous: '‹' },
            },
            dom: '<"d-flex align-items-center justify-content-between mb-2"lf>rt<"d-flex align-items-center justify-content-between mt-2"ip>',
        });
    }

    // ── Load stats ────────────────────────────────────────────────────
    function loadStats(filters) {
        $.get('{{ route('admin.master.siswa.stats') }}', filters, function (res) {
            if (res.status !== 200) return;
            var d = res.data;
            $('#stat-total').text(d.total.toLocaleString('id'));
            $('#stat-l').text(d.lakiLaki.toLocaleString('id'));
            $('#stat-p').text(d.perempuan.toLocaleString('id'));
            $('#stat-belum').text(d.belumKelas.toLocaleString('id'));

            // Distribusi per tingkat
            if (d.perTingkat && d.perTingkat.length > 0) {
                var html = '';
                d.perTingkat.forEach(function (t) {
                    var label = t.tingkat ? 'Kelas ' + t.tingkat : 'Belum di Kelas';
                    html += '<div class="badge bg-light text-dark border px-3 py-2 rounded-3 d-flex align-items-center gap-2">'
                          + '<span class="fw-bold text-primary">' + label + '</span>'
                          + '<span class="badge bg-primary rounded-pill">' + t.jumlah + ' siswa</span>'
                          + '</div>';
                });
                $('#distribusi-content').html(html);
                $('#card-distribusi').show();
            } else {
                $('#card-distribusi').hide();
            }
        });
    }

    // ── Initial load ──────────────────────────────────────────────────
    initTable(currentFilters);
    loadStats(currentFilters);

    // ── Apply filter ──────────────────────────────────────────────────
    $('#btn-filter').on('click', function () {
        currentFilters = {
            lembaga_id: $('#fil-lembaga').val(),
            tahun_pelajaran_id: $('#fil-tahun').val(),
            rombel_id: $('#fil-rombel').val(),
            tingkat: $('#fil-tingkat').val(),
        };
        initTable(currentFilters);
        loadStats(currentFilters);
    });

    // ── Export CSV ────────────────────────────────────────────────────
    $('#btn-export').on('click', function () {
        var params = new URLSearchParams({
            lembaga_id: $('#fil-lembaga').val(),
            tahun_pelajaran_id: $('#fil-tahun').val(),
            rombel_id: $('#fil-rombel').val(),
        });
        window.location.href = '{{ route('admin.master.siswa.export') }}?' + params.toString();
    });

});
</script>
@endpush
