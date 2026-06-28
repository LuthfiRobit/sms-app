@extends('admin.layouts.app')
@section('title', 'Detail Program Kerja')

@push('styles')
<style>
    .info-label { font-size: .75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: .15rem; }
    .info-value { font-size: .92rem; color: #222; font-weight: 500; }
    .stat-mini  { text-align: center; padding: .75rem 1rem; border-radius: .5rem; }
    .stat-mini .stat-val  { font-size: 1.6rem; font-weight: 700; line-height: 1; }
    .stat-mini .stat-label{ font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; margin-top: .2rem; }
    .timeline-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 2px; }
    .timeline-label{ font-size: .7rem; text-align: center; color: #888; padding: 2px 0; }
    .timeline-bar  { border-radius: 3px; height: 18px; cursor: default; }
    .tbl-kegiatan th { font-size: .8rem; background: #f8f9fa; }
    .tbl-kegiatan td { font-size: .85rem; vertical-align: middle; }
    .persen-display { font-size: 1.3rem; font-weight: 700; }
</style>
@endpush

@section('content')
@php
    $statusMap = \App\Models\ProgramKerja\ProgramKerja::statusConfig();
    [$statusColor, $statusLabel] = isset($statusMap[$prog->status])
        ? [$statusMap[$prog->status]['class'], $statusMap[$prog->status]['label']]
        : ['secondary', ucfirst($prog->status)];

    $bidangMap   = \App\Models\ProgramKerja\ProgramKerja::bidangConfig();
    $bidangLabel = $bidangMap[$prog->bidang] ?? ucfirst($prog->bidang);

    $bidangColors = [
        'kesiswaan' => 'primary',
        'sarpras'   => 'warning',
        'humas'     => 'info',
        'kurikulum' => 'success',
        'umum'      => 'secondary',
    ];
    $bidangColor = $bidangColors[$prog->bidang] ?? 'secondary';

    $isDraft      = $prog->status === 'draft';
    $isDiajukan   = $prog->status === 'diajukan';
    $isDiverifikasi = $prog->status === 'diverifikasi';
    $isDitolak    = $prog->status === 'ditolak';
    $isDisetujui  = $prog->status === 'disetujui';
    $isAktif      = $prog->status === 'aktif';
    $isSelesai    = $prog->status === 'selesai';
    $canEdit      = in_array($prog->status, ['draft', 'ditolak']);
    $canAddKegiatan = in_array($prog->status, ['draft', 'disetujui', 'aktif']);
    $canRealisasi = in_array($prog->status, ['disetujui', 'aktif']);
@endphp

{{-- ── Header Card ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="mb-0 text-primary">
                                <i class="bi bi-briefcase-fill me-1"></i>{{ $prog->nama_program }}
                            </h5>
                            <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            <span class="badge bg-{{ $bidangColor }} bg-opacity-75">{{ $bidangLabel }}</span>
                        </div>
                        <small class="text-muted">
                            {{ optional($prog->lembaga)->nama ?? '-' }} &mdash;
                            {{ optional($prog->tahunPelajaran)->nama ?? '-' }}
                        </small>
                    </div>
                    <div class="flex-shrink-0 d-flex flex-wrap gap-2">
                        {{-- Draft: Submit, Edit, Hapus --}}
                        @if($isDraft)
                            <button class="btn btn-sm btn-success" onclick="submitProgram({{ $prog->id }})">
                                <i class="bi bi-send me-1"></i>Ajukan
                            </button>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="hapusProgram({{ $prog->id }})">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        @endif

                        {{-- Ditolak: Submit Ulang --}}
                        @if($isDitolak)
                            <button class="btn btn-sm btn-success" onclick="submitProgram({{ $prog->id }})">
                                <i class="bi bi-send me-1"></i>Ajukan Ulang
                            </button>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                        @endif

                        {{-- Diajukan: Verifikasi, Tolak, Tarik --}}
                        @if($isDiajukan)
                            <button class="btn btn-sm btn-info text-white" onclick="verifikasiProgram({{ $prog->id }})">
                                <i class="bi bi-check-circle me-1"></i>Verifikasi
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="tolakProgram({{ $prog->id }})">
                                <i class="bi bi-x-circle me-1"></i>Tolak
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="tarikProgram({{ $prog->id }})">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Tarik
                            </button>
                        @endif

                        {{-- Diverifikasi: Setujui, Tolak --}}
                        @if($isDiverifikasi)
                            <button class="btn btn-sm btn-success" onclick="approvalProgram({{ $prog->id }})">
                                <i class="bi bi-patch-check me-1"></i>Setujui
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="tolakProgram({{ $prog->id }})">
                                <i class="bi bi-x-circle me-1"></i>Tolak
                            </button>
                        @endif

                        {{-- Disetujui/Aktif/Selesai: Cetak PDF --}}
                        @if(in_array($prog->status, ['disetujui','aktif','selesai']))
                            <a href="{{ route('admin.program-kerja.cetak', $prog->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-printer me-1"></i>Cetak PDF
                            </a>
                        @endif

                        <a href="{{ route('admin.program-kerja.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Rejection alert --}}
                @if($isDitolak && $prog->catatan_penolakan)
                    <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-x-circle-fill mt-1"></i>
                        <div>
                            <strong>Program Kerja Ditolak:</strong> {{ $prog->catatan_penolakan }}
                        </div>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="info-label">Lembaga</div>
                        <div class="info-value">{{ optional($prog->lembaga)->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Tahun Pelajaran</div>
                        <div class="info-value">{{ optional($prog->tahunPelajaran)->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Bidang</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $bidangColor }}">{{ $bidangLabel }}</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ $statusLabel }}</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Diajukan Pada</div>
                        <div class="info-value">
                            {{ $prog->diajukan_at ? $prog->diajukan_at->format('d M Y') : '—' }}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">Disetujui Pada</div>
                        <div class="info-value">
                            {{ $prog->disetujui_at ? $prog->disetujui_at->format('d M Y') : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Progress Summary ──────────────────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-md-2">
                        <div class="stat-mini bg-light">
                            <div class="stat-val text-dark">{{ $summary['total'] }}</div>
                            <div class="stat-label text-muted">Total Kegiatan</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-mini" style="background:#d1fae5">
                            <div class="stat-val text-success">{{ $summary['selesai'] }}</div>
                            <div class="stat-label text-success">Selesai</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-mini" style="background:#fef3c7">
                            <div class="stat-val text-warning">{{ $summary['proses'] }}</div>
                            <div class="stat-label text-warning">Sedang Berjalan</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-mini" style="background:#f1f5f9">
                            <div class="stat-val text-secondary">{{ $summary['belum'] }}</div>
                            <div class="stat-label text-secondary">Belum Mulai</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0">
                                <span class="persen-display text-primary">{{ $summary['progress_persen'] }}%</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="progress" style="height: 14px; border-radius: 8px;">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $summary['progress_persen'] }}%; border-radius: 8px;
                                                background: linear-gradient(90deg, #10b981, #059669) !important;">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ $summary['selesai'] }} dari {{ $summary['total'] }} kegiatan selesai
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Anggaran summary --}}
                @if($summary['total'] > 0)
                    <hr class="my-2">
                    <div class="row g-2 text-center">
                        <div class="col-md-4">
                            <div class="info-label">Total Anggaran</div>
                            <div class="info-value">Rp {{ number_format($summary['total_anggaran'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Realisasi Anggaran</div>
                            <div class="info-value">Rp {{ number_format($summary['realisasi_anggaran'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Sisa Anggaran</div>
                            <div class="info-value">
                                Rp {{ number_format(($summary['total_anggaran'] ?? 0) - ($summary['realisasi_anggaran'] ?? 0), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Tujuan & Deskripsi ────────────────────────────────────────────────── --}}
@if($prog->tujuan || $prog->deskripsi)
    <div class="row g-3 mb-3">
        <div class="col-xl-12">
            <div class="accordion" id="acc-info">
                <div class="accordion-item border-0 shadow-sm">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc-info-body">
                            <i class="bi bi-info-circle me-2"></i>Informasi Program
                        </button>
                    </h2>
                    <div id="acc-info-body" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="row g-3">
                                @if($prog->tujuan)
                                    <div class="col-md-6">
                                        <div class="info-label">Tujuan</div>
                                        <div>{{ $prog->tujuan }}</div>
                                    </div>
                                @endif
                                @if($prog->deskripsi)
                                    <div class="col-md-6">
                                        <div class="info-label">Deskripsi</div>
                                        <div>{{ $prog->deskripsi }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── Tabel Kegiatan ────────────────────────────────────────────────────── --}}
<div class="row g-3">
    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-list-task me-2"></i>Daftar Kegiatan
                        </h6>
                    </div>
                    @if($canAddKegiatan)
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah-kegiatan">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kegiatan
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div id="kegiatan-container">
                    @include('admin.program-kerja._kegiatan-table', [
                        'kegiatan'       => $prog->kegiatan,
                        'canEdit'        => $canEdit,
                        'canRealisasi'   => $canRealisasi,
                        'canAddKegiatan' => $canAddKegiatan,
                        'programId'      => $prog->id,
                        'programStatus'  => $prog->status,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Timeline Bulan ────────────────────────────────────────────────────── --}}
@if($prog->kegiatan->count() > 0)
    <div class="row g-3 mt-1">
        <div class="col-xl-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 text-primary">
                        <i class="bi bi-calendar3 me-2"></i>Timeline Kegiatan (per Bulan)
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $bulanNama = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                        $kegiatanStatusColors = [
                            'belum'      => '#94a3b8',
                            'proses'     => '#f59e0b',
                            'selesai'    => '#10b981',
                            'dibatalkan' => '#f87171',
                        ];
                    @endphp
                    {{-- Header months --}}
                    <div class="timeline-grid mb-1">
                        @foreach($bulanNama as $b)
                            <div class="timeline-label">{{ $b }}</div>
                        @endforeach
                    </div>
                    {{-- Bars per kegiatan --}}
                    @foreach($prog->kegiatan as $k)
                        @php
                            $barColor  = $kegiatanStatusColors[$k->status_kegiatan] ?? '#94a3b8';
                            $mulai     = max(1, (int)($k->bulan_mulai ?? 1)) - 1;
                            $selesaiK  = min(12, (int)($k->bulan_selesai ?? 1));
                            $span      = max(1, $selesaiK - $mulai);
                        @endphp
                        <div class="d-flex align-items-center mb-1 gap-2">
                            <div style="font-size:.72rem; width:180px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                 title="{{ $k->nama_kegiatan }}">
                                {{ $k->nama_kegiatan }}
                            </div>
                            <div style="flex:1; position:relative;">
                                <div class="timeline-grid" style="position:relative;">
                                    @for($m = 1; $m <= 12; $m++)
                                        @php
                                            $inRange = $m >= $k->bulan_mulai && $m <= $k->bulan_selesai;
                                        @endphp
                                        <div class="timeline-bar"
                                             style="background: {{ $inRange ? $barColor : 'transparent' }}; opacity: {{ $inRange ? '0.85' : '1' }};">
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="d-flex gap-3 mt-2 flex-wrap">
                        <small><span style="display:inline-block;width:12px;height:12px;background:#94a3b8;border-radius:2px;"></span> Belum Mulai</small>
                        <small><span style="display:inline-block;width:12px;height:12px;background:#f59e0b;border-radius:2px;"></span> Sedang Berjalan</small>
                        <small><span style="display:inline-block;width:12px;height:12px;background:#10b981;border-radius:2px;"></span> Selesai</small>
                        <small><span style="display:inline-block;width:12px;height:12px;background:#f87171;border-radius:2px;"></span> Dibatalkan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODALS                                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}

{{-- ── Modal Submit ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-submit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-send me-1"></i>Ajukan ke Verifikasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Program yang telah diajukan tidak dapat diedit hingga selesai diproses.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="submit-catatan" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-konfirmasi-submit">
                    <i class="bi bi-send me-1"></i>Ajukan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Edit Program ────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-edit">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i>Edit Program Kerja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bidang <span class="text-danger">*</span></label>
                            <select class="form-select" name="bidang" id="edit-bidang" required>
                                @foreach(\App\Models\ProgramKerja\ProgramKerja::bidangConfig() as $key => $label)
                                    <option value="{{ $key }}" {{ $prog->bidang === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Program <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_program" id="edit-nama" required
                                   value="{{ $prog->nama_program }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Tujuan <small class="text-muted fw-normal">(opsional)</small></label>
                            <textarea class="form-control" name="tujuan" id="edit-tujuan" rows="2">{{ $prog->tujuan }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi <small class="text-muted fw-normal">(opsional)</small></label>
                            <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="2">{{ $prog->deskripsi }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan-edit">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Verifikasi ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-verifikasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bi bi-check-circle me-1"></i>Verifikasi Program Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Verifikasi <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="verifikasi-catatan" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info text-white" id="btn-konfirmasi-verifikasi">
                    <i class="bi bi-check-circle me-1"></i>Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Approval ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-approval" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bi bi-patch-check me-1"></i>Setujui Program Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Persetujuan <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea class="form-control" id="approval-catatan" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn-konfirmasi-approval">
                    <i class="bi bi-patch-check me-1"></i>Setujui
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tolak ──────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-tolak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="bi bi-x-circle me-1"></i>Tolak Program Kerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="tolak-catatan" rows="3" placeholder="Tuliskan alasan penolakan..."></textarea>
                    <div class="form-text text-danger d-none" id="tolak-catatan-error">Alasan penolakan wajib diisi.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-konfirmasi-tolak">
                    <i class="bi bi-x-circle me-1"></i>Tolak
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah Kegiatan ────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-tambah-kegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-tambah-kegiatan">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-plus-circle me-1"></i>Tambah Kegiatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_kegiatan" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Penanggung Jawab</label>
                            <input type="text" class="form-control" name="penanggung_jawab" placeholder="Nama PJ...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Target</label>
                            <input type="text" class="form-control" name="target" placeholder="Target capaian...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Indikator Keberhasilan</label>
                            <input type="text" class="form-control" name="indikator" placeholder="Indikator...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Anggaran (Rp)</label>
                            <input type="number" class="form-control" name="anggaran" min="0" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bulan Mulai <span class="text-danger">*</span></label>
                            <select class="form-select" name="bulan_mulai" required>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                                    <option value="{{ $i+1 }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bulan Selesai <span class="text-danger">*</span></label>
                            <select class="form-select" name="bulan_selesai" required>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                                    <option value="{{ $i+1 }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan-kegiatan">
                        <i class="bi bi-save me-1"></i>Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Edit Kegiatan ──────────────────────────────────────────────── --}}
<div class="modal fade" id="modal-edit-kegiatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-edit-kegiatan">
                @csrf
                <input type="hidden" id="edit-kegiatan-id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil me-1"></i>Edit Kegiatan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_kegiatan" id="ek-nama" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Penanggung Jawab</label>
                            <input type="text" class="form-control" name="penanggung_jawab" id="ek-pj">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Target</label>
                            <input type="text" class="form-control" name="target" id="ek-target">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Indikator</label>
                            <input type="text" class="form-control" name="indikator" id="ek-indikator">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Anggaran (Rp)</label>
                            <input type="number" class="form-control" name="anggaran" id="ek-anggaran" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bulan Mulai <span class="text-danger">*</span></label>
                            <select class="form-select" name="bulan_mulai" id="ek-mulai" required>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                                    <option value="{{ $i+1 }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bulan Selesai <span class="text-danger">*</span></label>
                            <select class="form-select" name="bulan_selesai" id="ek-selesai" required>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                                    <option value="{{ $i+1 }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="ek-deskripsi" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-update-kegiatan">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Update Realisasi ───────────────────────────────────────────── --}}
<div class="modal fade" id="modal-realisasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-realisasi">
                @csrf
                <input type="hidden" id="realisasi-kegiatan-id">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-graph-up me-1"></i>Update Realisasi Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">
                                Progress <span class="text-danger">*</span>
                                <span class="badge bg-primary ms-2" id="persen-badge">0%</span>
                            </label>
                            <input type="range" class="form-range" id="realisasi-persen-range"
                                   name="realisasi_persen" min="0" max="100" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_kegiatan" id="realisasi-status" required>
                                <option value="belum">Belum Mulai</option>
                                <option value="proses">Sedang Berjalan</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Realisasi Anggaran (Rp)</label>
                            <input type="number" class="form-control" name="realisasi_anggaran" id="realisasi-anggaran" min="0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Catatan Realisasi</label>
                            <textarea class="form-control" name="catatan_realisasi" id="realisasi-catatan" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btn-simpan-realisasi">
                        <i class="bi bi-save me-1"></i>Simpan Realisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var programId = {{ $prog->id }};

    // ── Reload kegiatan table section ──────────────────────────────────────
    function reloadKegiatan() {
        $('#kegiatan-container').load(location.href + ' #kegiatan-container > *', function () {
            // Reinitialize tooltips
            var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipEls.forEach(function (el) { new bootstrap.Tooltip(el); });
        });
    }

    // ── Range slider live display ──────────────────────────────────────────
    $('#realisasi-persen-range').on('input', function () {
        $('#persen-badge').text($(this).val() + '%');
    });

    // ── Submit Program ─────────────────────────────────────────────────────
    window.submitProgram = function (id) {
        $('#submit-catatan').val('');
        $('#modal-submit').modal('show');
    };

    $('#btn-konfirmasi-submit').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/submit',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: $('#submit-catatan').val() },
            success: function (res) {
                $('#modal-submit').modal('hide');
                if (res.status === 200) {
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) { Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal.' }); },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Ajukan'); }
        });
    });

    // ── Tarik ──────────────────────────────────────────────────────────────
    window.tarikProgram = function (id) {
        Swal.fire({
            title: 'Tarik Pengajuan?', text: 'Program dikembalikan ke draft.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Tarik', cancelButtonText: 'Batal',
            confirmButtonColor: '#f59e0b',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post('{{ url('admin/program-kerja') }}/' + programId + '/withdraw', { _token: '{{ csrf_token() }}' }, function (res) {
                if (res.status === 200) { location.reload(); }
                else { Swal.fire({ icon: 'error', text: res.message }); }
            });
        });
    };

    // ── Verifikasi ─────────────────────────────────────────────────────────
    window.verifikasiProgram = function (id) {
        $('#verifikasi-catatan').val('');
        $('#modal-verifikasi').modal('show');
    };

    $('#btn-konfirmasi-verifikasi').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/verifikasi',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: $('#verifikasi-catatan').val() },
            success: function (res) {
                $('#modal-verifikasi').modal('hide');
                if (res.status === 200) { location.reload(); }
                else { Swal.fire({ icon: 'error', text: res.message }); }
            },
            error: function (xhr) { Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal.' }); },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Verifikasi'); }
        });
    });

    // ── Approval ───────────────────────────────────────────────────────────
    window.approvalProgram = function (id) {
        $('#approval-catatan').val('');
        $('#modal-approval').modal('show');
    };

    $('#btn-konfirmasi-approval').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/approval',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: $('#approval-catatan').val() },
            success: function (res) {
                $('#modal-approval').modal('hide');
                if (res.status === 200) { location.reload(); }
                else { Swal.fire({ icon: 'error', text: res.message }); }
            },
            error: function (xhr) { Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal.' }); },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-patch-check me-1"></i>Setujui'); }
        });
    });

    // ── Tolak ──────────────────────────────────────────────────────────────
    window.tolakProgram = function (id) {
        $('#tolak-catatan').val('');
        $('#tolak-catatan-error').addClass('d-none');
        $('#modal-tolak').modal('show');
    };

    $('#btn-konfirmasi-tolak').on('click', function () {
        var catatan = $('#tolak-catatan').val().trim();
        if (!catatan) { $('#tolak-catatan-error').removeClass('d-none'); return; }
        $('#tolak-catatan-error').addClass('d-none');
        var $btn = $(this).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/tolak',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', catatan: catatan },
            success: function (res) {
                $('#modal-tolak').modal('hide');
                if (res.status === 200) { location.reload(); }
                else { Swal.fire({ icon: 'error', text: res.message }); }
            },
            error: function (xhr) { Swal.fire({ icon: 'error', text: xhr.responseJSON?.message ?? 'Gagal.' }); },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Tolak'); }
        });
    });

    // ── Hapus Program ──────────────────────────────────────────────────────
    window.hapusProgram = function (id) {
        Swal.fire({
            title: 'Hapus Program Kerja?', text: 'Data akan dihapus permanen.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '{{ url('admin/program-kerja') }}/' + programId,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', text: res.message, timer: 1800, showConfirmButton: false })
                            .then(function () { window.location.href = '{{ route('admin.program-kerja.index') }}'; });
                    } else {
                        Swal.fire({ icon: 'error', text: res.message });
                    }
                }
            });
        });
    };

    // ── Edit Program ────────────────────────────────────────────────────────
    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn-simpan-edit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        $.ajax({
            url:    '{{ url('admin/program-kerja') }}/' + programId,
            type:   'PUT',
            data: {
                _token:       '{{ csrf_token() }}',
                bidang:       $('#edit-bidang').val(),
                nama_program: $('#edit-nama').val(),
                tujuan:       $('#edit-tujuan').val(),
                deskripsi:    $('#edit-deskripsi').val(),
            },
            success: function (res) {
                $('#modal-edit').modal('hide');
                if (res.status === 200) {
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal memperbarui.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Perubahan'); }
        });
    });

    // ── Tambah Kegiatan ────────────────────────────────────────────────────
    $('#form-tambah-kegiatan').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btn-simpan-kegiatan').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        var fd   = $(this).serialize() + '&_token={{ csrf_token() }}';

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/kegiatan',
            type: 'POST',
            data: fd,
            success: function (res) {
                $('#modal-tambah-kegiatan').modal('hide');
                if (res.status === 200) {
                    $('#form-tambah-kegiatan')[0].reset();
                    reloadKegiatan();
                    Swal.fire({ icon: 'success', text: res.message, timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Kegiatan'); }
        });
    });

    // ── Edit Kegiatan ──────────────────────────────────────────────────────
    window.editKegiatan = function (id, nama, pj, target, indikator, anggaran, mulai, selesai, deskripsi) {
        $('#edit-kegiatan-id').val(id);
        $('#ek-nama').val(nama);
        $('#ek-pj').val(pj);
        $('#ek-target').val(target);
        $('#ek-indikator').val(indikator);
        $('#ek-anggaran').val(anggaran);
        $('#ek-mulai').val(mulai);
        $('#ek-selesai').val(selesai);
        $('#ek-deskripsi').val(deskripsi);
        $('#modal-edit-kegiatan').modal('show');
    };

    $('#form-edit-kegiatan').on('submit', function (e) {
        e.preventDefault();
        var kId  = $('#edit-kegiatan-id').val();
        var $btn = $('#btn-update-kegiatan').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        var fd   = $(this).serialize() + '&_token={{ csrf_token() }}&_method=PUT';

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/kegiatan/' + kId,
            type: 'POST',
            data: fd,
            success: function (res) {
                $('#modal-edit-kegiatan').modal('hide');
                if (res.status === 200) {
                    reloadKegiatan();
                    Swal.fire({ icon: 'success', text: res.message, timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan'); }
        });
    });

    // ── Hapus Kegiatan ─────────────────────────────────────────────────────
    window.hapusKegiatan = function (kId) {
        Swal.fire({
            title: 'Hapus Kegiatan?', text: 'Kegiatan ini akan dihapus.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url:  '{{ url('admin/program-kerja') }}/' + programId + '/kegiatan/' + kId,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.status === 200) {
                        reloadKegiatan();
                        Swal.fire({ icon: 'success', text: res.message, timer: 1800, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', text: res.message });
                    }
                }
            });
        });
    };

    // ── Update Realisasi ───────────────────────────────────────────────────
    window.updateRealisasi = function (kId, persen, status, realisasiAnggaran, catatan) {
        $('#realisasi-kegiatan-id').val(kId);
        $('#realisasi-persen-range').val(persen).trigger('input');
        $('#realisasi-status').val(status);
        $('#realisasi-anggaran').val(realisasiAnggaran);
        $('#realisasi-catatan').val(catatan);
        $('#persen-badge').text(persen + '%');
        $('#modal-realisasi').modal('show');
    };

    $('#form-realisasi').on('submit', function (e) {
        e.preventDefault();
        var kId  = $('#realisasi-kegiatan-id').val();
        var $btn = $('#btn-simpan-realisasi').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');
        var fd   = $(this).serialize() + '&_token={{ csrf_token() }}&_method=PUT';

        $.ajax({
            url:  '{{ url('admin/program-kerja') }}/' + programId + '/kegiatan/' + kId + '/realisasi',
            type: 'POST',
            data: fd,
            success: function (res) {
                $('#modal-realisasi').modal('hide');
                if (res.status === 200) {
                    reloadKegiatan();
                    Swal.fire({ icon: 'success', text: res.message, timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Gagal.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', html: msg });
            },
            complete: function () { $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Realisasi'); }
        });
    });
});
</script>
@endpush
