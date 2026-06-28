@extends('admin.layouts.app')
@section('title', 'Dashboard Kinerja')

@section('content')

@php
    $activeTahunId   = $activeTahun?->id;
    $activeTahunNama = $activeTahun?->nama ?? '—';
@endphp

{{-- ══ PAGE HEADER ══════════════════════════════════════════════════════ --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold text-dark">
            <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard Kinerja
        </h4>
        <small class="text-muted">Pantau pencapaian KPI sekolah lintas dimensi: akademik, PPDB, program kerja, kesiswaan, sarpras, dan humas.</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.kinerja.manage') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-sliders me-1"></i>Kelola Indikator
        </a>
        <button class="btn btn-sm btn-outline-info" id="btn-sync-auto" title="Perbarui indikator otomatis dari sumber data">
            <i class="bi bi-lightning-charge-fill me-1"></i>Perbarui Data Otomatis
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
                    <option value="">-- Pilih Tahun --</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t->id }}" {{ $activeTahunId == $t->id ? 'selected' : '' }}>
                            {{ $t->nama }}{{ $t->status === 'aktif' ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <button class="btn btn-sm btn-primary w-100" id="btn-load-kpi">
                    <i class="bi bi-bar-chart-fill me-1"></i>Muat Data
                </button>
            </div>
            <div class="col-md-2 col-sm-6">
                <div id="last-updated-info" class="text-muted small text-end d-none">
                    <i class="bi bi-clock me-1"></i><span id="last-updated-text"></span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ SUMMARY STAT CARDS ═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4" id="stats-row">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div>
                    <div class="kpi-stat-val" id="stat-total">—</div>
                    <div class="kpi-stat-label">Total KPI</div>
                    <div class="kpi-stat-sub" id="stat-total-sub"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 kpi-card-hijau">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-stat-icon bg-success-subtle text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="kpi-stat-val text-success" id="stat-hijau">—</div>
                    <div class="kpi-stat-label">On Track &nbsp;<small class="text-muted fw-normal">(≥ 90%)</small></div>
                    <div class="kpi-stat-sub" id="stat-hijau-sub"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 kpi-card-kuning">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div>
                    <div class="kpi-stat-val text-warning" id="stat-kuning">—</div>
                    <div class="kpi-stat-label">Perlu Perhatian &nbsp;<small class="text-muted fw-normal">(70–89%)</small></div>
                    <div class="kpi-stat-sub" id="stat-kuning-sub"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 kpi-card-merah">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div>
                    <div class="kpi-stat-val text-danger" id="stat-merah">—</div>
                    <div class="kpi-stat-label">Kritis &nbsp;<small class="text-muted fw-normal">(&lt; 70%)</small></div>
                    <div class="kpi-stat-sub" id="stat-merah-sub"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ KPI GRID AREA ════════════════════════════════════════════════════ --}}
<div id="kpi-grid-area">
    {{-- Initial placeholder --}}
    <div class="text-center py-5 text-muted" id="kpi-placeholder">
        <i class="bi bi-bar-chart-line display-5 d-block mb-3 opacity-25"></i>
        <p class="mb-0">Pilih lembaga dan tahun pelajaran, kemudian klik <strong>Muat Data</strong>.</p>
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
                <input type="hidden" id="r-indikator-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Indikator</label>
                    <input type="text" class="form-control bg-light" id="r-nama-indikator" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="r-periode" placeholder="cth. Semester 1 2025/2026" maxlength="100">
                    <div class="form-text">Isi sesuai periode pelaporan, misal: Semester 1 2025/2026</div>
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
                    <i class="bi bi-save me-1"></i>Simpan Realisasi
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ── KPI Stat Cards ─────────────────────────────────── */
    .kpi-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .kpi-stat-val {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .kpi-stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        margin-top: 2px;
    }
    .kpi-stat-sub {
        font-size: 0.72rem;
        color: #9ca3af;
        font-variant-numeric: tabular-nums;
        min-height: 1rem;
    }

    /* ── KPI Kategori Section ───────────────────────────── */
    .kpi-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 8px 8px 0 0;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .kpi-section-header i { font-size: 1rem; }

    /* ── KPI Row Item ───────────────────────────────────── */
    .kpi-item-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: background .15s;
    }
    .kpi-item-row:last-child { border-bottom: none; }
    .kpi-item-row:hover { background: #f9fafb; }

    .kpi-item-info { flex: 0 0 220px; min-width: 0; }
    .kpi-item-name {
        font-size: .875rem;
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kpi-item-desc {
        font-size: .72rem;
        color: #9ca3af;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kpi-item-satuan {
        font-size: .7rem;
        background: #f3f4f6;
        color: #6b7280;
        padding: 1px 6px;
        border-radius: 4px;
        margin-top: 3px;
        display: inline-block;
    }

    .kpi-item-progress { flex: 1; min-width: 80px; }
    .kpi-progress-bar-wrap {
        height: 8px;
        background: #e5e7eb;
        border-radius: 99px;
        overflow: hidden;
    }
    .kpi-progress-fill {
        height: 100%;
        border-radius: 99px;
        transition: width .5s ease;
    }
    .kpi-progress-labels {
        display: flex;
        justify-content: space-between;
        font-size: .68rem;
        color: #9ca3af;
        margin-top: 3px;
        font-variant-numeric: tabular-nums;
    }

    .kpi-item-pct {
        flex: 0 0 80px;
        text-align: right;
    }
    .kpi-pct-val {
        font-size: 1.1rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        line-height: 1;
    }
    .kpi-tl-dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-left: 4px;
        flex-shrink: 0;
    }

    .kpi-item-meta { flex: 0 0 140px; text-align: right; }
    .kpi-meta-date { font-size: .7rem; color: #9ca3af; font-variant-numeric: tabular-nums; }
    .kpi-meta-nodata { font-size: .72rem; color: #d1d5db; font-style: italic; }

    /* ── Traffic Light Colors ───────────────────────────── */
    .tl-hijau  { background-color: #16a34a; }
    .tl-kuning { background-color: #d97706; }
    .tl-merah  { background-color: #dc2626; }
    .tl-none   { background-color: #d1d5db; }

    .fill-hijau  { background-color: #16a34a; }
    .fill-kuning { background-color: #d97706; }
    .fill-merah  { background-color: #dc2626; }
    .fill-none   { background-color: #d1d5db; }

    /* ── Kategori header color strips ──────────────────── */
    .kat-primary   { background: #eff6ff; color: #1d4ed8; border-bottom: 2px solid #3b82f6; }
    .kat-info      { background: #f0f9ff; color: #0369a1; border-bottom: 2px solid #0ea5e9; }
    .kat-warning   { background: #fffbeb; color: #92400e; border-bottom: 2px solid #f59e0b; }
    .kat-success   { background: #f0fdf4; color: #166534; border-bottom: 2px solid #22c55e; }
    .kat-secondary { background: #f8fafc; color: #475569; border-bottom: 2px solid #94a3b8; }
    .kat-purple    { background: #faf5ff; color: #6d28d9; border-bottom: 2px solid #a78bfa; }
    .kat-dark      { background: #f9fafb; color: #111827; border-bottom: 2px solid #374151; }

    /* ── Loading Skeleton ───────────────────────────────── */
    .kpi-skeleton {
        animation: pulse-bg 1.5s ease-in-out infinite;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        border-radius: 6px;
    }
    @keyframes pulse-bg {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 576px) {
        .kpi-item-info { flex: 0 0 160px; }
        .kpi-item-meta { display: none; }
        .kpi-item-pct  { flex: 0 0 64px; }
    }
</style>
@endpush

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const ROUTES = {
        dashboard:    '{{ route("admin.kinerja.dashboard") }}',
        realisasi:    '{{ route("admin.kinerja.inputRealisasi", ":id") }}',
        syncAuto:     '{{ route("admin.kinerja.syncAuto") }}',
        manage:       '{{ route("admin.kinerja.manage") }}',
    };

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── DOM refs ──────────────────────────────────────────────────────────
    const filterLembaga = document.getElementById('filter-lembaga');
    const filterTahun   = document.getElementById('filter-tahun');
    const gridArea      = document.getElementById('kpi-grid-area');
    const placeholder   = document.getElementById('kpi-placeholder');

    // ── Bootstrap modals ─────────────────────────────────────────────────
    const modalRealisasi = new bootstrap.Modal(document.getElementById('modal-realisasi'));

    // ── Load KPI Data ─────────────────────────────────────────────────────
    function loadKpiData() {
        const lembagaId = filterLembaga.value;
        const tahunId   = filterTahun.value;

        if (!lembagaId || !tahunId) {
            Swal.fire({ icon: 'warning', title: 'Pilih Filter', text: 'Pilih lembaga dan tahun pelajaran terlebih dahulu.' });
            return;
        }

        showLoadingSkeleton();

        const url = ROUTES.dashboard + '?lembaga_id=' + lembagaId + '&tahun_pelajaran_id=' + tahunId;

        fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } })
            .then(r => r.json())
            .then(res => {
                if (res.status !== 200) {
                    showError(res.message || 'Gagal memuat data.');
                    return;
                }
                const data = res.data;
                renderSummaryCards(data.stats);
                renderKpiGrid(data.grouped, data.last_updated);
            })
            .catch(() => showError('Terjadi kesalahan jaringan.'));
    }

    // ── Render Summary Cards ──────────────────────────────────────────────
    function renderSummaryCards(stats) {
        const total = stats.total || 0;
        const pct   = (n) => total > 0 ? ' (' + Math.round(n / total * 100) + '%)' : '';

        document.getElementById('stat-total').textContent    = total;
        document.getElementById('stat-hijau').textContent    = stats.hijau ?? 0;
        document.getElementById('stat-kuning').textContent   = stats.kuning ?? 0;
        document.getElementById('stat-merah').textContent    = stats.merah ?? 0;

        document.getElementById('stat-total-sub').textContent  = stats.tanpa_data + ' belum ada data';
        document.getElementById('stat-hijau-sub').textContent  = pct(stats.hijau);
        document.getElementById('stat-kuning-sub').textContent = pct(stats.kuning);
        document.getElementById('stat-merah-sub').textContent  = pct(stats.merah);
    }

    // ── Render KPI Grid ───────────────────────────────────────────────────
    function renderKpiGrid(grouped, lastUpdated) {
        if (!grouped || grouped.length === 0) {
            gridArea.innerHTML = renderEmptyState();
            return;
        }

        let html = '';
        grouped.forEach(group => {
            html += renderKategoriSection(group);
        });

        gridArea.innerHTML = html;

        if (lastUpdated) {
            document.getElementById('last-updated-text').textContent = 'Diperbarui: ' + lastUpdated;
            document.getElementById('last-updated-info').classList.remove('d-none');
        }

        // Bind input buttons
        gridArea.querySelectorAll('[data-input-id]').forEach(btn => {
            btn.addEventListener('click', () => openInputRealisasi(btn.dataset.inputId, btn.dataset.nama));
        });
    }

    function renderKategoriSection(group) {
        const katClass = 'kat-' + (group.color || 'secondary');
        const items    = group.items || [];

        let itemsHtml = '';
        items.forEach(kpi => { itemsHtml += renderKpiRow(kpi); });

        return `
            <div class="card shadow-sm border-0 mb-3">
                <div class="kpi-section-header ${katClass}">
                    <i class="${group.icon || 'bi-grid'}"></i>
                    <span>${escHtml(group.label)}</span>
                    <span class="ms-auto fw-normal opacity-75">${items.length} indikator</span>
                </div>
                <div class="kpi-items-list">
                    ${itemsHtml || '<div class="text-center py-3 text-muted small">Belum ada indikator di kategori ini.</div>'}
                </div>
            </div>`;
    }

    function renderKpiRow(kpi) {
        const pct      = kpi.pencapaian_persen;
        const tl       = kpi.traffic_light || 'none';
        const hasPct   = pct !== null && pct !== undefined;
        const barWidth = hasPct ? Math.min(100, Math.max(0, pct)) : 0;
        const fillCls  = 'fill-' + tl;
        const dotCls   = 'tl-' + tl;

        const pctDisplay  = hasPct ? pct.toFixed(1) + '%' : '—';
        const realDisplay = (kpi.nilai_realisasi !== null && kpi.nilai_realisasi !== undefined)
            ? kpi.nilai_realisasi + (kpi.satuan ? ' ' + escHtml(kpi.satuan) : '')
            : '—';

        const metaHtml = kpi.last_updated
            ? `<div class="kpi-meta-date"><i class="bi bi-clock me-1"></i>${escHtml(kpi.last_updated)}</div>
               <div class="kpi-meta-date">${escHtml(kpi.periode || '')}</div>`
            : `<div class="kpi-meta-nodata">Belum ada data</div>`;

        const pctColor = tl === 'hijau' ? 'text-success' : tl === 'kuning' ? 'text-warning' : tl === 'merah' ? 'text-danger' : 'text-secondary';

        const isAutoBadge = kpi.is_auto
            ? `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 ms-1" style="font-size:.6rem"><i class="bi bi-lightning-fill"></i></span>`
            : '';

        return `
            <div class="kpi-item-row">
                <div class="kpi-item-info">
                    <div class="kpi-item-name" title="${escHtml(kpi.nama_indikator)}">${escHtml(kpi.nama_indikator)}${isAutoBadge}</div>
                    ${kpi.deskripsi ? `<div class="kpi-item-desc">${escHtml(kpi.deskripsi)}</div>` : ''}
                    <span class="kpi-item-satuan">${escHtml(kpi.satuan || 'unit')}</span>
                </div>
                <div class="kpi-item-progress">
                    <div class="kpi-progress-bar-wrap">
                        <div class="kpi-progress-fill ${fillCls}" style="width:${barWidth}%"></div>
                    </div>
                    <div class="kpi-progress-labels">
                        <span>0</span>
                        <span>${escHtml(String(kpi.target || 0))} ${escHtml(kpi.satuan || '')}</span>
                    </div>
                </div>
                <div class="kpi-item-pct">
                    <div class="d-flex align-items-center justify-content-end">
                        <span class="kpi-pct-val ${pctColor}">${pctDisplay}</span>
                        <span class="kpi-tl-dot ${dotCls}"></span>
                    </div>
                    <div style="font-size:.68rem;color:#9ca3af;text-align:right;font-variant-numeric:tabular-nums;">
                        ${realDisplay}
                    </div>
                </div>
                <div class="kpi-item-meta">
                    ${metaHtml}
                    <button class="btn btn-xs btn-light border mt-1" style="font-size:.68rem;padding:2px 8px;"
                        data-input-id="${kpi.id}" data-nama="${escHtml(kpi.nama_indikator)}">
                        <i class="bi bi-pencil me-1"></i>Input
                    </button>
                </div>
            </div>`;
    }

    function renderEmptyState() {
        return `
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <i class="bi bi-bar-chart-line display-4 text-muted opacity-25 d-block mb-3"></i>
                    <h6 class="text-muted">Belum ada indikator KPI</h6>
                    <p class="text-muted small mb-3">Konfigurasikan indikator KPI untuk lembaga dan tahun pelajaran yang dipilih.</p>
                    <a href="${ROUTES.manage}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Konfigurasi Indikator
                    </a>
                </div>
            </div>`;
    }

    // ── Skeleton Loading ──────────────────────────────────────────────────
    function showLoadingSkeleton() {
        let html = '';
        for (let i = 0; i < 3; i++) {
            html += `<div class="card shadow-sm border-0 mb-3">
                <div class="kpi-section-header kat-secondary">
                    <span class="kpi-skeleton d-inline-block" style="width:120px;height:12px;border-radius:4px;"></span>
                </div>
                <div class="kpi-items-list">
                    ${Array(3).fill('').map(() => `
                        <div class="kpi-item-row">
                            <div class="kpi-item-info">
                                <div class="kpi-skeleton mb-1" style="width:160px;height:12px;border-radius:4px;"></div>
                                <div class="kpi-skeleton" style="width:80px;height:10px;border-radius:4px;"></div>
                            </div>
                            <div class="kpi-item-progress">
                                <div class="kpi-skeleton" style="height:8px;border-radius:99px;"></div>
                            </div>
                            <div class="kpi-item-pct">
                                <div class="kpi-skeleton" style="width:50px;height:16px;border-radius:4px;margin-left:auto;"></div>
                            </div>
                            <div class="kpi-item-meta">
                                <div class="kpi-skeleton" style="width:80px;height:10px;border-radius:4px;margin-left:auto;"></div>
                            </div>
                        </div>`).join('')}
                </div>
            </div>`;
        }
        gridArea.innerHTML = html;
    }

    function showError(msg) {
        gridArea.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>${escHtml(msg)}</div>`;
    }

    // ── Input Realisasi Modal ─────────────────────────────────────────────
    function openInputRealisasi(id, nama) {
        document.getElementById('r-indikator-id').value   = id;
        document.getElementById('r-nama-indikator').value = nama;
        document.getElementById('r-periode').value        = '';
        document.getElementById('r-nilai').value          = '';
        document.getElementById('r-catatan').value        = '';
        modalRealisasi.show();
    }

    document.getElementById('btn-save-realisasi').addEventListener('click', function () {
        const id      = document.getElementById('r-indikator-id').value;
        const periode = document.getElementById('r-periode').value.trim();
        const nilai   = document.getElementById('r-nilai').value.trim();
        const catatan = document.getElementById('r-catatan').value.trim();

        if (!periode || !nilai) {
            Swal.fire({ icon: 'warning', title: 'Isian Tidak Lengkap', text: 'Periode dan nilai realisasi wajib diisi.' });
            return;
        }

        const url = ROUTES.realisasi.replace(':id', id);

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ periode, nilai_realisasi: parseFloat(nilai), catatan }),
        })
            .then(r => r.json())
            .then(res => {
                modalRealisasi.hide();
                if (res.status === 200) {
                    Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 1800, showConfirmButton: false });
                    loadKpiData();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
    });

    // ── Sync Auto ─────────────────────────────────────────────────────────
    document.getElementById('btn-sync-auto').addEventListener('click', function () {
        const lembagaId = filterLembaga.value;
        const tahunId   = filterTahun.value;

        if (!lembagaId || !tahunId) {
            Swal.fire({ icon: 'warning', title: 'Pilih Filter', text: 'Pilih lembaga dan tahun pelajaran terlebih dahulu.' });
            return;
        }

        Swal.fire({
            title: 'Perbarui Data Otomatis?',
            html: 'Sistem akan menghitung ulang nilai indikator yang bertanda <b>Otomatis</b> dari sumber datanya.<br><br>Masukkan periode:',
            input: 'text',
            inputPlaceholder: 'cth. Semester 1 2025/2026',
            inputAttributes: { maxlength: 100 },
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-lightning-fill me-1"></i>Perbarui',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0dcaf0',
        }).then(result => {
            if (!result.isConfirmed || !result.value?.trim()) return;

            const btn = document.getElementById('btn-sync-auto');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memperbarui...';

            fetch(ROUTES.syncAuto, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ lembaga_id: lembagaId, tahun_pelajaran_id: tahunId, periode: result.value.trim() }),
            })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i>Perbarui Data Otomatis';

                    if (res.status === 200) {
                        Swal.fire({ icon: 'success', title: 'Selesai', text: res.message, timer: 2000, showConfirmButton: false });
                        loadKpiData();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i>Perbarui Data Otomatis';
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' });
                });
        });
    });

    // ── Utility ───────────────────────────────────────────────────────────
    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Bind events ───────────────────────────────────────────────────────
    document.getElementById('btn-load-kpi').addEventListener('click', loadKpiData);

    // Auto-load on page ready if both selectors pre-filled
    window.addEventListener('DOMContentLoaded', () => {
        if (filterLembaga.value && filterTahun.value) {
            loadKpiData();
        }
    });

    // Expose for inline use from rendered HTML
    window.openInputRealisasi = openInputRealisasi;
}());
</script>
@endpush
