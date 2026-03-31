@extends('admin.layouts.app')
@section('title', 'Builder Formulir — ' . $formulir->nama)

@push('styles')
<style>
/* ============================================================
   BUILDER LAYOUT
   ============================================================ */
.builder-wrapper {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    min-height: calc(100vh - 220px);
}

/* Panel Kiri — Dapodik Palette */
.palette-panel {
    width: 320px;
    flex-shrink: 0;
    position: sticky;
    top: 80px;
}

/* Panel Kanan — Drop Area */
.canvas-panel {
    flex: 1;
    min-width: 0;
}

/* ============================================================
   DAPODIK ITEM — bisa di-drag ke canvas
   ============================================================ */
.dapodik-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    margin-bottom: 6px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: grab;
    transition: box-shadow .15s ease, border-color .15s ease, transform .1s ease;
    user-select: none;
    font-size: .84rem;
}
.dapodik-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13,110,253,.18);
    transform: translateX(2px);
}
.dapodik-item.sortable-chosen { cursor: grabbing; opacity: .7; }
.dapodik-item.already-added {
    background: #f8f9fa;
    border-color: #adb5bd;
    opacity: .55;
    cursor: not-allowed;
}
.dapodik-item .dapodik-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem;
    flex-shrink: 0;
}
.dapodik-item .dapodik-label { font-weight: 500; flex: 1; line-height: 1.3; }
.dapodik-item .dapodik-type  { font-size: .7rem; color: #6c757d; }

/* ============================================================
   FIELD CARD — di canvas
   ============================================================ */
.field-card {
    background: #fff;
    border: 1.5px solid #dee2e6;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 10px;
    transition: box-shadow .15s ease, border-color .15s ease;
}
.field-card:hover { border-color: #0d6efd; box-shadow: 0 3px 12px rgba(13,110,253,.12); }
.field-card.sortable-ghost { opacity: .4; background: #e8f0fe; border: 2px dashed #0d6efd; }
.field-card.sortable-chosen { cursor: grabbing; box-shadow: 0 6px 20px rgba(13,110,253,.22); }

.field-card .drag-handle {
    cursor: grab;
    color: #adb5bd;
    font-size: 1.1rem;
    padding: 0 6px;
    line-height: 1;
}
.field-card .drag-handle:hover { color: #0d6efd; }
.field-card .field-label    { font-weight: 600; font-size: .9rem; }
.field-card .field-badges   { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }

/* ============================================================
   DROP ZONE (empty state)
   ============================================================ */
.drop-zone-empty {
    border: 2.5px dashed #adb5bd;
    border-radius: 12px;
    padding: 56px 24px;
    text-align: center;
    color: #adb5bd;
    background: #f8f9fa;
    transition: border-color .2s, background .2s;
}
.drop-zone-empty.drag-over {
    border-color: #0d6efd;
    background: #eef3ff;
    color: #0d6efd;
}

/* Highlight seluruh canvas saat ada drag dari palette */
.canvas-drag-over,
#canvas-body.drag-over-canvas {
    outline: 2.5px dashed #0d6efd;
    outline-offset: -4px;
    background: #f0f5ff;
    border-radius: 8px;
    transition: outline .15s, background .15s;
}

/* ============================================================
   TIPE BADGE COLOR MAP
   ============================================================ */
.badge-tipe-text     { background:#17a2b8; }
.badge-tipe-number   { background:#6f42c1; }
.badge-tipe-date     { background:#fd7e14; }
.badge-tipe-select   { background:#20c997; }
.badge-tipe-radio    { background:#e83e8c; }
.badge-tipe-file     { background:#6c757d; }
.badge-tipe-textarea { background:#0d6efd; }

/* ============================================================
   PREVIEW MODAL
   ============================================================ */
.preview-form-group { margin-bottom: 1.25rem; }
.preview-form-group label { font-weight: 600; font-size: .88rem; margin-bottom: .3rem; display: block; }
.preview-form-group .required-star { color: #dc3545; }
.preview-field-static { border-left: 3px solid #0d6efd; padding-left: 8px; }
.preview-field-dynamic { border-left: 3px solid #6f42c1; padding-left: 8px; }

/* ============================================================
   PALETTE ACCORDION
   ============================================================ */
.palette-accordion .accordion-button {
    font-size: .82rem;
    font-weight: 600;
    padding: 8px 14px;
    background: #f0f4ff;
}
.palette-accordion .accordion-button:not(.collapsed) {
    background: #0d6efd;
    color: #fff;
}
.palette-accordion .accordion-body { padding: 10px; }

/* Loading overlay */
.loading-overlay {
    position: fixed; inset: 0; background: rgba(255,255,255,.65);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; display: none;
}
</style>
@endpush

@section('content')
{{-- ============================================================
     LOADING OVERLAY
     ============================================================ --}}
<div class="loading-overlay" id="loading-overlay">
    <div class="text-center">
        <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;" role="status"></div>
        <div class="mt-2 fw-bold text-primary">Memproses...</div>
    </div>
</div>

{{-- ============================================================
     HEADER — info formulir
     ============================================================ --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.ppdb.formulir.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="flex-grow-1">
                <h5 class="mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-tools text-primary"></i>
                    <span>Builder: <strong>{{ $formulir->nama }}</strong></span>
                    @if($formulir->is_aktif)
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                    @else
                        <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </h5>
                <small class="text-muted">
                    <i class="bi bi-diagram-3 me-1"></i>{{ $formulir->jalurPendaftaran?->nama ?? '-' }}
                    &nbsp;·&nbsp;
                    <i class="bi bi-calendar3 me-1"></i>{{ $formulir->tahunPelajaran?->nama ?? '-' }}
                    @if($formulir->deskripsi)
                        &nbsp;·&nbsp; {{ $formulir->deskripsi }}
                    @endif
                </small>
            </div>
            <div class="d-flex gap-2 flex-shrink-0">
                <button class="btn btn-sm btn-outline-success" id="btn-preview-formulir">
                    <i class="bi bi-eye me-1"></i> Preview
                </button>
                <button class="btn btn-sm btn-outline-primary" id="btn-simpan-urutan">
                    <i class="bi bi-arrow-down-up me-1"></i> Simpan Urutan
                </button>
                <button class="btn btn-sm {{ $formulir->is_aktif ? 'btn-warning' : 'btn-success' }}"
                        id="btn-toggle-aktif" data-id="{{ $formulir->id }}"
                        data-aktif="{{ $formulir->is_aktif ? '1' : '0' }}">
                    @if($formulir->is_aktif)
                        <i class="bi bi-toggle-off me-1"></i> Nonaktifkan
                    @else
                        <i class="bi bi-toggle-on me-1"></i> Aktifkan
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     BUILDER — 2 Panel
     ============================================================ --}}
<div class="builder-wrapper">

    {{-- ============================================================
         PANEL KIRI — Dapodik Palette
         ============================================================ --}}
    <div class="palette-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-2 d-flex align-items-center gap-2">
                <i class="bi bi-database-fill-gear text-info"></i>
                <span class="fw-bold small">Field Dapodik Tersedia</span>
                <span class="badge bg-info ms-auto" id="badge-dapodik-count">{{ collect($dapodikGrouped)->flatten(1)->count() }}</span>
            </div>
            <div class="card-body p-2">
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="dapodik-search" placeholder="Cari field Dapodik...">
                </div>
                <p class="text-muted small px-1 mb-2">
                    <i class="bi bi-info-circle me-1"></i>Drag field ke panel kanan untuk menambahkan.
                </p>

                {{-- Group per tabel --}}
                <div class="accordion palette-accordion" id="palette-accordion">
                    @php
                    $tableLabels = [
                        'peserta'            => ['label' => 'Data Pribadi',    'icon' => 'bi-person-fill',        'color' => 'primary'],
                        'peserta_alamat'     => ['label' => 'Alamat',          'icon' => 'bi-geo-alt-fill',       'color' => 'success'],
                        'peserta_kontak'     => ['label' => 'Kontak',          'icon' => 'bi-telephone-fill',     'color' => 'warning'],
                        'peserta_orang_tua'  => ['label' => 'Orang Tua',       'icon' => 'bi-people-fill',        'color' => 'danger'],
                        'peserta_periodik'   => ['label' => 'Data Periodik',   'icon' => 'bi-heart-pulse-fill',   'color' => 'info'],
                    ];
                    $usedDapodikKeys = $formulir->formulirField
                        ->whereNotNull('dapodik_key')
                        ->pluck('dapodik_key')
                        ->toArray();
                    @endphp

                    @foreach($dapodikGrouped as $table => $fields)
                    @php $meta = $tableLabels[$table] ?? ['label' => ucfirst($table), 'icon' => 'bi-table', 'color' => 'secondary']; @endphp
                    <div class="accordion-item border-0 mb-1">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }} rounded"
                                    type="button" data-bs-toggle="collapse"
                                    data-bs-target="#palette-{{ $table }}">
                                <i class="bi {{ $meta['icon'] }} me-2 text-{{ $meta['color'] }}"></i>
                                {{ $meta['label'] }}
                                <span class="badge bg-{{ $meta['color'] }} ms-2">{{ count($fields) }}</span>
                            </button>
                        </h2>
                        <div id="palette-{{ $table }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                             data-bs-parent="#palette-accordion">
                            <div class="accordion-body" id="palette-list-{{ $table }}">
                                @foreach($fields as $key => $fieldMeta)
                                @php $isUsed = in_array($key, $usedDapodikKeys); @endphp
                                <div class="dapodik-item {{ $isUsed ? 'already-added' : '' }}"
                                     data-dapodik-key="{{ $key }}"
                                     data-label="{{ $fieldMeta['label'] }}"
                                     data-tipe="{{ $fieldMeta['tipe'] }}"
                                     data-table="{{ $table }}"
                                     draggable="{{ $isUsed ? 'false' : 'true' }}"
                                     title="{{ $isUsed ? 'Sudah ditambahkan ke formulir' : 'Drag ke panel kanan untuk menambahkan' }}">
                                    <div class="dapodik-icon bg-{{ $meta['color'] }}-subtle text-{{ $meta['color'] }}">
                                        <i class="bi bi-database"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="dapodik-label">{{ $fieldMeta['label'] }}</div>
                                        <div class="dapodik-type">{{ strtoupper($fieldMeta['tipe']) }}</div>
                                    </div>
                                    @if($isUsed)
                                        <i class="bi bi-check-circle-fill text-success small"></i>
                                    @else
                                        <i class="bi bi-grip-vertical text-muted small"></i>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Tombol tambah field dinamis --}}
                <div class="d-grid mt-3">
                    <button class="btn btn-outline-primary btn-sm" id="btn-tambah-dinamis">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Field Dinamis
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         PANEL KANAN — Canvas / Drop Zone
         ============================================================ --}}
    <div class="canvas-panel">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-2 d-flex align-items-center">
                <i class="bi bi-ui-checks text-primary me-2"></i>
                <span class="fw-bold small">Field Formulir</span>
                <span class="badge bg-primary ms-2" id="badge-field-count">
                    {{ $formulir->formulirField->count() }}
                </span>
                <span class="text-muted small ms-auto" id="urutan-hint" style="display:none">
                    <i class="bi bi-info-circle me-1"></i>Urutan berubah — klik "Simpan Urutan"
                </span>
            </div>
            <div class="card-body" id="canvas-body" style="min-height: 400px;">

                {{-- Empty state --}}
                <div class="drop-zone-empty" id="drop-empty-state" style="{{ $formulir->formulirField->count() > 0 ? 'display:none' : '' }}">
                    <i class="bi bi-arrow-left-circle fs-2 mb-3 d-block"></i>
                    <h6>Belum ada field</h6>
                    <p class="mb-0 small">Drag field Dapodik dari panel kiri, atau gunakan tombol<br>
                    <strong>"Tambah Field Dinamis"</strong> untuk field kustom.</p>
                </div>

                {{-- Field list (sortable) --}}
                <div id="sortable-fields">
                    @foreach($formulir->formulirField->sortBy('urutan') as $field)
                    {{-- BUG FIX: Gunakan @if directive (bukan {{ }}) agar tanda kutip tidak di-escape Blade --}}
                    <div class="field-card"
                         data-field-id="{{ $field->id }}"
                         @if($field->is_statis && $field->dapodik_key) data-dapodik-key="{{ $field->dapodik_key }}" @endif>
                        <div class="d-flex align-items-start gap-2">
                            <span class="drag-handle mt-1"><i class="bi bi-grip-vertical"></i></span>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="field-label">{{ $field->label }}</span>
                                    @if($field->is_required)
                                        <i class="bi bi-asterisk text-danger" title="Wajib diisi" style="font-size:.7rem;"></i>
                                    @endif
                                </div>
                                <div class="field-badges mt-2">
                                    @if($field->is_statis)
                                        <span class="badge bg-info text-white" title="Mapping ke: {{ $field->dapodik_key }}">
                                            <i class="bi bi-database me-1"></i>Statis (Dapodik)
                                        </span>
                                        <span class="badge bg-secondary" style="font-size:.7rem;">{{ $field->dapodik_key }}</span>
                                    @else
                                        <span class="badge bg-purple text-white" style="background:#6f42c1;">
                                            <i class="bi bi-sliders me-1"></i>Dinamis
                                        </span>
                                    @endif
                                    <span class="badge text-white badge-tipe-{{ $field->tipe_field }}" style="font-size:.7rem;">
                                        {{ strtoupper($field->tipe_field) }}
                                    </span>
                                    @if($field->is_required)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.7rem;">
                                            Wajib
                                        </span>
                                    @endif
                                </div>
                                @if($field->opsi && count($field->opsi) > 0)
                                <div class="mt-1">
                                    <span class="text-muted small"><i class="bi bi-list-ul me-1"></i>
                                    {{ implode(', ', array_slice($field->opsi, 0, 3)) }}{{ count($field->opsi) > 3 ? '...' : '' }}
                                    ({{ count($field->opsi) }} opsi)</span>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button class="btn btn-sm btn-outline-primary btn-edit-field"
                                        data-field-id="{{ $field->id }}"
                                        title="Edit Field">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-field"
                                        data-field-id="{{ $field->id }}"
                                        data-label="{{ $field->label }}"
                                        title="Hapus Field">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL — Tambah Field Dinamis
     ============================================================ --}}
<div class="modal fade" id="modal-tambah-field" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bi bi-plus-circle me-1"></i> Tambah Field Dinamis</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Label Field <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dyn-label" placeholder="Contoh: Nilai Rapor Semester 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tipe Field <span class="text-danger">*</span></label>
                        <select class="form-select" id="dyn-tipe">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select (Dropdown)</option>
                            <option value="radio">Radio</option>
                            <option value="file">File Upload</option>
                        </select>
                    </div>
                    <div class="col-12" id="dyn-opsi-group" style="display:none;">
                        <label class="form-label fw-bold">
                            Pilihan Opsi <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small ms-2">(satu opsi per baris)</span>
                        </label>
                        <textarea class="form-control font-monospace" id="dyn-opsi" rows="5"
                                  placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"></textarea>
                        <div class="form-text">Minimal 2 opsi harus diisi.</div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dyn-required">
                            <label class="form-check-label fw-bold" for="dyn-required">
                                Wajib diisi <small class="text-muted fw-normal">(is_required)</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-submit-dinamis">
                    <i class="bi bi-plus me-1"></i> Tambah Field
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL — Edit Field
     ============================================================ --}}
<div class="modal fade" id="modal-edit-field" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i> Edit Field</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-field-id">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Label Field <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-field-label">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tipe Field <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-field-tipe">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select (Dropdown)</option>
                            <option value="radio">Radio</option>
                            <option value="file">File Upload</option>
                        </select>
                    </div>
                    <div class="col-12" id="edit-opsi-group" style="display:none;">
                        <label class="form-label fw-bold">
                            Pilihan Opsi <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small ms-2">(satu opsi per baris)</span>
                        </label>
                        <textarea class="form-control font-monospace" id="edit-field-opsi" rows="5"></textarea>
                    </div>
                    <div class="col-12" id="edit-dapodik-group" style="display:none;">
                        <label class="form-label fw-bold">Dapodik Key</label>
                        <select class="form-select" id="edit-field-dapodik-key">
                            <option value="">-- Tidak Pakai Mapping Dapodik --</option>
                            @foreach($dapodikFlat as $key => $meta)
                                <option value="{{ $key }}">{{ $meta['label'] }} ({{ $key }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit-field-required">
                            <label class="form-check-label fw-bold" for="edit-field-required">Wajib diisi</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit-field-statis">
                            <label class="form-check-label fw-bold" for="edit-field-statis">Field Statis (Dapodik)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-submit-edit-field">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL — Preview Formulir
     ============================================================ --}}
<div class="modal fade" id="modal-preview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-eye me-1"></i> Preview Formulir: {{ $formulir->nama }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Ini adalah <strong>simulasi tampilan</strong> formulir seperti yang akan dilihat peserta.
                    Field berwarna biru = data Dapodik (statis), ungu = field kustom (dinamis).
                </div>
                {{-- Header info --}}
                <div class="card mb-4 border-0 bg-light">
                    <div class="card-body">
                        <h5 class="text-primary mb-1">{{ $formulir->nama }}</h5>
                        @if($formulir->deskripsi)
                        <p class="text-muted small mb-0">{{ $formulir->deskripsi }}</p>
                        @endif
                        <div class="mt-2">
                            <span class="badge bg-primary me-1">{{ $formulir->jalurPendaftaran?->nama ?? '-' }}</span>
                            <span class="badge bg-secondary">{{ $formulir->tahunPelajaran?->nama ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                {{-- Rendered fields --}}
                <div id="preview-form-body">
                    <div class="text-center py-5 text-muted" id="preview-loading">
                        <div class="spinner-border spinner-border-sm me-2"></div> Memuat preview...
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="text-muted small me-auto">
                    <i class="bi bi-shield-check text-success me-1"></i>
                    Form ini hanya preview — tidak ada data yang tersimpan.
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SortableJS via jsDelivr --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
$(document).ready(function() {

    // ===================================================================
    // CONFIG
    // ===================================================================
    const FORMULIR_ID  = {{ $formulir->id }};
    const BASE_URL     = "{{ url('admin/ppdb/formulir') }}";
    const FIELD_URL    = "{{ url('admin/ppdb/formulir/field') }}";
    const REORDER_URL  = `${BASE_URL}/${FORMULIR_ID}/reorder-fields`;
    const TOGGLE_URL   = `${BASE_URL}/${FORMULIR_ID}/toggle-aktif`;
    const CSRF_TOKEN   = "{{ csrf_token() }}";

    // Dapodik options sebagai JS object untuk lookup cepat
    const dapodikFlat = @json($dapodikFlat);

    // ===================================================================
    // HELPERS
    // ===================================================================
    function showLoading()  { document.getElementById('loading-overlay').style.display = 'flex'; }
    function hideLoading()  { document.getElementById('loading-overlay').style.display = 'none'; }
    function updateFieldCount() {
        const cnt = document.querySelectorAll('#sortable-fields .field-card').length;
        document.getElementById('badge-field-count').textContent = cnt;
        document.getElementById('drop-empty-state').style.display = cnt > 0 ? 'none' : '';
    }
    function markDapodikUsed(key, used) {
        const el = document.querySelector(`.dapodik-item[data-dapodik-key="${key}"]`);
        if (!el) return;
        if (used) {
            el.classList.add('already-added');
            el.setAttribute('draggable', 'false');
            // Ganti icon grip → check
            const icon = el.querySelector('i.bi:last-child');
            if (icon) {
                icon.className = 'bi bi-check-circle-fill text-success small';
            }
        } else {
            el.classList.remove('already-added');
            el.setAttribute('draggable', 'true');
            // Restore icon check → grip
            const icon = el.querySelector('i.bi:last-child');
            if (icon) {
                icon.className = 'bi bi-grip-vertical text-muted small';
            }
            // Re-attach dragstart agar bisa drag lagi setelah unmark
            attachSingleDapodikItem(el);
        }
    }

    // Build field card HTML
    function buildFieldCard(field) {
        const badgesHtml = (() => {
            let b = '';
            if (field.is_statis) {
                b += `<span class="badge bg-info text-white" title="Mapping ke: ${field.dapodik_key}">
                        <i class="bi bi-database me-1"></i>Statis (Dapodik)
                      </span>
                      <span class="badge bg-secondary" style="font-size:.7rem;">${field.dapodik_key}</span>`;
            } else {
                b += `<span class="badge text-white" style="background:#6f42c1;font-size:.75rem;">
                        <i class="bi bi-sliders me-1"></i>Dinamis
                      </span>`;
            }
            b += `<span class="badge text-white badge-tipe-${field.tipe_field}" style="font-size:.7rem;">
                    ${field.tipe_field.toUpperCase()}
                  </span>`;
            if (field.is_required) {
                b += `<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.7rem;">Wajib</span>`;
            }
            return b;
        })();

        const opsiInfo = (() => {
            if (field.opsi && field.opsi.length > 0) {
                const preview = field.opsi.slice(0, 3).join(', ') + (field.opsi.length > 3 ? '...' : '');
                return `<div class="mt-1"><span class="text-muted small">
                    <i class="bi bi-list-ul me-1"></i>${preview} (${field.opsi.length} opsi)</span></div>`;
            }
            return '';
        })();

        // Simpan dapodik_key di data attribute agar bisa di-unmark saat delete
        const dapodikKeyAttr = (field.is_statis && field.dapodik_key)
            ? `data-dapodik-key="${field.dapodik_key}"`
            : '';

        return `
        <div class="field-card" data-field-id="${field.id}" ${dapodikKeyAttr}>
            <div class="d-flex align-items-start gap-2">
                <span class="drag-handle mt-1"><i class="bi bi-grip-vertical"></i></span>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="field-label">${field.label}</span>
                        ${field.is_required ? '<i class="bi bi-asterisk text-danger" style="font-size:.7rem;"></i>' : ''}
                    </div>
                    <div class="field-badges mt-2">${badgesHtml}</div>
                    ${opsiInfo}
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <button class="btn btn-sm btn-outline-primary btn-edit-field" data-field-id="${field.id}" title="Edit Field">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-field"
                            data-field-id="${field.id}" data-label="${field.label}" title="Hapus Field">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    // ===================================================================
    // SORTABLEJS — Reorder dalam canvas
    // ===================================================================
    let orderChanged = false;
    const sortableCanvas = Sortable.create(document.getElementById('sortable-fields'), {
        animation: 200,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd() {
            orderChanged = true;
            document.getElementById('urutan-hint').style.display = '';
        }
    });

    // ===================================================================
    // DRAG & DROP — Palette ke Canvas
    // BUG FIX:
    // 1. Pasang event ke #canvas-body (parent) bukan hanya #sortable-fields
    //    agar bisa menerima drop meski sudah ada field-card di dalamnya.
    // 2. attachSingleDapodikItem() agar item yang di-unmark bisa drag lagi.
    // ===================================================================
    let draggedDapodikKey  = null;
    let draggedDapodikMeta = null;
    const canvasBody = document.getElementById('canvas-body');

    // dragover & drop dipasang ke canvas-body (mencakup #sortable-fields + #drop-empty-state)
    canvasBody.addEventListener('dragover', e => {
        if (!draggedDapodikKey) return; // hanya izinkan drag dari palette
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        canvasBody.classList.add('drag-over-canvas');
    });

    canvasBody.addEventListener('dragleave', e => {
        // Hanya hapus highlight jika benar-benar keluar dari canvas (bukan masuk child)
        if (!canvasBody.contains(e.relatedTarget)) {
            canvasBody.classList.remove('drag-over-canvas');
        }
    });

    canvasBody.addEventListener('drop', e => {
        e.preventDefault();
        canvasBody.classList.remove('drag-over-canvas');
        if (!draggedDapodikKey) return;
        addStatisField(draggedDapodikKey, draggedDapodikMeta);
        draggedDapodikKey  = null;
        draggedDapodikMeta = null;
    });

    // Attach dragstart ke satu item
    function attachSingleDapodikItem(item) {
        // Hapus listener lama dengan clone trick agar tidak duplikat
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);

        newItem.addEventListener('dragstart', function(e) {
            if (this.classList.contains('already-added')) { e.preventDefault(); return; }
            draggedDapodikKey  = this.dataset.dapodikKey;
            draggedDapodikMeta = {
                label : this.dataset.label,
                tipe  : this.dataset.tipe,
                table : this.dataset.table,
            };
            e.dataTransfer.effectAllowed = 'copy';
        });

        newItem.addEventListener('dragend', () => {
            draggedDapodikKey  = null;
            draggedDapodikMeta = null;
            canvasBody.classList.remove('drag-over-canvas');
        });
    }

    // Pasang ke semua item sekaligus (termasuk yang already-added — dragstart akan di-block di dalamnya)
    function attachDapodikDragHandlers() {
        document.querySelectorAll('.dapodik-item').forEach(item => {
            attachSingleDapodikItem(item);
        });
    }

    attachDapodikDragHandlers();

    // ===================================================================
    // ADD STATIS FIELD (drag dari palette)
    // ===================================================================
    function addStatisField(key, meta) {
        showLoading();
        $.ajax({
            url: `${BASE_URL}/${FORMULIR_ID}/fields`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            data: {
                label       : meta.label,
                tipe_field  : meta.tipe,
                is_required : 0,
                is_statis   : 1,
                dapodik_key : key,
            },
            success(res) {
                hideLoading();
                if (res.status === 200 && res.data) {
                    $('#sortable-fields').append(buildFieldCard(res.data));
                    markDapodikUsed(key, true);
                    updateFieldCount();
                    toastSuccess(res.message || 'Field berhasil ditambahkan.');
                } else {
                    toastError(res.message || 'Gagal menambahkan field.');
                }
            },
            error(xhr) {
                hideLoading();
                toastError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    }

    // ===================================================================
    // TOAST HELPERS (gunakan SweetAlert2 toast)
    // ===================================================================
    function toastSuccess(msg) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg,
                    showConfirmButton: false, timer: 2500, timerProgressBar: true });
    }
    function toastError(msg) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg,
                    showConfirmButton: false, timer: 3500, timerProgressBar: true });
    }

    // ===================================================================
    // TAMBAH FIELD DINAMIS
    // ===================================================================
    $('#btn-tambah-dinamis').on('click', () => $('#modal-tambah-field').modal('show'));

    // Show/hide opsi textarea berdasarkan tipe
    $('#dyn-tipe').on('change', function() {
        const showOpsi = ['select','radio'].includes(this.value);
        $('#dyn-opsi-group').toggle(showOpsi);
    });

    $('#btn-submit-dinamis').on('click', function() {
        const label = $('#dyn-label').val().trim();
        const tipe  = $('#dyn-tipe').val();
        const req   = $('#dyn-required').is(':checked') ? 1 : 0;

        if (!label) { toastError('Label field wajib diisi.'); return; }

        let opsi = [];
        if (['select','radio'].includes(tipe)) {
            opsi = $('#dyn-opsi').val().split('\n').map(s => s.trim()).filter(s => s);
            if (opsi.length < 2) { toastError('Minimal 2 opsi harus diisi.'); return; }
        }

        showLoading();
        $.ajax({
            url    : `${BASE_URL}/${FORMULIR_ID}/fields`,
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            data   : {
                label       : label,
                tipe_field  : tipe,
                is_required : req,
                is_statis   : 0,
                opsi        : opsi.length ? opsi : undefined,
            },
            success(res) {
                hideLoading();
                if (res.status === 200 && res.data) {
                    $('#sortable-fields').append(buildFieldCard(res.data));
                    updateFieldCount();
                    toastSuccess(res.message || 'Field dinamis ditambahkan.');
                    // Reset form
                    $('#dyn-label').val('');
                    $('#dyn-tipe').val('text');
                    $('#dyn-opsi').val('');
                    $('#dyn-required').prop('checked', false);
                    $('#dyn-opsi-group').hide();
                    $('#modal-tambah-field').modal('hide');
                } else {
                    toastError(res.message || 'Gagal menambahkan field.');
                }
            },
            error(xhr) {
                hideLoading();
                toastError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    });

    // ===================================================================
    // SIMPAN URUTAN
    // ===================================================================
    $('#btn-simpan-urutan').on('click', function() {
        const ids = [];
        document.querySelectorAll('#sortable-fields .field-card').forEach(card => {
            ids.push(parseInt(card.dataset.fieldId));
        });

        if (!ids.length) { toastError('Tidak ada field untuk diurutkan.'); return; }

        showLoading();
        $.ajax({
            url    : REORDER_URL,
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            data   : { urutan_ids: ids },
            success(res) {
                hideLoading();
                if (res.status === 200) {
                    orderChanged = false;
                    document.getElementById('urutan-hint').style.display = 'none';
                    toastSuccess('Urutan field berhasil disimpan.');
                } else {
                    toastError(res.message || 'Gagal menyimpan urutan.');
                }
            },
            error(xhr) {
                hideLoading();
                toastError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    });

    // ===================================================================
    // EDIT FIELD
    // ===================================================================
    $(document).on('click', '.btn-edit-field', function() {
        const fieldId = $(this).data('field-id');
        showLoading();
        $.ajax({
            url    : `${FIELD_URL}/${fieldId}`,
            method : 'GET',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success(res) {
                hideLoading();
                if (res.status !== 200 || !res.data) { toastError(res.message); return; }
                const f = res.data;
                $('#edit-field-id').val(f.id);
                $('#edit-field-label').val(f.label);
                $('#edit-field-tipe').val(f.tipe_field);
                $('#edit-field-required').prop('checked', f.is_required);
                $('#edit-field-statis').prop('checked', f.is_statis);
                $('#edit-field-dapodik-key').val(f.dapodik_key || '');

                // Show/hide groups
                const showOpsi = ['select','radio'].includes(f.tipe_field);
                $('#edit-opsi-group').toggle(showOpsi);
                $('#edit-dapodik-group').toggle(!!f.is_statis);

                if (f.opsi && f.opsi.length) {
                    $('#edit-field-opsi').val(f.opsi.join('\n'));
                } else {
                    $('#edit-field-opsi').val('');
                }

                $('#modal-edit-field').modal('show');
            },
            error(xhr) {
                hideLoading();
                toastError(xhr.responseJSON?.message || 'Gagal memuat data field.');
            }
        });
    });

    $('#edit-field-tipe').on('change', function() {
        $('#edit-opsi-group').toggle(['select','radio'].includes(this.value));
    });
    $('#edit-field-statis').on('change', function() {
        $('#edit-dapodik-group').toggle(this.checked);
    });

    $('#btn-submit-edit-field').on('click', function() {
        const fieldId  = $('#edit-field-id').val();
        const label    = $('#edit-field-label').val().trim();
        const tipe     = $('#edit-field-tipe').val();
        const isStatis = $('#edit-field-statis').is(':checked');

        if (!label) { toastError('Label field wajib diisi.'); return; }

        let opsi = [];
        if (['select','radio'].includes(tipe)) {
            opsi = $('#edit-field-opsi').val().split('\n').map(s => s.trim()).filter(s => s);
            if (opsi.length < 2) { toastError('Minimal 2 opsi harus diisi.'); return; }
        }

        const payload = {
            label       : label,
            tipe_field  : tipe,
            is_required : $('#edit-field-required').is(':checked') ? 1 : 0,
            is_statis   : isStatis ? 1 : 0,
            dapodik_key : isStatis ? $('#edit-field-dapodik-key').val() : null,
            opsi        : opsi.length ? opsi : undefined,
        };

        showLoading();
        $.ajax({
            url    : `${FIELD_URL}/${fieldId}`,
            method : 'PUT',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            data   : payload,
            success(res) {
                hideLoading();
                if (res.status === 200 && res.data) {
                    const updatedCard = buildFieldCard(res.data);
                    $(`.field-card[data-field-id="${fieldId}"]`).replaceWith(updatedCard);
                    $('#modal-edit-field').modal('hide');
                    toastSuccess(res.message || 'Field berhasil diperbarui.');
                } else {
                    toastError(res.message || 'Gagal memperbarui field.');
                }
            },
            error(xhr) {
                hideLoading();
                toastError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
            }
        });
    });

    // ===================================================================
    // DELETE FIELD
    // ===================================================================
    $(document).on('click', '.btn-delete-field', function() {
        const fieldId = $(this).data('field-id');
        const label   = $(this).data('label');

        Swal.fire({
            title  : 'Hapus Field?',
            html   : `Field <strong>"${label}"</strong> akan dihapus permanen.<br>
                      <small class="text-danger">Pastikan tidak ada data pendaftar yang sudah mengisi field ini.</small>`,
            icon   : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#d33',
            confirmButtonText : 'Ya, Hapus!',
            cancelButtonText  : 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;
            showLoading();
            $.ajax({
                url    : `${FIELD_URL}/${fieldId}`,
                method : 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                success(res) {
                    hideLoading();
                    if (res.status === 200) {
                        // BUG FIX: Ambil dapodik_key dari data attribute field-card, bukan dari teks badge
                        // data-dapodik-key diset di buildFieldCard() saat field is_statis=true
                        const card = $(`.field-card[data-field-id="${fieldId}"]`);
                        const dapodikKey = card.attr('data-dapodik-key');
                        if (dapodikKey) {
                            markDapodikUsed(dapodikKey, false);
                        }
                        card.remove();
                        updateFieldCount();
                        toastSuccess(res.message || 'Field berhasil dihapus.');
                    } else {
                        toastError(res.message || 'Gagal menghapus field.');
                    }
                },
                error(xhr) {
                    hideLoading();
                    toastError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
                }
            });
        });
    });

    // ===================================================================
    // TOGGLE AKTIF
    // ===================================================================
    $('#btn-toggle-aktif').on('click', function() {
        const isAktif = $(this).data('aktif') == '1';
        const action  = isAktif ? 'Nonaktifkan' : 'Aktifkan';
        const icon    = isAktif ? 'question' : 'info';

        Swal.fire({
            title: `${action} Formulir?`,
            html: isAktif
                ? 'Formulir yang nonaktif tidak bisa digunakan untuk pendaftaran baru.'
                : 'Pastikan formulir sudah memiliki semua field yang diperlukan.<br><small>Formulir wajib memiliki minimal 1 field.</small>',
            icon,
            showCancelButton: true,
            confirmButtonText: action,
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;
            showLoading();
            $.ajax({
                url    : TOGGLE_URL,
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                success(res) {
                    hideLoading();
                    if (res.status === 200) {
                        toastSuccess(res.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastError(res.message);
                    }
                },
                error(xhr) {
                    hideLoading();
                    toastError(xhr.responseJSON?.message || 'Terjadi kesalahan.');
                }
            });
        });
    });

    // ===================================================================
    // PREVIEW FORMULIR
    // ===================================================================
    $('#btn-preview-formulir').on('click', function() {
        $('#modal-preview').modal('show');
        $('#preview-form-body').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Memuat preview...</div>');

        // Ambil fields dari DOM untuk preview (tidak perlu AJAX tambahan)
        const fields = [];
        document.querySelectorAll('#sortable-fields .field-card').forEach(card => {
            const id = card.dataset.fieldId;
            const label = card.querySelector('.field-label')?.textContent.trim() || '';
            const isStatis = card.querySelector('.badge.bg-info') !== null;
            const isRequired = card.querySelector('.bi-asterisk') !== null;
            const tipeBadge = card.querySelector('[class*="badge-tipe-"]');
            const tipe = tipeBadge ? tipeBadge.className.match(/badge-tipe-(\w+)/)?.[1] : 'text';
            const opsiText = card.querySelector('.text-muted.small')?.textContent || '';
            const dapodikKey = card.querySelector('.badge.bg-secondary')?.textContent.trim() || null;

            // Parse opsi dari badge text
            let opsi = [];
            if (opsiText.includes('opsi')) {
                // Will be shown as generic
            }

            fields.push({ id, label, tipe, isStatis, isRequired, dapodikKey });
        });

        // Build preview HTML
        let html = '';
        if (!fields.length) {
            html = '<div class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada field di formulir ini.</div>';
        } else {
            fields.forEach((f, i) => {
                const borderClass = f.isStatis ? 'preview-field-static' : 'preview-field-dynamic';
                const required    = f.isRequired ? '<span class="required-star">*</span>' : '';
                const sourceHint  = f.isStatis
                    ? `<span class="badge bg-info text-white ms-2" style="font-size:.65rem;">Dapodik: ${f.dapodikKey || '-'}</span>`
                    : `<span class="badge text-white ms-2" style="background:#6f42c1;font-size:.65rem;">Kustom</span>`;

                let inputHtml = '';
                switch (f.tipe) {
                    case 'textarea':
                        inputHtml = `<textarea class="form-control form-control-sm" rows="3" disabled placeholder="Masukkan ${f.label.toLowerCase()}..."></textarea>`;
                        break;
                    case 'select':
                        inputHtml = `<select class="form-select form-select-sm" disabled>
                            <option>-- Pilih ${f.label} --</option>
                        </select>`;
                        break;
                    case 'radio':
                        inputHtml = `<div class="d-flex gap-3">
                            <div class="form-check"><input class="form-check-input" type="radio" disabled>
                                <label class="form-check-label small">Opsi 1</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" disabled>
                                <label class="form-check-label small">Opsi 2</label></div>
                        </div>`;
                        break;
                    case 'file':
                        inputHtml = `<input type="file" class="form-control form-control-sm" disabled>`;
                        break;
                    case 'date':
                        inputHtml = `<input type="date" class="form-control form-control-sm" disabled>`;
                        break;
                    case 'number':
                        inputHtml = `<input type="number" class="form-control form-control-sm" disabled placeholder="0">`;
                        break;
                    default:
                        inputHtml = `<input type="text" class="form-control form-control-sm" disabled placeholder="Masukkan ${f.label.toLowerCase()}...">`;
                }

                html += `
                <div class="preview-form-group ${borderClass}">
                    <label>${i+1}. ${f.label} ${required} ${sourceHint}</label>
                    ${inputHtml}
                </div>`;
            });

            // Tombol submit simulasi
            html += `
            <hr>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button class="btn btn-secondary btn-sm" disabled>Simpan Draft</button>
                <button class="btn btn-primary btn-sm" disabled>Submit Pendaftaran</button>
            </div>`;
        }

        // Update preview body dengan transisi
        setTimeout(() => { $('#preview-form-body').html(html); }, 300);
    });

    // ===================================================================
    // DAPODIK SEARCH
    // ===================================================================
    $('#dapodik-search').on('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.dapodik-item').forEach(item => {
            const label = (item.dataset.label || '').toLowerCase();
            const key   = (item.dataset.dapodikKey || '').toLowerCase();
            item.style.display = (!q || label.includes(q) || key.includes(q)) ? '' : 'none';
        });
    });

    // ===================================================================
    // INIT
    // ===================================================================
    updateFieldCount();

    // Warn before leave if unsaved order
    window.addEventListener('beforeunload', function(e) {
        if (orderChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>
@endpush
