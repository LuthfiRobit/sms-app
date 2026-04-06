@extends('layouts.portal')

@php
    $pend      = $detail['data']['pendaftaran'] ?? null;
    $peserta   = $detail['data']['peserta'] ?? null;
    $jalur     = $detail['data']['jalur'] ?? null;
    $fvMap     = collect($detail['data']['field_values'] ?? [])->keyBy('formulir_field_id');
    $dokMap    = collect($detail['data']['dokumen'] ?? [])->keyBy('syarat_id');
    $prog      = $progress['data'] ?? ['formulir'=>['persen'=>0,'terisi'=>0,'total'=>0,'kurang'=>[]],'dokumen'=>['persen'=>0,'uploaded'=>0,'total'=>0,'kurang'=>[]],'siap_submit'=>false];
    $status    = $pend?->status ?? 'draft';
    $isDraft   = $status === 'draft';
    $totalPersen = round(($prog['formulir']['persen'] + $prog['dokumen']['persen']) / 2, 1);

    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $fmtTgl = fn($d) => $d ? ($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->day.' '.$bulanIndo[($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->month].' '.($d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d))->year : '—';

    $statusCfg = [
        'draft'        => ['color'=>'secondary','label'=>'Draft',       'icon'=>'bi-file-earmark'],
        'submit'       => ['color'=>'warning',  'label'=>'Menunggu',    'icon'=>'bi-hourglass-split'],
        'verifikasi'   => ['color'=>'info',     'label'=>'Verifikasi',  'icon'=>'bi-search'],
        'lulus'        => ['color'=>'success',  'label'=>'Lulus',       'icon'=>'bi-trophy-fill'],
        'tidak_lulus'  => ['color'=>'danger',   'label'=>'Tidak Lulus', 'icon'=>'bi-x-circle-fill'],
        'daftar_ulang' => ['color'=>'primary',  'label'=>'Daftar Ulang','icon'=>'bi-arrow-repeat'],
        'siswa_tetap'  => ['color'=>'success',  'label'=>'Siswa Tetap', 'icon'=>'bi-mortarboard-fill'],
    ];
    $st = $statusCfg[$status] ?? ['color'=>'secondary','label'=>$status,'icon'=>'bi-circle'];

    // URL routes untuk JS
    $urlSave   = route('ppdb.pendaftaran.saveFormulir', $pend?->id);
    $urlSubmit = route('ppdb.pendaftaran.submit', $pend?->id);
@endphp

@section('title', 'Pendaftaran #'.($pend?->no_pendaftaran).' — PPDB')

@section('content')

{{-- ══ BREADCRUMB ══ --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb breadcrumb-ppdb">
        <li class="breadcrumb-item"><a href="{{ route('ppdb.dashboard') }}"><i class="bi bi-house me-1"></i>Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ppdb.pendaftaran.index') }}">Pendaftaran</a></li>
        <li class="breadcrumb-item active">{{ $pend?->no_pendaftaran }}</li>
    </ol>
</nav>

{{-- ══ PAGE HEADER ══ --}}
<div class="show-header mb-4">
    <div class="show-header-main">
        <div class="show-no-block">
            <span class="show-no-label">No. Pendaftaran</span>
            <span class="show-no-value">{{ $pend?->no_pendaftaran }}</span>
        </div>
        <div class="show-meta-row">
            <span class="show-meta-item">
                <i class="bi bi-diagram-3 me-1 text-success"></i>
                <strong>{{ $jalur?->nama ?? '—' }}</strong>
            </span>
            <span class="show-meta-item">
                <i class="bi bi-calendar3 me-1 text-muted"></i>
                {{ $fmtTgl($pend?->tanggal_daftar ?? $pend?->created_at) }}
            </span>
            <span class="badge bg-{{ $st['color'] }} px-3 py-2">
                <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] }}
            </span>
        </div>
    </div>

    {{-- Progress Besar --}}
    <div class="show-progress-block">
        <div class="show-progress-top">
            <span class="show-progress-title">Kelengkapan Pengisian</span>
            <span class="show-progress-pct" id="main-pct">{{ $totalPersen }}%</span>
        </div>
        <div class="show-progress-track">
            <div class="show-progress-fill" id="main-bar" style="width:{{ $totalPersen }}%"></div>
        </div>
        <div class="show-progress-subs">
            <span><i class="bi bi-ui-checks me-1"></i>Formulir: <strong id="formulir-pct">{{ $prog['formulir']['persen'] }}%</strong></span>
            <span><i class="bi bi-paperclip me-1"></i>Dokumen: <strong id="dokumen-pct">{{ $prog['dokumen']['persen'] }}%</strong></span>
        </div>
    </div>
</div>

{{-- ══ STATUS NOTIFICATION BOXES ══ --}}
@if($isDraft && $pend?->catatan_verifikasi)
<div class="status-box status-box--warning mb-3">
    <i class="bi bi-arrow-counterclockwise fs-4 flex-shrink-0"></i>
    <div>
        <strong>Pendaftaran Dikembalikan untuk Diperbaiki</strong>
        <p class="mb-0 mt-1" style="font-size:.85rem;">Catatan admin: <em>{{ $pend->catatan_verifikasi }}</em></p>
    </div>
</div>
@elseif($isDraft)
<div class="status-box status-box--info mb-3">
    <i class="bi bi-info-circle fs-4 flex-shrink-0"></i>
    <div>
        <strong>Lengkapi Formulir dan Dokumen</strong>
        <p class="mb-0 mt-1" style="font-size:.85rem;">Setelah semua data lengkap, klik tombol <strong>Kirim Pendaftaran</strong> di bawah.</p>
    </div>
</div>
@elseif($status === 'submit')
<div class="status-box status-box--success mb-3">
    <i class="bi bi-send-check fs-4 flex-shrink-0"></i>
    <div><strong>Pendaftaran Berhasil Dikirim</strong><p class="mb-0 mt-1" style="font-size:.85rem;">Sedang menunggu verifikasi oleh admin. Pantau halaman ini secara berkala.</p></div>
</div>
@elseif($status === 'verifikasi')
<div class="status-box status-box--info mb-3">
    <i class="bi bi-search fs-4 flex-shrink-0"></i>
    <div><strong>Sedang Diverifikasi</strong><p class="mb-0 mt-1" style="font-size:.85rem;">Admin sedang memeriksa kelengkapan data dan dokumen Anda.</p></div>
</div>
@elseif(in_array($status, ['lulus','daftar_ulang','siswa_tetap','tidak_lulus']))
<div class="status-box status-box--{{ $status === 'tidak_lulus' ? 'warning' : 'success' }} mb-3">
    <i class="bi bi-{{ $status === 'tidak_lulus' ? 'x-circle' : 'trophy' }} fs-4 flex-shrink-0"></i>
    <div>
        <strong>{{ $st['label'] }}</strong>
        <p class="mb-0 mt-1" style="font-size:.85rem;">
            <a href="{{ route('ppdb.pengumuman.index') }}" class="fw-semibold">Lihat Pengumuman Resmi →</a>
        </p>
    </div>
</div>
@endif

{{-- ══ SECTION 1 — FORMULIR DINAMIS ══ --}}
@if($formulirFields->isNotEmpty())
<div class="show-section mb-4">
    <div class="show-section-header">
        <div class="show-section-title">
            <i class="bi bi-ui-checks-grid me-2 text-success"></i>Formulir Pendaftaran
        </div>
        @if($isDraft)
        <div class="autosave-indicator" id="autosave-indicator">
            <i class="bi bi-cloud-check me-1"></i><span id="autosave-text">Belum ada perubahan</span>
        </div>
        @endif
    </div>
    <div class="show-section-body">
        <form id="formulirForm" autocomplete="off">
        @foreach($formulirFields->where('tipe_field', '!=', 'file') as $field)
        @php
            $fv       = $fvMap->get($field->id);
            $curVal   = $fv?->value ?? '';
            $isStatis = $field->is_statis;
            $required = $field->is_required;
            $fieldId  = 'field_'.$field->id;
            $opsi     = $field->opsi ?? [];

            // Untuk field statis, ambil dari data Dapodik peserta via dapodik_key
            if ($isStatis && $peserta && $field->dapodik_key) {
                $dk = $field->dapodik_key;
                $staticVal = match(true) {
                    str_starts_with($dk, 'kontak.') => $peserta->kontak?->{substr($dk,7)} ?? $curVal,
                    str_starts_with($dk, 'alamat.') => $peserta->alamat?->{substr($dk,7)} ?? $curVal,
                    default => $peserta->{$dk} ?? $curVal,
                };
                $curVal = $staticVal;
            }
        @endphp
        <div class="form-field-wrapper {{ $isStatis ? 'field-statis' : '' }}" data-field-id="{{ $field->id }}">
            <label class="form-label fw-semibold" for="{{ $fieldId }}">
                {{ $field->label }}
                @if($required) <span class="text-danger ms-1">*</span> @endif
                @if($isStatis) <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size:.65rem;">Data Profil</span> @endif
            </label>

            @if($isStatis)
                {{-- READ ONLY dari Dapodik --}}
                <div class="field-statis-val">{{ $curVal ?: '—' }}</div>
                <input type="hidden" name="fields[{{ $field->id }}][formulir_field_id]" value="{{ $field->id }}">
                <input type="hidden" name="fields[{{ $field->id }}][value]" value="{{ $curVal }}">
                <div class="field-statis-hint">
                    <i class="bi bi-info-circle me-1"></i>Data dari profil —
                    <a href="{{ route('ppdb.profil.index') }}" target="_blank">ubah di halaman profil</a>
                </div>
            @elseif($field->tipe_field === 'select')
                <select id="{{ $fieldId }}" class="form-select auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                        name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}"
                        {{ !$isDraft ? 'disabled' : '' }}>
                    <option value="">— Pilih —</option>
                    @foreach($opsi as $opt)
                    @php $optVal = is_array($opt) ? ($opt['value']??'') : $opt; $optLabel = is_array($opt) ? ($opt['label']??$opt['value']??'') : $opt; @endphp
                    <option value="{{ $optVal }}" {{ $curVal == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                </select>
            @elseif($field->tipe_field === 'radio')
                <div class="d-flex flex-wrap gap-3">
                @foreach($opsi as $opt)
                @php $optVal = is_array($opt) ? ($opt['value']??'') : $opt; $optLabel = is_array($opt) ? ($opt['label']??'') : $opt; @endphp
                <div class="form-check">
                    <input class="form-check-input auto-save-field" type="radio"
                           name="fields[{{ $field->id }}][value]" id="{{ $fieldId }}_{{ $loop->index }}"
                           value="{{ $optVal }}" data-field-id="{{ $field->id }}"
                           {{ $curVal == $optVal ? 'checked' : '' }} {{ !$isDraft ? 'disabled' : '' }}>
                    <label class="form-check-label" for="{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
                </div>
                @endforeach
                </div>
            @elseif($field->tipe_field === 'textarea')
                <textarea id="{{ $fieldId }}" class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                          name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}"
                          rows="3" {{ !$isDraft ? 'readonly' : '' }}>{{ $curVal }}</textarea>
            @elseif($field->tipe_field === 'date')
                <input type="date" id="{{ $fieldId }}" class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                       name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}"
                       value="{{ $curVal }}" {{ !$isDraft ? 'readonly' : '' }}>
            @elseif($field->tipe_field === 'number')
                <input type="number" id="{{ $fieldId }}" class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                       name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}"
                       value="{{ $curVal }}" {{ !$isDraft ? 'readonly' : '' }}>
            @else
                <input type="text" id="{{ $fieldId }}" class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                       name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}"
                       value="{{ $curVal }}" {{ !$isDraft ? 'readonly' : '' }}>
            @endif

            <input type="hidden" name="fields[{{ $field->id }}][formulir_field_id]" value="{{ $field->id }}">
        </div>
        @endforeach
        </form>
    </div>
</div>
@endif

{{-- ══ SECTION 2 — UPLOAD DOKUMEN ══ --}}
@if($syarat->isNotEmpty())
<div class="show-section mb-4">
    <div class="show-section-header">
        <div class="show-section-title">
            <i class="bi bi-paperclip me-2 text-success"></i>Dokumen Persyaratan
        </div>
        <span class="text-muted" style="font-size:.78rem;">{{ $prog['dokumen']['uploaded'] }}/{{ $prog['dokumen']['total'] }} dokumen wajib diupload</span>
    </div>
    <div class="show-section-body">
        <div class="dokumen-grid">
        @foreach($syarat as $s)
        @php
            $dok      = $dokMap->get($s->id);
            $hasDok   = !empty($dok);
            $isValid  = ($dok['status_verifikasi'] ?? '') === 'valid';
            $isInvalid= ($dok['status_verifikasi'] ?? '') === 'invalid';
            $statBadge = match($dok['status_verifikasi'] ?? 'none') {
                'valid'   => ['color'=>'success', 'label'=>'Valid ✓', 'icon'=>'bi-check-circle-fill'],
                'invalid' => ['color'=>'danger',  'label'=>'Invalid ✗','icon'=>'bi-x-circle-fill'],
                'pending' => ['color'=>'warning',  'label'=>'Menunggu','icon'=>'bi-hourglass-split'],
                default   => ['color'=>'secondary','label'=>'Belum Upload','icon'=>'bi-cloud-upload'],
            };
            $uploadUrl  = route('ppdb.pendaftaran.uploadDokumen', [$pend?->id, $s->id]);
            $hapusUrl   = $hasDok ? route('ppdb.pendaftaran.hapusDokumen', [$pend?->id, $dok['id']]) : '#';
            $isPdf      = $hasDok && str_contains($dok['mime_type']??'','pdf');
        @endphp
        <div class="dok-card" id="dok-card-{{ $s->id }}" data-syarat-id="{{ $s->id }}">
            <div class="dok-card-header">
                <div class="dok-nama">{{ $s->nama }}</div>
                <div class="d-flex align-items-center gap-2">
                    @if($s->wajib)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.65rem;">Wajib</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary" style="font-size:.65rem;">Opsional</span>
                    @endif
                    <span class="badge bg-{{ $statBadge['color'] }}" id="dok-status-{{ $s->id }}" style="font-size:.7rem;">
                        <i class="bi {{ $statBadge['icon'] }} me-1"></i>{{ $statBadge['label'] }}
                    </span>
                </div>
            </div>

            @if($s->keterangan)
            <div class="dok-keterangan">{{ $s->keterangan }}</div>
            @endif

            {{-- Progress bar upload (hidden by default) --}}
            <div class="dok-upload-progress d-none" id="dok-progress-{{ $s->id }}">
                <div class="dok-progress-track"><div class="dok-progress-fill" id="dok-pbar-{{ $s->id }}" style="width:0%"></div></div>
                <span class="dok-progress-label" id="dok-pbar-pct-{{ $s->id }}">0%</span>
            </div>

            @if($hasDok)
            {{-- File sudah ada --}}
            <div class="dok-file-row" id="dok-file-row-{{ $s->id }}">
                <div class="dok-file-info">
                    <i class="bi bi-{{ $isPdf ? 'file-earmark-pdf text-danger' : 'file-earmark-image text-primary' }} fs-5"></i>
                    <div>
                        <div class="dok-file-name">{{ $dok['nama_file'] }}</div>
                        <div class="dok-file-size">{{ number_format(($dok['ukuran_file']??0)/1024,1) }} KB</div>
                    </div>
                </div>
                <div class="dok-file-actions">
                    <button type="button" class="btn btn-sm btn-outline-info"
                            onclick="previewDokumen('{{ $dok['url_file'] ?? '' }}','{{ $isPdf ? 'pdf' : 'image' }}','{{ $dok['nama_file'] }}')">
                        <i class="bi bi-eye me-1"></i>Preview
                    </button>
                    @if($isDraft && !$isValid)
                    <label class="btn btn-sm btn-outline-secondary" for="file-ganti-{{ $s->id }}">
                        <i class="bi bi-arrow-repeat me-1"></i>Ganti
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="hapusDokumen({{ $dok['id'] }}, {{ $s->id }}, '{{ addslashes($dok['nama_file']) }}')">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                    @endif
                </div>
            </div>
            @if($isDraft && !$isValid)
            <input type="file" id="file-ganti-{{ $s->id }}" class="file-input-hidden"
                   accept=".pdf,.jpg,.jpeg,.png" data-syarat-id="{{ $s->id }}" data-upload-url="{{ $uploadUrl }}">
            @endif

            @else
            {{-- Belum ada dokumen —Area drag-and-drop --}}
            @if($isDraft)
            <div class="dok-dropzone" id="dropzone-{{ $s->id }}"
                 data-syarat-id="{{ $s->id }}" data-upload-url="{{ $uploadUrl }}"
                 onclick="document.getElementById('file-input-{{ $s->id }}').click()"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleDrop(event, {{ $s->id }}, '{{ $uploadUrl }}')">
                <i class="bi bi-cloud-arrow-up dok-drop-icon"></i>
                <div class="dok-drop-text">Seret file ke sini atau <span class="text-success fw-semibold">klik untuk memilih</span></div>
                <div class="dok-drop-hint">Format: PDF, JPG, PNG · Maks 5MB</div>
                <input type="file" id="file-input-{{ $s->id }}" class="file-input-hidden"
                       accept=".pdf,.jpg,.jpeg,.png" data-syarat-id="{{ $s->id }}" data-upload-url="{{ $uploadUrl }}">
            </div>
            @else
            <div class="dok-empty-readonly">
                <i class="bi bi-dash-circle text-muted me-2"></i>Dokumen belum diupload
            </div>
            @endif
            @endif

            {{-- Error message area --}}
            <div class="dok-error d-none text-danger" id="dok-error-{{ $s->id }}" style="font-size:.8rem;margin-top:6px;"></div>
        </div>
        @endforeach
        </div>
    </div>
</div>
@endif

{{-- ══ SECTION 3 — TOMBOL AKSI ══ --}}
<div class="show-actions-bar {{ $isDraft ? '' : 'd-none' }}" id="actions-bar">
    @if($isDraft)
    <div class="show-actions-inner">
        <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-light px-4">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
        <button type="button" class="btn btn-success btn-lg px-5 fw-bold" onclick="bukaModalSubmit()">
            <i class="bi bi-send me-2"></i>Kirim Pendaftaran
        </button>
    </div>
    @endif
</div>

{{-- ══ MODAL: PREVIEW DOKUMEN ══ --}}
<div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title fw-bold" id="preview-filename">Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2" id="preview-body" style="min-height:200px;"></div>
        </div>
    </div>
</div>

{{-- ══ MODAL: KONFIRMASI SUBMIT ══ --}}
<div class="modal fade" id="modalSubmit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content modal-submit">
            <div class="modal-header modal-submit-header">
                <div class="modal-submit-icon"><i class="bi bi-send-fill"></i></div>
                <div>
                    <h5 class="modal-title fw-800 mb-0">Konfirmasi Pengiriman</h5>
                    <p class="text-muted mb-0" style="font-size:.78rem;">Pastikan semua data sudah benar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                {{-- Checklist --}}
                <div class="submit-checklist">
                    <div class="checklist-item" id="ck-formulir">
                        <span class="ck-icon" id="ck-formulir-icon"><i class="bi bi-circle text-muted"></i></span>
                        <div class="flex-1">
                            <div class="ck-label">Formulir</div>
                            <div class="ck-sub" id="ck-formulir-sub">Memuat...</div>
                        </div>
                        <span class="ck-pct" id="ck-formulir-pct"></span>
                    </div>
                    <div class="checklist-item" id="ck-dokumen">
                        <span class="ck-icon" id="ck-dokumen-icon"><i class="bi bi-circle text-muted"></i></span>
                        <div class="flex-1">
                            <div class="ck-label">Dokumen Persyaratan</div>
                            <div class="ck-sub" id="ck-dokumen-sub">Memuat...</div>
                        </div>
                        <span class="ck-pct" id="ck-dokumen-pct"></span>
                    </div>
                </div>

                {{-- Warning kekurangan --}}
                <div id="submit-warning-box" class="submit-warning-box d-none">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    <div>
                        <strong>Ada kelengkapan yang belum terpenuhi:</strong>
                        <ul class="mb-0 mt-1" id="submit-kekurangan-list" style="font-size:.82rem;padding-left:16px;"></ul>
                    </div>
                </div>

                <div class="submit-notice">
                    <i class="bi bi-shield-check text-info me-2"></i>
                    <small>Setelah dikirim, data formulir <strong>tidak dapat diubah</strong>. Periksa kembali sebelum mengirim.</small>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Kembali Periksa
                </button>
                <button type="button" class="btn btn-success px-4 fw-bold" id="btn-confirm-submit" onclick="doSubmit()">
                    <i class="bi bi-send me-2"></i>Ya, Kirim Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ TOAST NOTIFIKASI ══ --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="ppdbToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="ppdbToastMsg"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── Breadcrumb ── */
.breadcrumb-ppdb { background:none; padding:0; margin:0; font-size:.8125rem; }
.breadcrumb-ppdb .breadcrumb-item a { color:#16a34a; text-decoration:none; }
.breadcrumb-ppdb .breadcrumb-item.active { color:#6b7280; }

/* ── Page Header ── */
.show-header { background:#fff; border:1.5px solid #e5e7eb; border-radius:16px; padding:22px 24px; box-shadow:0 2px 10px rgba(0,0,0,.05); }
.show-no-label  { display:block; font-size:.67rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; }
.show-no-value  { font-family:'Courier New',monospace; font-size:1.2rem; font-weight:800; color:#111827; letter-spacing:1.5px; }
.show-meta-row  { display:flex; align-items:center; flex-wrap:wrap; gap:12px; margin-top:8px; }
.show-meta-item { display:inline-flex; align-items:center; font-size:.8375rem; color:#374151; }

/* Progress Block */
.show-progress-block { margin-top:18px; padding-top:18px; border-top:1px solid #f3f4f6; }
.show-progress-top   { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
.show-progress-title { font-size:.825rem; font-weight:700; color:#374151; }
.show-progress-pct   { font-size:1rem; font-weight:800; color:#16a34a; }
.show-progress-track { height:8px; background:#f3f4f6; border-radius:4px; overflow:hidden; margin-bottom:6px; }
.show-progress-fill  { height:100%; background:linear-gradient(90deg,#16a34a,#22c55e); border-radius:4px; transition:width .8s ease; }
.show-progress-subs  { display:flex; gap:20px; font-size:.78rem; color:#6b7280; }
.show-progress-subs strong { color:#111827; }

/* Status Boxes */
.status-box { display:flex; align-items:flex-start; gap:14px; border-radius:14px; padding:16px 20px; margin-bottom:0; }
.status-box--info    { background:#eff6ff; border:1.5px solid #93c5fd; color:#1e40af; }
.status-box--success { background:#f0fdf4; border:1.5px solid #86efac; color:#15803d; }
.status-box--warning { background:#fffbeb; border:1.5px solid #fcd34d; border-left:5px solid #f59e0b; color:#92400e; }

/* ── Show Section ── */
.show-section { background:#fff; border:1.5px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.04); }
.show-section-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
.show-section-title  { font-weight:700; color:#111827; font-size:.9rem; display:flex; align-items:center; }
.show-section-body   { padding:20px 24px; }

/* ── Autosave ── */
.autosave-indicator { font-size:.75rem; color:#9ca3af; display:flex; align-items:center; gap:4px; }
.autosave-indicator.saving { color:#f59e0b; }
.autosave-indicator.saved  { color:#16a34a; }
.autosave-indicator.error  { color:#ef4444; }

/* ── Form Fields ── */
.form-field-wrapper { margin-bottom:18px; }
.field-statis { background:#fafafa; border:1px dashed #e5e7eb; border-radius:10px; padding:14px 16px; }
.field-statis-val { font-size:.9rem; font-weight:600; color:#111827; margin-bottom:4px; }
.field-statis-hint { font-size:.72rem; color:#9ca3af; }
.field-statis-hint a { color:#16a34a; }
.readonly-field { background:#f9fafb!important; color:#6b7280; cursor:not-allowed; }
.form-control:focus, .form-select:focus { border-color:#86efac; box-shadow:0 0 0 3px rgba(22,163,74,.12); }

/* ── Dokumen Grid ── */
.dokumen-grid { display:flex; flex-direction:column; gap:14px; }
.dok-card { border:1.5px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
.dok-card-header { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; padding:14px 16px; background:#f9fafb; border-bottom:1px solid #f3f4f6; flex-wrap:wrap; }
.dok-nama { font-size:.875rem; font-weight:700; color:#111827; }
.dok-keterangan { font-size:.78rem; color:#9ca3af; padding:8px 16px 0; }

/* Progress upload */
.dok-upload-progress { display:flex; align-items:center; gap:10px; padding:10px 16px; }
.dok-progress-track { flex:1; height:5px; background:#f3f4f6; border-radius:3px; overflow:hidden; }
.dok-progress-fill { height:100%; background:linear-gradient(90deg,#16a34a,#22c55e); border-radius:3px; transition:width .2s; }
.dok-progress-label { font-size:.75rem; font-weight:700; color:#16a34a; min-width:32px; text-align:right; }

/* File row */
.dok-file-row { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; flex-wrap:wrap; gap:10px; }
.dok-file-info { display:flex; align-items:center; gap:10px; }
.dok-file-name { font-size:.8375rem; font-weight:600; color:#111827; }
.dok-file-size { font-size:.72rem; color:#9ca3af; }
.dok-file-actions { display:flex; gap:6px; flex-wrap:wrap; }
.dok-empty-readonly { padding:14px 16px; font-size:.8rem; color:#9ca3af; }

/* Dropzone */
.dok-dropzone { margin:12px 16px; border:2px dashed #d1fae5; border-radius:10px; padding:24px 16px; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; }
.dok-dropzone:hover, .dok-dropzone.dragover { border-color:#16a34a; background:#f0fdf4; }
.dok-drop-icon { font-size:2rem; color:#86efac; display:block; margin-bottom:8px; }
.dok-drop-text { font-size:.8375rem; color:#374151; margin-bottom:4px; }
.dok-drop-hint { font-size:.72rem; color:#9ca3af; }
.file-input-hidden { display:none!important; }

/* ── Actions Bar ── */
.show-actions-bar { position:sticky; bottom:0; z-index:100; background:rgba(255,255,255,.95); backdrop-filter:blur(8px); border-top:1px solid #e5e7eb; margin:0 -20px; padding:14px 20px; }
.show-actions-inner { display:flex; align-items:center; justify-content:space-between; max-width:100%; }
@media(max-width:576px) { .show-actions-bar { position:fixed; bottom:0; left:0; right:0; margin:0; } }

/* ── Modal Submit ── */
.modal-submit { border-radius:18px; overflow:hidden; border:none; box-shadow:0 24px 64px rgba(0,0,0,.15); }
.modal-submit-header { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-bottom:1px solid #bbf7d0; padding:18px 22px; display:flex; align-items:center; gap:14px; }
.modal-submit-icon { width:42px; height:42px; background:linear-gradient(135deg,#16a34a,#059669); border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:#fff; flex-shrink:0; }
.submit-checklist { display:flex; flex-direction:column; gap:10px; margin-bottom:14px; }
.checklist-item { display:flex; align-items:center; gap:12px; background:#f9fafb; border:1px solid #f3f4f6; border-radius:10px; padding:12px 14px; }
.ck-icon   { font-size:1.1rem; flex-shrink:0; }
.ck-label  { font-size:.85rem; font-weight:700; color:#111827; }
.ck-sub    { font-size:.75rem; color:#9ca3af; margin-top:2px; }
.ck-pct    { font-size:.875rem; font-weight:800; color:#16a34a; margin-left:auto; }
.submit-warning-box { display:flex; align-items:flex-start; gap:10px; background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; padding:12px 14px; margin-bottom:12px; font-size:.82rem; color:#92400e; }
.submit-notice { display:flex; align-items:center; gap:8px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:10px 14px; font-size:.79rem; color:#1e40af; margin-top:4px; }
.flex-1 { flex:1; }
</style>
@endpush

@push('scripts')
<script>
// ══════════════════════════════════════════════════════
// CONFIG
// ══════════════════════════════════════════════════════
const PENDAFTARAN_ID = {{ $pend?->id ?? 'null' }};
const URL_SAVE       = "{{ $urlSave }}";
const URL_SUBMIT     = "{{ $urlSubmit }}";
const IS_DRAFT       = {{ $isDraft ? 'true' : 'false' }};
const _TOKEN         = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

// ══════════════════════════════════════════════════════
// AUTO-SAVE FORMULIR
// ══════════════════════════════════════════════════════
let saveTimer = null;

function setAutosaveStatus(state, msg) {
    const ind  = document.getElementById('autosave-indicator');
    const text = document.getElementById('autosave-text');
    if (!ind || !text) return;
    ind.className = 'autosave-indicator ' + state;
    const icons = { saving:'bi-arrow-repeat', saved:'bi-cloud-check', error:'bi-cloud-slash' };
    ind.querySelector('i').className = 'bi ' + (icons[state] || 'bi-cloud') + ' me-1';
    text.textContent = msg;
}

function collectFields() {
    const fields = [];
    document.querySelectorAll('#formulirForm [data-field-id]').forEach(el => {
        if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) return;
        if (el.tagName === 'INPUT' && el.type === 'hidden') return;
        const fid = parseInt(el.dataset.fieldId);
        if (!fid) return;
        if (fields.find(f => f.formulir_field_id === fid)) return;
        fields.push({ formulir_field_id: fid, value: el.value });
    });
    return fields;
}

async function doAutoSave() {
    if (!IS_DRAFT) return;
    const fields = collectFields();
    if (!fields.length) return;
    setAutosaveStatus('saving', 'Menyimpan...');
    try {
        const res  = await fetch(URL_SAVE, {
            method: 'PUT',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':_TOKEN, 'Accept':'application/json' },
            body: JSON.stringify({ fields })
        });
        const data = await res.json();
        if (data.success) {
            const now = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
            setAutosaveStatus('saved', 'Disimpan ' + now);
            if (data.progress) updateProgressUI(data.progress);
        } else {
            setAutosaveStatus('error', 'Gagal menyimpan');
        }
    } catch(e) {
        setAutosaveStatus('error', 'Gagal menyimpan');
    }
}

document.querySelectorAll('.auto-save-field').forEach(el => {
    const evt = (el.tagName === 'SELECT' || el.type === 'radio' || el.type === 'checkbox') ? 'change' : 'blur';
    el.addEventListener(evt, () => {
        if (!IS_DRAFT) return;
        clearTimeout(saveTimer);
        setAutosaveStatus('saving', 'Menunggu...');
        saveTimer = setTimeout(doAutoSave, 1500);
    });
});

function updateProgressUI(prog) {
    if (!prog) return;
    const fp = prog.formulir?.persen ?? null;
    if (fp !== null) {
        document.getElementById('formulir-pct').textContent = fp + '%';
        const total = Math.round((fp + parseFloat(document.getElementById('dokumen-pct').textContent)) / 2);
        document.getElementById('main-pct').textContent = total + '%';
        document.getElementById('main-bar').style.width = total + '%';
    }
}

// ══════════════════════════════════════════════════════
// UPLOAD DOKUMEN
// ══════════════════════════════════════════════════════
function handleDrop(event, syaratId, uploadUrl) {
    event.preventDefault();
    document.getElementById('dropzone-' + syaratId)?.classList.remove('dragover');
    const file = event.dataTransfer.files[0];
    if (file) uploadFile(file, syaratId, uploadUrl);
}

// File input change handler (delegated)
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('file-input-hidden') && e.target.type !== 'file') return;
    const file = e.target.files[0];
    if (!file) return;
    const syaratId  = e.target.dataset.syaratId;
    const uploadUrl = e.target.dataset.uploadUrl;
    if (syaratId && uploadUrl) uploadFile(file, syaratId, uploadUrl);
});

function uploadFile(file, syaratId, uploadUrl) {
    if (file.size > 5 * 1024 * 1024) { showDokError(syaratId, 'Ukuran file melebihi 5MB.'); return; }
    const allowed = ['application/pdf','image/jpeg','image/jpg','image/png'];
    if (!allowed.includes(file.type)) { showDokError(syaratId, 'Format tidak didukung. Gunakan PDF, JPG, atau PNG.'); return; }

    clearDokError(syaratId);
    showUploadProgress(syaratId, true);

    const fd = new FormData();
    fd.append('dokumen', file);
    fd.append('_token', _TOKEN);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl);
    xhr.setRequestHeader('X-CSRF-TOKEN', _TOKEN);
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            setDokProgress(syaratId, pct);
        }
    };

    xhr.onload = () => {
        showUploadProgress(syaratId, false);
        try {
            const r = JSON.parse(xhr.responseText);
            if (r.success && r.dokumen) {
                renderDokumenUploaded(syaratId, r.dokumen);
                showToast('success', r.message || 'Dokumen berhasil diupload.');
                refreshDokumenProgress();
            } else {
                showDokError(syaratId, r.message || 'Gagal upload.');
            }
        } catch(e) { showDokError(syaratId, 'Terjadi kesalahan.'); }
    };
    xhr.onerror = () => { showUploadProgress(syaratId, false); showDokError(syaratId, 'Upload gagal. Periksa koneksi Anda.'); };
    xhr.send(fd);
}

function showUploadProgress(syaratId, show) {
    const el = document.getElementById('dok-progress-' + syaratId);
    if (el) { el.classList.toggle('d-none', !show); if (!show) setDokProgress(syaratId, 0); }
}

function setDokProgress(syaratId, pct) {
    const bar  = document.getElementById('dok-pbar-' + syaratId);
    const lbl  = document.getElementById('dok-pbar-pct-' + syaratId);
    if (bar) bar.style.width = pct + '%';
    if (lbl) lbl.textContent = pct + '%';
}

function renderDokumenUploaded(syaratId, dok) {
    const card     = document.getElementById('dok-card-' + syaratId);
    const isPdf    = (dok.mime_type || '').includes('pdf');
    const sizeKb   = ((dok.ukuran_file || 0) / 1024).toFixed(1);

    // Remove dropzone
    const dz = document.getElementById('dropzone-' + syaratId);
    if (dz) dz.remove();

    // Update status badge
    const badge = document.getElementById('dok-status-' + syaratId);
    if (badge) badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Menunggu';

    // Inject file row
    const existing = document.getElementById('dok-file-row-' + syaratId);
    const rowHtml = `
    <div class="dok-file-row" id="dok-file-row-${syaratId}">
        <div class="dok-file-info">
            <i class="bi bi-file-earmark-${isPdf?'pdf text-danger':'image text-primary'} fs-5"></i>
            <div>
                <div class="dok-file-name">${dok.nama_file}</div>
                <div class="dok-file-size">${sizeKb} KB</div>
            </div>
        </div>
        <div class="dok-file-actions">
            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewDokumen('${dok.url}','${isPdf?'pdf':'image'}','${dok.nama_file}')">
                <i class="bi bi-eye me-1"></i>Preview
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusDokumen(${dok.id},${syaratId},'${dok.nama_file}')">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
        </div>
    </div>`;
    if (existing) existing.outerHTML = rowHtml;
    else card.insertAdjacentHTML('beforeend', rowHtml);
}

async function hapusDokumen(dokumenId, syaratId, namaFile) {
    if (!confirm(`Hapus dokumen "${namaFile}"?`)) return;
    const url = `/ppdb/pendaftaran/${PENDAFTARAN_ID}/dokumen/${dokumenId}`;
    try {
        const res  = await fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN':_TOKEN, 'Accept':'application/json', 'Content-Type':'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('dok-file-row-' + syaratId);
            if (row) row.remove();
            const badge = document.getElementById('dok-status-' + syaratId);
            if (badge) badge.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Belum Upload';
            // Inject dropzone kembali
            const card = document.getElementById('dok-card-' + syaratId);
            const uploadUrl = `/ppdb/pendaftaran/${PENDAFTARAN_ID}/dokumen/${syaratId}`;
            card.insertAdjacentHTML('beforeend', `
            <div class="dok-dropzone" id="dropzone-${syaratId}"
                 data-syarat-id="${syaratId}" data-upload-url="${uploadUrl}"
                 onclick="document.getElementById('file-input-${syaratId}').click()"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleDrop(event,${syaratId},'${uploadUrl}')">
                <i class="bi bi-cloud-arrow-up dok-drop-icon"></i>
                <div class="dok-drop-text">Seret file ke sini atau <span class="text-success fw-semibold">klik untuk memilih</span></div>
                <div class="dok-drop-hint">Format: PDF, JPG, PNG · Maks 5MB</div>
                <input type="file" id="file-input-${syaratId}" class="file-input-hidden"
                       accept=".pdf,.jpg,.jpeg,.png" data-syarat-id="${syaratId}" data-upload-url="${uploadUrl}">
            </div>`);
            showToast('success', data.message || 'Dokumen dihapus.');
            refreshDokumenProgress();
        } else { showToast('error', data.message || 'Gagal menghapus.'); }
    } catch(e) { showToast('error', 'Terjadi kesalahan.'); }
}

async function refreshDokumenProgress() {
    // Update progress dokumen dari server (lightweight)
    const res  = await fetch(URL_SAVE.replace('formulir','formulir'), { method:'HEAD' }).catch(()=>null);
    // Reload halaman progress saja via simple GET ke API tidak ada,
    // cukup update counter secara visual (count elemen)
    const total    = document.querySelectorAll('.dok-card').length;
    const uploaded = document.querySelectorAll('.dok-file-row').length;
    const pct      = total > 0 ? Math.round((uploaded / total) * 100) : 100;
    document.getElementById('dokumen-pct').textContent = pct + '%';
    const fp    = parseFloat(document.getElementById('formulir-pct').textContent) || 0;
    const main  = Math.round((fp + pct) / 2);
    document.getElementById('main-pct').textContent = main + '%';
    document.getElementById('main-bar').style.width = main + '%';
}

function showDokError(syaratId, msg) {
    const el = document.getElementById('dok-error-' + syaratId);
    if (el) { el.textContent = '\u26a0 ' + msg; el.classList.remove('d-none'); }
}
function clearDokError(syaratId) {
    const el = document.getElementById('dok-error-' + syaratId);
    if (el) { el.textContent = ''; el.classList.add('d-none'); }
}

// ══════════════════════════════════════════════════════
// PREVIEW DOKUMEN
// ══════════════════════════════════════════════════════
function previewDokumen(url, type, nama) {
    document.getElementById('preview-filename').textContent = nama;
    const body = document.getElementById('preview-body');
    body.innerHTML = type === 'pdf'
        ? `<iframe src="${url}" style="width:100%;height:70vh;border:none;"></iframe>`
        : `<div class="text-center p-2"><img src="${url}" class="img-fluid" style="max-height:70vh;border-radius:8px;"></div>`;
    new bootstrap.Modal(document.getElementById('modalPreview')).show();
}

// ══════════════════════════════════════════════════════
// SUBMIT PENDAFTARAN
// ══════════════════════════════════════════════════════
async function bukaModalSubmit() {
    // Simpan dulu sebelum buka modal
    await doAutoSave();

    // Ambil progress terbaru dari DOM
    const fPct = parseFloat(document.getElementById('formulir-pct').textContent) || 0;
    const dPct = parseFloat(document.getElementById('dokumen-pct').textContent)  || 0;

    // Update checklist
    const setCheck = (id, pct, label) => {
        const ok = pct >= 100;
        document.getElementById('ck-'+id+'-icon').innerHTML =
            ok ? '<i class="bi bi-check-circle-fill text-success"></i>'
               : '<i class="bi bi-exclamation-circle-fill text-warning"></i>';
        document.getElementById('ck-'+id+'-pct').textContent = pct + '%';
        document.getElementById('ck-'+id+'-sub').textContent = ok ? 'Lengkap' : 'Belum lengkap';
        document.getElementById('ck-'+id).style.borderColor = ok ? '#86efac' : '#fcd34d';
    };
    setCheck('formulir', fPct);
    setCheck('dokumen',  dPct);

    // Warning kekurangan
    const warnBox = document.getElementById('submit-warning-box');
    const warnList = document.getElementById('submit-kekurangan-list');
    const kurang = [];
    if (fPct < 100) { const n = parseInt(document.getElementById('formulir-pct').textContent); kurang.push('Formulir: ' + fPct + '% — belum semua field wajib terisi'); }
    if (dPct < 100) { kurang.push('Dokumen: ' + dPct + '% — belum semua dokumen wajib diupload'); }

    if (kurang.length) {
        warnBox.classList.remove('d-none');
        warnList.innerHTML = kurang.map(k => `<li>${k}</li>`).join('');
    } else {
        warnBox.classList.add('d-none');
    }

    new bootstrap.Modal(document.getElementById('modalSubmit')).show();
}

async function doSubmit() {
    const btn = document.getElementById('btn-confirm-submit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    try {
        const res  = await fetch(URL_SUBMIT, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN':_TOKEN, 'Accept':'application/json', 'Content-Type':'application/json' }
        });
        const data = await res.json();
        bootstrap.Modal.getInstance(document.getElementById('modalSubmit'))?.hide();
        if (data.success) {
            showToast('success', data.message || 'Pendaftaran berhasil dikirim!');
            setTimeout(() => window.location.reload(), 1800);
        } else {
            showToast('error', data.message || 'Gagal mengirim pendaftaran.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-2"></i>Ya, Kirim Sekarang';
        }
    } catch(e) {
        showToast('error', 'Terjadi kesalahan. Coba lagi.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Ya, Kirim Sekarang';
    }
}

// ══════════════════════════════════════════════════════
// TOAST
// ══════════════════════════════════════════════════════
function showToast(type, msg) {
    const toast = document.getElementById('ppdbToast');
    const msgEl = document.getElementById('ppdbToastMsg');
    toast.className = 'toast align-items-center border-0 text-white bg-' + (type === 'success' ? 'success' : 'danger');
    msgEl.textContent = msg;
    new bootstrap.Toast(toast, { delay: 4000 }).show();
}

// Animate progress on load
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('main-bar');
    if (bar) { const w = bar.style.width; bar.style.width = '0'; setTimeout(()=>{ bar.style.width = w; }, 300); }
});
</script>
@endpush
