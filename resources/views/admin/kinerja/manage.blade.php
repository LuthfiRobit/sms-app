@extends('admin.layouts.app')
@section('title', 'Kelola Indikator KPI')

@section('content')

{{-- ══ PAGE HEADER ═════════════════════════════════════════════════════ --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold text-dark">
            <i class="bi bi-sliders me-2 text-primary"></i>Kelola Indikator KPI
        </h4>
        <small class="text-muted">Atur indikator kinerja utama per lembaga dan tahun pelajaran. Setiap indikator dapat diisi secara manual atau dihitung otomatis dari sumber data.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.kinerja.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard Kinerja
        </a>
        <button class="btn btn-sm btn-primary" id="btn-tambah">
            <i class="bi bi-plus-lg me-1"></i>Tambah Indikator
        </button>
    </div>
</div>

{{-- ══ FILTER BAR ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label class="form-label form-label-sm fw-semibold mb-1">Lembaga</label>
                <select class="form-select form-select-sm" id="filter-lembaga">
                    <option value="">-- Pilih Lembaga --</option>
                    @foreach($lembagaList as $l)
                        <option value="{{ $l->id }}" {{ $activeLembagaId == $l->id ? 'selected' : '' }}>
                            [{{ $l->kode }}] {{ $l->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="form-label form-label-sm fw-semibold mb-1">Tahun Pelajaran</label>
                <select class="form-select form-select-sm" id="filter-tahun">
                    <option value="">-- Semua Tahun --</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                            {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <button class="btn btn-sm btn-secondary w-100" id="btn-filter">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ DATA TABLE ═══════════════════════════════════════════════════════ --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 text-primary"><i class="bi bi-table me-1"></i>Daftar Indikator KPI</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered w-100 mb-0" id="kpi-table">
                <thead class="bg-light">
                    <tr>
                        <th width="4%">No</th>
                        <th width="10%">Aksi</th>
                        <th>Nama Indikator</th>
                        <th width="10%">Kategori</th>
                        <th width="7%">Satuan</th>
                        <th width="7%">Target</th>
                        <th width="10%">Realisasi Terbaru</th>
                        <th width="9%">Pencapaian</th>
                        <th width="8%">Auto</th>
                        <th width="9%">Status</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- ══ MODAL TAMBAH / EDIT ══════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-form" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modal-form-title">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Indikator KPI
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="form-id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lembaga <span class="text-danger">*</span></label>
                        <select class="form-select" id="form-lembaga" required>
                            <option value="">-- Pilih Lembaga --</option>
                            @foreach($lembagaList as $l)
                                <option value="{{ $l->id }}" {{ $activeLembagaId == $l->id ? 'selected' : '' }}>
                                    [{{ $l->kode }}] {{ $l->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tahun Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select" id="form-tahun" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($tahunList as $t)
                                <option value="{{ $t->id }}" {{ $t->status === 'aktif' ? 'selected' : '' }}>
                                    {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="form-kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriConfig as $key => $cfg)
                                <option value="{{ $key }}">{{ $cfg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Indikator <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="form-nama" maxlength="255" required placeholder="cth. Tingkat Kelulusan Siswa">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control" id="form-deskripsi" rows="2" maxlength="1000" placeholder="Penjelasan singkat tentang indikator ini (opsional)"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Satuan</label>
                        <input type="text" class="form-control" id="form-satuan" maxlength="50" placeholder="%" value="%">
                        <div class="form-text">Misal: %, orang, unit, jam</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Target <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="form-target" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Urutan</label>
                        <input type="number" class="form-control" id="form-urutan" min="0" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sumber Data</label>
                        <select class="form-select" id="form-sumber-data">
                            <option value="manual">Manual — diinput secara manual</option>
                            <option value="akademik_nilai">Akademik: Rata-rata Nilai</option>
                            <option value="ppdb_pendaftar">PPDB: Jumlah Pendaftar</option>
                            <option value="ppdb_diterima">PPDB: Tingkat Penerimaan (%)</option>
                            <option value="program_kerja">Program Kerja: Tingkat Selesai (%)</option>
                            <option value="absensi">Absensi: Tingkat Kehadiran (%)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kalkulasi Otomatis</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="form-is-auto" role="switch">
                            <label class="form-check-label" for="form-is-auto">Aktifkan kalkulasi otomatis</label>
                        </div>
                        <div class="form-text text-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Jika aktif, nilai dihitung otomatis dari sumber data saat klik <em>Perbarui Data Otomatis</em>.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-form">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL INPUT REALISASI ════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-realisasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-graph-up-arrow me-1"></i>Input Realisasi KPI</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="r-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Indikator</label>
                    <input type="text" class="form-control bg-light" id="r-nama" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="r-periode" maxlength="100" placeholder="cth. Semester 1 2025/2026">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nilai Realisasi <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="r-nilai" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea class="form-control" id="r-catatan" rows="2" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info btn-sm text-white" id="btn-save-realisasi">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const ROUTES = {
        list:       '{{ route("admin.kinerja.list") }}',
        store:      '{{ route("admin.kinerja.store") }}',
        update:     '{{ route("admin.kinerja.update", ":id") }}',
        destroy:    '{{ route("admin.kinerja.destroy", ":id") }}',
        realisasi:  '{{ route("admin.kinerja.inputRealisasi", ":id") }}',
    };

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── DataTable ─────────────────────────────────────────────────────────
    let dt;

    function initDataTable() {
        dt = $('#kpi-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: ROUTES.list,
                data: function (d) {
                    d.lembaga_id         = document.getElementById('filter-lembaga').value;
                    d.tahun_pelajaran_id = document.getElementById('filter-tahun').value;
                },
            },
            columns: [
                { data: 'DT_RowIndex',        name: 'DT_RowIndex',    orderable: false, searchable: false, className: 'text-center' },
                { data: 'action',             name: 'action',         orderable: false, searchable: false, className: 'text-center' },
                { data: 'nama_indikator',     name: 'nama_indikator' },
                { data: 'kategori_label',     name: 'kategori',       className: 'text-center' },
                { data: 'satuan',             name: 'satuan',         className: 'text-center' },
                { data: 'target',             name: 'target',         className: 'text-end' },
                { data: 'nilai_realisasi',    name: 'nilai_realisasi', className: 'text-end' },
                { data: 'pencapaian_persen',  name: 'pencapaian_persen', className: 'text-center' },
                { data: 'is_auto_badge',      name: 'is_auto',        className: 'text-center' },
                { data: 'traffic_light_badge', name: 'traffic_light', className: 'text-center' },
            ],
            order: [[2, 'asc']],
            pageLength: 25,
            language: {
                processing: '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...',
                zeroRecords: 'Tidak ada indikator ditemukan.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ indikator',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: '&lsaquo;', next: '&rsaquo;' },
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_',
            },
        });
    }

    initDataTable();

    document.getElementById('btn-filter').addEventListener('click', () => dt.ajax.reload());

    // ── Modal Form Tambah/Edit ────────────────────────────────────────────
    const modalForm     = new bootstrap.Modal(document.getElementById('modal-form'));
    const modalRls      = new bootstrap.Modal(document.getElementById('modal-realisasi'));

    function resetForm() {
        document.getElementById('form-id').value         = '';
        document.getElementById('form-nama').value       = '';
        document.getElementById('form-deskripsi').value  = '';
        document.getElementById('form-satuan').value     = '%';
        document.getElementById('form-target').value     = '';
        document.getElementById('form-urutan').value     = '0';
        document.getElementById('form-kategori').value   = '';
        document.getElementById('form-sumber-data').value = 'manual';
        document.getElementById('form-is-auto').checked  = false;

        const activeLembaga = document.getElementById('filter-lembaga').value;
        const activeTahun   = document.getElementById('filter-tahun').value;
        if (activeLembaga) document.getElementById('form-lembaga').value = activeLembaga;
        if (activeTahun)   document.getElementById('form-tahun').value   = activeTahun;
    }

    document.getElementById('btn-tambah').addEventListener('click', () => {
        resetForm();
        document.getElementById('modal-form-title').innerHTML = '<i class="bi bi-plus-circle me-1"></i>Tambah Indikator KPI';
        modalForm.show();
    });

    window.editIndikator = function (id) {
        fetch(ROUTES.update.replace(':id', id), {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
            .then(r => r.json())
            .then(res => {
                if (res.status !== 200) {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    return;
                }
                const d = res.data;
                document.getElementById('form-id').value           = d.id;
                document.getElementById('form-lembaga').value      = d.lembaga_id;
                document.getElementById('form-tahun').value        = d.tahun_pelajaran_id;
                document.getElementById('form-kategori').value     = d.kategori;
                document.getElementById('form-nama').value         = d.nama_indikator;
                document.getElementById('form-deskripsi').value    = d.deskripsi || '';
                document.getElementById('form-satuan').value       = d.satuan || '';
                document.getElementById('form-target').value       = d.target;
                document.getElementById('form-sumber-data').value  = d.sumber_data || 'manual';
                document.getElementById('form-is-auto').checked    = !!d.is_auto;
                document.getElementById('form-urutan').value       = d.urutan ?? 0;

                document.getElementById('modal-form-title').innerHTML = '<i class="bi bi-pencil me-1"></i>Edit Indikator KPI';
                modalForm.show();
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data.' }));
    };

    document.getElementById('btn-save-form').addEventListener('click', function () {
        const id = document.getElementById('form-id').value;

        const payload = {
            lembaga_id:         document.getElementById('form-lembaga').value,
            tahun_pelajaran_id: document.getElementById('form-tahun').value,
            kategori:           document.getElementById('form-kategori').value,
            nama_indikator:     document.getElementById('form-nama').value.trim(),
            deskripsi:          document.getElementById('form-deskripsi').value.trim(),
            satuan:             document.getElementById('form-satuan').value.trim(),
            target:             document.getElementById('form-target').value,
            sumber_data:        document.getElementById('form-sumber-data').value,
            is_auto:            document.getElementById('form-is-auto').checked,
            urutan:             parseInt(document.getElementById('form-urutan').value, 10) || 0,
        };

        if (!payload.lembaga_id || !payload.tahun_pelajaran_id || !payload.nama_indikator || !payload.kategori || !payload.target) {
            Swal.fire({ icon: 'warning', title: 'Isian Tidak Lengkap', text: 'Lembaga, tahun, kategori, nama, dan target wajib diisi.' });
            return;
        }

        const url    = id ? ROUTES.update.replace(':id', id) : ROUTES.store;
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(res => {
                if (res.status === 200) {
                    modalForm.hide();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
                    dt.ajax.reload();
                } else if (res.status === 422 && res.errors) {
                    const msgs = Object.values(res.errors).flat().join('\n');
                    Swal.fire({ icon: 'warning', title: 'Validasi', text: msgs });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
    });

    // ── Hapus ─────────────────────────────────────────────────────────────
    window.hapusIndikator = function (id) {
        Swal.fire({
            title: 'Hapus Indikator?',
            text: 'Indikator yang tidak memiliki data realisasi saja yang dapat dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(ROUTES.destroy.replace(':id', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1800, showConfirmButton: false });
                        dt.ajax.reload();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
        });
    };

    // ── Input Realisasi ───────────────────────────────────────────────────
    window.inputRealisasi = function (id) {
        // Fetch detail to get nama_indikator
        fetch(ROUTES.update.replace(':id', id), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
            .then(r => r.json())
            .then(res => {
                if (res.status !== 200) return;
                const d = res.data;
                document.getElementById('r-id').value     = d.id;
                document.getElementById('r-nama').value   = d.nama_indikator;
                document.getElementById('r-periode').value  = '';
                document.getElementById('r-nilai').value    = '';
                document.getElementById('r-catatan').value  = '';
                modalRls.show();
            });
    };

    document.getElementById('btn-save-realisasi').addEventListener('click', function () {
        const id      = document.getElementById('r-id').value;
        const periode = document.getElementById('r-periode').value.trim();
        const nilai   = document.getElementById('r-nilai').value.trim();
        const catatan = document.getElementById('r-catatan').value.trim();

        if (!periode || !nilai) {
            Swal.fire({ icon: 'warning', title: 'Isian Tidak Lengkap', text: 'Periode dan nilai wajib diisi.' });
            return;
        }

        fetch(ROUTES.realisasi.replace(':id', id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ periode, nilai_realisasi: parseFloat(nilai), catatan }),
        })
            .then(r => r.json())
            .then(res => {
                modalRls.hide();
                if (res.status === 200) {
                    Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 1800, showConfirmButton: false });
                    dt.ajax.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
    });
}());
</script>
@endpush

@endsection
