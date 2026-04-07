{{-- ══ BREADCRUMB ══ --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb breadcrumb-ppdb">
        <li class="breadcrumb-item"><a href="{{ route('ppdb.dashboard') }}"><i
                    class="bi bi-house me-1"></i>Dashboard</a></li>
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
            <span><i class="bi bi-ui-checks me-1"></i>Formulir: <strong
                    id="formulir-pct">{{ $prog['formulir']['persen'] }}%</strong></span>
            <span><i class="bi bi-paperclip me-1"></i>Dokumen: <strong
                    id="dokumen-pct">{{ $prog['dokumen']['persen'] }}%</strong></span>
        </div>
    </div>
</div>
