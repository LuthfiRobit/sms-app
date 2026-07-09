@extends('admin.layouts.app')
@section('title', $rpp ? 'Edit RPP' : 'Tambah RPP')

@section('content')
@php
    if (!function_exists('getBagianIcon')) {
        function getBagianIcon($nama) {
            $nama = strtolower($nama);
            if (str_contains($nama, 'identifikasi') || str_contains($nama, 'kondisi')) return 'bi-eye-fill';
            if (str_contains($nama, 'desain') || str_contains($nama, 'tujuan')) return 'bi-bounding-box-circles';
            if (str_contains($nama, 'langkah') || str_contains($nama, 'kegiatan') || str_contains($nama, 'pengalaman')) return 'bi-compass-fill';
            if (str_contains($nama, 'asesmen') || str_contains($nama, 'evaluasi') || str_contains($nama, 'nilai')) return 'bi-award-fill';
            return 'bi-file-earmark-text-fill';
        }
    }
@endphp

<style>
    /* RPP Stepper Sidebar */
    #rpp-wizard-steps .list-group-item {
        border: none;
        margin-bottom: 8px;
        border-radius: 12px !important;
        transition: all 0.25s ease;
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color-translucent);
    }
    #rpp-wizard-steps .list-group-item:hover {
        background-color: var(--bs-secondary-bg);
        transform: translateX(4px);
        border-color: rgba(13, 92, 62, 0.2);
    }
    #rpp-wizard-steps .list-group-item.active {
        background-color: rgba(13, 92, 62, 0.06) !important;
        color: #0d5c3e !important;
        border: 1.5px solid #0d5c3e !important;
        box-shadow: 0 4px 12px rgba(13, 92, 62, 0.05);
    }
    #rpp-wizard-steps .list-group-item.active .step-icon-wrapper {
        background-color: #0d5c3e !important;
        color: #ffffff !important;
    }
    #rpp-wizard-steps .list-group-item.completed {
        border-color: rgba(40, 167, 69, 0.2);
        background-color: rgba(40, 167, 69, 0.02);
    }
    #rpp-wizard-steps .list-group-item.completed .step-icon-wrapper {
        background-color: #28a745 !important;
        color: #ffffff !important;
    }
    
    .text-xs {
        font-size: 0.75rem;
    }
    .uppercase-letter-spacing {
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    /* Wizard Panel Transition Animations */
    .wizard-panel {
        display: none;
        opacity: 0;
        transform: translateY(15px);
        transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .wizard-panel.active {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Custom Check Cards */
    .check-card {
        border: 1px solid var(--bs-border-color);
        transition: all 0.2s ease;
    }
    .check-card:hover {
        border-color: #0d5c3e !important;
        background-color: rgba(13, 92, 62, 0.02);
    }
    .check-card:has(.form-check-input:checked) {
        border-color: #0d5c3e !important;
        background-color: rgba(13, 92, 62, 0.05);
        box-shadow: 0 2px 8px rgba(13, 92, 62, 0.05);
    }
    
    /* Global Inputs Focus */
    .form-control:focus, .form-select:focus {
        border-color: #0d5c3e !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 92, 62, 0.15) !important;
    }
    
    /* Sticky sidebar behavior */
    @media (min-width: 992px) {
        .sticky-sidebar {
            position: sticky;
            top: 24px;
            z-index: 10;
        }
    }
</style>

<div class="row g-4">
    <!-- Left Sidebar: Stepper -->
    <div class="col-lg-4 col-xl-3">
        <div class="sticky-sidebar">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 1px;">Tahapan RPP</span>
                        <span class="badge bg-light-primary text-primary rounded-pill px-2 py-1" id="wizard-progress-badge" style="font-size: 10px;">Step 1/1</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress rounded-pill mb-4 mx-2" style="height: 6px;">
                        <div class="progress-bar bg-success transition-all" id="rpp-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="list-group list-group-flush gap-1" id="rpp-wizard-steps">
                        <!-- Step 0: Identitas -->
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3 active" data-step="0" data-icon="bi-info-circle-fill">
                            <div class="step-icon-wrapper rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary" style="width: 36px; height: 36px; transition: all 0.2s ease;">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="fw-bold mb-0 text-sm">Identitas Umum</div>
                                <small class="text-muted text-xs">Informasi kelas, mapel &amp; waktu</small>
                            </div>
                        </button>

                        <!-- Dynamic Steps for Bagian -->
                        @foreach($bagianList as $bIndex => $bagian)
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 border-0 rounded-3" data-step="{{ $bIndex + 1 }}" data-icon="{{ getBagianIcon($bagian->nama) }}">
                            <div class="step-icon-wrapper rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary" style="width: 36px; height: 36px; transition: all 0.2s ease;">
                                <i class="bi {{ getBagianIcon($bagian->nama) }} fs-5"></i>
                            </div>
                            <div style="flex: 1;">
                                <div class="fw-bold mb-0 text-sm">{{ $bagian->nama }}</div>
                                <small class="text-muted text-xs">Pengisian detail {{ strtolower($bagian->nama) }}</small>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Content Form -->
    <div class="col-lg-8 col-xl-9" id="rpp-card-wrapper">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-4 px-4 px-md-5 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-journal-text me-2"></i>{{ $rpp ? 'Edit RPP' : 'Tambah RPP' }}</h5>
                    <small class="text-muted">Isi data pada setiap bagian — sistem akan menggabungkannya menjadi RPP utuh.</small>
                </div>
                <div>
                    @if(!$rpp && !$guruAktif && app()->environment(['local', 'testing']))
                        <button type="button" class="btn btn-sm btn-outline-warning me-2 rounded-pill px-3" id="btn-dummy">
                            <i class="bi bi-magic me-1"></i>Isi Data Dummy
                        </button>
                    @endif
                    <a href="{{ route('admin.akademik.rpp.index') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <div id="form-alert" class="alert d-none rounded-3"></div>

                <form id="form-rpp" action="{{ $rpp ? route('admin.akademik.rpp.update', $rpp->id) : route('admin.akademik.rpp.store') }}" method="POST">
                    @csrf
                    @if($rpp) @method('PUT') @endif

                    <!-- STEP 0: Identitas Umum -->
                    <div class="wizard-panel active" data-panel-index="0">
                        <h4 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-primary"></i> Identitas &amp; Informasi Umum
                        </h4>
                        
                        <div class="row g-3 mb-4">
                            @if($guruAktif)
                                <input type="hidden" name="lembaga_id" value="{{ $guruAktif->lembaga_id }}">
                                <input type="hidden" name="guru_id" value="{{ $guruAktif->id }}">
                                <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktif->id }}">
                                <input type="hidden" name="semester_id" value="{{ $semesterAktif->id }}">
                                
                                <div class="col-md-12">
                                    <div class="card border-0 bg-light rounded-3 mb-3">
                                        <div class="card-body p-3 d-flex flex-wrap gap-4 align-items-center">
                                            <div><small class="text-muted d-block text-xs uppercase-letter-spacing">Lembaga</small><strong>{{ $guruAktif->lembaga?->nama }}</strong></div>
                                            <div class="vr bg-dark-subtle opacity-25 d-none d-md-block" style="height: 30px;"></div>
                                            <div><small class="text-muted d-block text-xs uppercase-letter-spacing">Guru</small><strong>{{ $guruAktif->nama_lengkap }}</strong></div>
                                            <div class="vr bg-dark-subtle opacity-25 d-none d-md-block" style="height: 30px;"></div>
                                            <div><small class="text-muted d-block text-xs uppercase-letter-spacing">Tahun Ajaran</small><strong>{{ $tahunAktif->nama }}</strong></div>
                                            <div class="vr bg-dark-subtle opacity-25 d-none d-md-block" style="height: 30px;"></div>
                                            <div><small class="text-muted d-block text-xs uppercase-letter-spacing">Semester</small><strong>{{ $semesterAktif->nama }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg fs-6" name="mata_pelajaran_id" id="f-mapel_id" required>
                                        <option value="">-- Memuat... --</option>
                                    </select>
                                </div>
                            @else
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark">Lembaga <span class="text-danger">*</span></label>
                                    <select class="form-select" name="lembaga_id" id="f-lembaga_id" required>
                                        <option value="">-- Pilih Lembaga --</option>
                                        @foreach($lembagaList as $l)
                                            <option value="{{ $l->id }}" {{ ($rpp->lembaga_id ?? app('active_lembaga_id')) == $l->id ? 'selected' : '' }}>[{{ $l->kode }}] {{ $l->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark">Guru <span class="text-danger">*</span></label>
                                    <select class="form-select" name="guru_id" id="f-guru_id" required>
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($guruList as $g)
                                            <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}" {{ ($rpp->guru_id ?? null) == $g->id ? 'selected' : '' }}>{{ $g->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark">Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="mata_pelajaran_id" id="f-mapel_id" required>
                                        <option value="">-- Pilih Guru Terlebih Dahulu --</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">Tahun Pelajaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tahun_pelajaran_id" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach($tahunList as $t)
                                            <option value="{{ $t->id }}" {{ ($rpp->tahun_pelajaran_id ?? null) == $t->id ? 'selected' : '' }}>{{ $t->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">Semester <span class="text-danger">*</span></label>
                                    <select class="form-select" name="semester_id" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach($semesterList as $s)
                                            <option value="{{ $s->id }}" {{ ($rpp->semester_id ?? null) == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Fase / Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fase_kelas" maxlength="100" placeholder="C / V" value="{{ $rpp->fase_kelas ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Alokasi Waktu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="alokasi_waktu" maxlength="100" placeholder="4 x 35 Menit" value="{{ $rpp->alokasi_waktu ?? '' }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark">Materi / Judul RPP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="materi" maxlength="255" placeholder="mis. Kisah Teladan Umar bin Khattab R.A." value="{{ $rpp->materi ?? '' }}" required>
                            </div>
                            
                            <div class="col-md-12 mt-4">
                                <label class="form-label fw-bold text-dark d-flex align-items-center justify-content-between mb-2">
                                    <span>Submateri</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-tambah-submateri">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Submateri
                                    </button>
                                </label>
                                <div class="table-responsive rounded-3 border mb-2">
                                    <table class="table table-hover align-middle mb-0" id="submateri-table">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th width="50" class="text-center py-3">No</th>
                                                <th class="py-3">Nama Submateri / Bahasan Sesi</th>
                                                <th width="60" class="text-center py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($rpp->submateri ?? []) as $i => $sub)
                                            <tr class="submateri-row">
                                                <td class="text-center text-muted submateri-nomor">{{ $i + 1 }}</td>
                                                <td><input type="text" class="form-control" name="submateri[]" maxlength="255" value="{{ $sub->teks }}"></td>
                                                <td class="text-center"><button type="button" class="btn btn-icon btn-light-danger btn-remove-submateri"><i class="bi bi-trash"></i></button></td>
                                            </tr>
                                            @empty
                                            <tr class="submateri-row">
                                                <td class="text-center text-muted submateri-nomor">1</td>
                                                <td><input type="text" class="form-control" name="submateri[]" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-icon btn-light-danger btn-remove-submateri"><i class="bi bi-trash"></i></button></td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>Setiap baris mewakili satu submateri. Kolom ini digunakan untuk membagi materi per sesi di aplikasi mobile guru. Kosongkan jika RPP ini hanya satu sesi.</div>
                            </div>
                            
                            <div class="col-md-12 mt-3">
                                <label class="form-label fw-bold text-dark">Model Pembelajaran <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg fs-6" name="model_pembelajaran_id" id="model_pembelajaran_id" required>
                                    @foreach($modelList as $m)
                                        <option value="{{ $m->id }}" {{ ($rpp->model_pembelajaran_id ?? null) == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>Menentukan sintaks/tahapan pembelajaran yang tampil pada bagian "Inti".</div>
                            </div>
                        </div>
                    </div>

                    <!-- STEPS 1...N: RppBagian -->
                    @foreach($bagianList as $bIndex => $bagian)
                    <div class="wizard-panel" data-panel-index="{{ $bIndex + 1 }}">
                        <h4 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                            <i class="bi {{ getBagianIcon($bagian->nama) }} text-primary"></i> {{ $bagian->nama }}
                        </h4>
                        
                        <div class="rpp-poin-inputs">
                            @forelse($bagian->poin as $poin)
                                @include('admin.akademik.rpp._poin-input', ['poin' => $poin, 'rpp' => $rpp])
                            @empty
                                <div class="text-muted py-5 text-center">
                                    <i class="bi bi-folder-x fs-1 opacity-25"></i>
                                    <p class="mt-2 small">Belum ada poin di bagian ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach

                    <!-- Wizard Navigation Footer Buttons -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4 btn-wizard-back py-2 rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i>Sebelumnya
                        </button>
                        
                        <div>
                            <button type="button" class="btn btn-primary px-4 btn-wizard-next py-2 rounded-pill">
                                Berikutnya<i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success px-5 btn-wizard-submit d-none py-2 rounded-pill">
                                <i class="bi bi-check-lg me-2"></i>Simpan RPP
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
function showAlert(sel, type, msg) {
    $(sel).removeClass('d-none alert-success alert-danger').addClass('alert-' + type).html(msg);
}

// ── WYSIWYG (CKEditor) untuk semua poin bertipe teks_panjang ────────────────
var rppEditors = {};
$('.rpp-wysiwyg').each(function () {
    var $el = $(this);
    ClassicEditor
        .create(this, { toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'insertTable'] })
        .then(function (editor) { rppEditors[$el.attr('id')] = editor; })
        .catch(function (error) { console.error(error); });
});

function syncWysiwygFields() {
    $('.rpp-wysiwyg').each(function () {
        var editor = rppEditors[$(this).attr('id')];
        if (editor) {
            $(this).val(editor.getData());
        }
    });
}

// ── Dropdown guru (difilter lembaga) & mapel (dependent ke guru) ──────────
function filterGuruByLembaga(lembagaId) {
    var $guru = $('#f-guru_id');
    var selectedBefore = $guru.val();
    $guru.find('option[data-lembaga]').each(function () {
        var opt = $(this);
        if (!lembagaId || opt.data('lembaga') == lembagaId) {
            opt.show();
        } else {
            opt.hide();
            if (opt.is(':selected')) opt.prop('selected', false);
        }
    });
    if ($guru.val() !== selectedBefore) {
        $guru.trigger('change');
    }
}

function loadMapelByGuru(guruId, selectedMapelId) {
    var $mapel = $('#f-mapel_id');
    $mapel.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);

    if (!guruId) {
        $mapel.empty().append('<option value="">-- Pilih Guru Terlebih Dahulu --</option>').prop('disabled', true);
        return;
    }

    var url = '{{ route('admin.akademik.rpp.guru-mapel', ':guruId') }}'.replace(':guruId', guruId);

    $.get(url, function (res) {
        $mapel.empty().append('<option value="">-- Pilih Mata Pelajaran --</option>').prop('disabled', false);
        if (res.status === 200 && res.data.length) {
            $.each(res.data, function (i, item) {
                var selected = (selectedMapelId && item.id == selectedMapelId) ? 'selected' : '';
                $mapel.append('<option value="' + item.id + '" ' + selected + '>' + item.nama + '</option>');
            });
        } else {
            $mapel.empty().append('<option value="">-- Guru tidak memiliki jadwal mengajar --</option>').prop('disabled', true);
        }
    }).fail(function () {
        $mapel.empty().append('<option value="">-- Gagal memuat data --</option>').prop('disabled', true);
    });
}

@if($guruAktif)
    loadMapelByGuru({{ $guruAktif->id }}, {{ $rpp->mata_pelajaran_id ?? 'null' }});
@else
    $('#f-lembaga_id').on('change', function () {
        filterGuruByLembaga($(this).val());
    }).trigger('change');

    $('#f-guru_id').on('change', function () {
        loadMapelByGuru($(this).val(), {{ $rpp->mata_pelajaran_id ?? 'null' }});
    }).trigger('change');
@endif

// ── Baris berulang untuk poin bertipe pasangan_kolom ──────────────────────
$(document).on('click', '.btn-add-row', function () {
    var poinId = $(this).data('poin-id');
    var $table = $('.pasangan-kolom-table[data-poin-id="' + poinId + '"]');
    var idx = $table.find('tbody tr').length;
    var row = $('<tr class="pasangan-row">' +
        '<td class="px-3"><input type="text" class="form-control" name="poin[' + poinId + '][' + idx + '][kolom1]"></td>' +
        '<td class="px-3"><input type="text" class="form-control" name="poin[' + poinId + '][' + idx + '][kolom2]"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-icon btn-light-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>' +
        '</tr>');
    $table.find('tbody').append(row);
});

$(document).on('click', '.btn-remove-row', function () {
    var $tbody = $(this).closest('tbody');
    if ($tbody.find('tr').length > 1) {
        $(this).closest('tr').remove();
    } else {
        $(this).closest('tr').find('input').val('');
    }
});

// ── Baris berulang untuk Submateri ──────────────────────────────────────────
function renumberSubmateri() {
    $('#submateri-table .submateri-row').each(function (i) {
        $(this).find('.submateri-nomor').text(i + 1);
    });
}

$('#btn-tambah-submateri').on('click', function () {
    var row = $('<tr class="submateri-row">' +
        '<td class="text-center text-muted submateri-nomor"></td>' +
        '<td><input type="text" class="form-control" name="submateri[]" maxlength="255"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-icon btn-light-danger btn-remove-submateri"><i class="bi bi-trash"></i></button></td>' +
        '</tr>');
    $('#submateri-table tbody').append(row);
    renumberSubmateri();
    row.find('input').trigger('focus');
});

$(document).on('click', '.btn-remove-submateri', function () {
    var $tbody = $('#submateri-table tbody');
    if ($tbody.find('tr').length > 1) {
        $(this).closest('tr').remove();
        renumberSubmateri();
    } else {
        $(this).closest('tr').find('input').val('');
    }
});

// ── Logika Wizard (Multi-Langkah) ─────────────────────────────────────────
$(document).ready(function() {
    let currentStepIndex = 0;
    const panels = $('.wizard-panel');
    const steps = $('#rpp-wizard-steps button');
    const progressBar = $('#rpp-progress-bar');
    const badge = $('#wizard-progress-badge');
    
    function validateCurrentStep() {
        const activePanel = $('.wizard-panel.active');
        let isValid = true;
        activePanel.find('[required]').each(function() {
            if (!this.checkValidity()) {
                this.reportValidity();
                isValid = false;
                return false; // break loop
            }
        });
        return isValid;
    }

    function updateWizard() {
        // Update panels visibility
        panels.removeClass('active');
        $(panels[currentStepIndex]).addClass('active');
        
        // Update sidebar active class
        steps.removeClass('active');
        $(steps[currentStepIndex]).addClass('active');
        
        // Mark steps as completed or reset icons
        steps.each(function(index) {
            const defaultIcon = $(this).data('icon') || 'bi-file-earmark-text-fill';
            if (index < currentStepIndex) {
                $(this).addClass('completed').find('.step-icon-wrapper').html('<i class="bi bi-check-lg fs-5"></i>');
            } else {
                $(this).removeClass('completed');
                $(this).find('.step-icon-wrapper').html('<i class="bi ' + defaultIcon + ' fs-5"></i>');
            }
        });

        // Update progress bar & progress badge
        const progressPercent = ((currentStepIndex) / (panels.length - 1)) * 100;
        progressBar.css('width', progressPercent + '%');
        badge.text('Langkah ' + (currentStepIndex + 1) + '/' + panels.length);
        
        // Update footer buttons
        if (currentStepIndex === 0) {
            $('.btn-wizard-back').addClass('opacity-50').prop('disabled', true);
        } else {
            $('.btn-wizard-back').removeClass('opacity-50').prop('disabled', false);
        }
        
        if (currentStepIndex === panels.length - 1) {
            $('.btn-wizard-next').addClass('d-none');
            $('.btn-wizard-submit').removeClass('d-none');
        } else {
            $('.btn-wizard-next').removeClass('d-none');
            $('.btn-wizard-submit').addClass('d-none');
        }
        
        // Scroll to top of card smoothly
        $('html, body').animate({
            scrollTop: $('#rpp-card-wrapper').offset().top - 80
        }, 200);
    }
    
    // Klik pada langkah sidebar langsung
    steps.on('click', function() {
        const targetIndex = steps.index(this);
        if (targetIndex === currentStepIndex) return;
        
        // Jika maju langkah, validasi semua langkah diantaranya
        if (targetIndex > currentStepIndex) {
            for (let i = currentStepIndex; i < targetIndex; i++) {
                panels.removeClass('active');
                $(panels[i]).addClass('active');
                if (!validateCurrentStep()) {
                    currentStepIndex = i;
                    updateWizard();
                    return;
                }
            }
        }
        
        currentStepIndex = targetIndex;
        updateWizard();
    });
    
    // Klik Berikutnya
    $('.btn-wizard-next').on('click', function() {
        if (validateCurrentStep()) {
            currentStepIndex++;
            updateWizard();
        }
    });
    
    // Klik Sebelumnya
    $('.btn-wizard-back').on('click', function() {
        if (currentStepIndex > 0) {
            currentStepIndex--;
            updateWizard();
        }
    });
    
    // Interaktivitas Check Cards
    $(document).on('click', '.check-card', function(e) {
        if ($(e.target).is('.form-check-input')) return;
        const checkbox = $(this).find('.form-check-input');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
    });

    // Inisialisasi awal
    updateWizard();
});

// ── Isi Data Dummy (khusus testing, tidak tampil di production) ────────────
function randInt(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }
function pickOne(arr) { return arr[randInt(0, arr.length - 1)]; }
function pickMany(arr, n) {
    var sisa = arr.slice();
    var hasil = [];
    n = Math.min(n, sisa.length);
    for (var i = 0; i < n; i++) {
        hasil.push(sisa.splice(randInt(0, sisa.length - 1), 1)[0]);
    }
    return hasil;
}
function waitFor(cekFn, timeoutMs) {
    return new Promise(function (resolve) {
        var mulai = Date.now();
        (function poll() {
            if (cekFn() || Date.now() - mulai > timeoutMs) return resolve();
            setTimeout(poll, 150);
        })();
    });
}

var DUMMY_MATERI = ['Ide Pokok Paragraf', 'Kisah Teladan Umar bin Khattab R.A.', 'Perkalian dan Pembagian Pecahan', 'Siklus Air dan Manfaatnya', 'Struktur Teks Deskripsi', 'Sistem Pernapasan Manusia', 'Keragaman Budaya Indonesia', 'Bangun Ruang Sisi Datar', 'Perubahan Wujud Benda', 'Norma dan Aturan di Masyarakat'];
var DUMMY_FASE = ['A / I', 'A / II', 'B / III', 'B / IV', 'C / V', 'C / VI'];
var DUMMY_ALOKASI = ['2 x 35 Menit', '3 x 35 Menit', '4 x 35 Menit', '2 x 40 Menit'];
var DUMMY_KALIMAT = [
    'Murid mampu memahami konsep dasar materi dengan baik.',
    'Peserta didik menunjukkan antusiasme tinggi dalam pembelajaran.',
    'Guru memberikan contoh konkret yang relevan dengan kehidupan sehari-hari.',
    'Pembelajaran dilakukan secara berkelompok untuk melatih kolaborasi.',
    'Murid diminta menyampaikan hasil diskusi di depan kelas.',
    'Evaluasi dilakukan melalui tanya-jawab dan lembar kerja.',
    'Materi dikaitkan dengan nilai-nilai karakter Panca Cinta.',
    'Guru menggunakan media visual untuk mempermudah pemahaman.',
    'Murid mengerjakan tugas secara mandiri sebagai bentuk penguatan.',
    'Refleksi dilakukan di akhir sesi untuk mengukur ketercapaian tujuan.',
];
var DUMMY_SUBMATERI = ['Pengertian dan Ciri-Ciri', 'Contoh dalam Kehidupan Sehari-hari', 'Latihan Soal dan Pembahasan', 'Penerapan dalam Proyek Sederhana', 'Diskusi Kelompok dan Presentasi', 'Rangkuman dan Kesimpulan'];

function dummyDaftar() { return pickMany(DUMMY_KALIMAT, randInt(2, 4)).join('\n'); }
function dummyParagraf() { return '<p>' + pickMany(DUMMY_KALIMAT, randInt(2, 3)).join(' ') + '</p>'; }
function dummySingkat() { return pickOne(DUMMY_KALIMAT).split(' ').slice(0, 4).join(' '); }

async function isiDataDummy() {
    var $btn = $('#btn-dummy').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Mengisi...');

    var mapelOpts = $('#f-mapel_id option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
    if (mapelOpts.length === 0) {
        for (var percobaan = 0; percobaan < 8 && mapelOpts.length === 0; percobaan++) {
            var guruAll = $('#f-guru_id option[data-lembaga]').get();
            if (!guruAll.length) break;
            var guruEl = pickOne(guruAll);
            var lembagaId = $(guruEl).data('lembaga');
            $('#f-lembaga_id').val(lembagaId).trigger('change');
            $('#f-guru_id').val(guruEl.value).trigger('change');
            await waitFor(function () { return $('#f-mapel_id option:first').text().indexOf('Memuat') === -1; }, 3000);
            mapelOpts = $('#f-mapel_id option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
        }
    }
    if (mapelOpts.length) $('#f-mapel_id').val(pickOne(mapelOpts));

    var tahunOpts = $('select[name="tahun_pelajaran_id"] option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
    if (tahunOpts.length) $('select[name="tahun_pelajaran_id"]').val(pickOne(tahunOpts));
    var semesterOpts = $('select[name="semester_id"] option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
    if (semesterOpts.length) $('select[name="semester_id"]').val(pickOne(semesterOpts));

    $('input[name="fase_kelas"]').val(pickOne(DUMMY_FASE));
    $('input[name="alokasi_waktu"]').val(pickOne(DUMMY_ALOKASI));
    $('input[name="materi"]').val(pickOne(DUMMY_MATERI) + ' (Dummy ' + randInt(100, 999) + ')');

    // Submateri
    $('#submateri-table tbody tr').slice(1).remove();
    $('#submateri-table tbody tr:first input').val('');
    var subItems = pickMany(DUMMY_SUBMATERI, randInt(2, 4));
    subItems.forEach(function (_, i) { if (i > 0) $('#btn-tambah-submateri').trigger('click'); });
    $('#submateri-table tbody tr').each(function (i) { $(this).find('input').val(subItems[i] || ''); });

    // Model Pembelajaran
    var modelOpts = $('#model_pembelajaran_id option').map(function () { return $(this).val(); }).get();
    if (modelOpts.length) $('#model_pembelajaran_id').val(pickOne(modelOpts)).trigger('change');

    // Poin tipe teks
    $('input[type="text"][name^="poin["]').filter(function () {
        return /^poin\[\d+\]$/.test($(this).attr('name'));
    }).each(function () { $(this).val(dummySingkat()); });

    // Poin tipe daftar_poin
    $('textarea[name^="poin["]:not(.rpp-wysiwyg)').filter(function () {
        return /^poin\[\d+\]$/.test($(this).attr('name'));
    }).each(function () { $(this).val(dummyDaftar()); });

    // Poin tipe pasangan_kolom
    $('.pasangan-kolom-table').each(function () {
        var poinId = $(this).data('poin-id');
        var $tbody = $(this).find('tbody');
        var jumlahBaris = randInt(1, 2);
        while ($tbody.find('tr').length < jumlahBaris) {
            $('.btn-add-row[data-poin-id="' + poinId + '"]').trigger('click');
        }
        $tbody.find('tr').each(function () {
            $(this).find('input').eq(0).val(dummySingkat());
            $(this).find('input').eq(1).val(dummySingkat());
        });
    });

    // Poin tipe pilih_master
    var kelompokCheckbox = {};
    $('input[type="checkbox"][name^="poin["]').each(function () {
        var nama = $(this).attr('name');
        (kelompokCheckbox[nama] = kelompokCheckbox[nama] || []).push(this);
    });
    Object.keys(kelompokCheckbox).forEach(function (nama) {
        var kotak = kelompokCheckbox[nama];
        kotak.forEach(function (k) { k.checked = false; });
        pickMany(kotak, randInt(1, Math.min(3, kotak.length))).forEach(function (k) { k.checked = true; });
    });

    // Poin tipe teks_panjang (WYSIWYG)
    await waitFor(function () {
        return $('.rpp-wysiwyg').toArray().every(function (el) { return rppEditors[el.id]; });
    }, 4000);
    $('.rpp-wysiwyg').each(function () {
        var editor = rppEditors[this.id];
        if (editor) { editor.setData(dummyParagraf()); } else { $(this).val(dummyParagraf()); }
    });

    // Blok Inti
    $('#inti-container textarea').each(function () { $(this).val(dummyDaftar()); });

    $btn.prop('disabled', false).html('<i class="bi bi-magic me-2"></i>Isi Data Dummy');
}

$('#btn-dummy').on('click', isiDataDummy);

// ── Submit ─────────────────────────────────────────────────────────────────
$('#form-rpp').on('submit', function (e) {
    e.preventDefault();
    syncWysiwygFields();
    $('#form-alert').addClass('d-none');
    var $btn = $('#btn-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function (res) {
            if (res.status === 200) {
                toastr.success(res.message);
                window.location.href = '{{ route('admin.akademik.rpp.index') }}';
            } else {
                showAlert('#form-alert', 'danger', res.message);
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            showAlert('#form-alert', 'danger', msg);
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan');
        },
    });
});
</script>
@endpush
