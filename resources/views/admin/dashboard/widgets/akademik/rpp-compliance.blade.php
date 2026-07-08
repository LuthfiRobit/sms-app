@php
    $pctBelum = $totalKombinasi > 0 ? round($belumPunyaRpp / $totalKombinasi * 100) : 0;
    $sudahPunyaRpp = $totalKombinasi - $belumPunyaRpp;
@endphp

<div class="dash-card h-100">
    <div class="dash-card-head">
        <div>
            <div class="dash-card-title">Kepatuhan RPP</div>
            <div class="dash-card-sub">Guru + mata pelajaran yang belum punya RPP disetujui</div>
        </div>
        <a href="{{ route('admin.akademik.rpp-compliance.index') }}" class="btn btn-sm btn-outline-secondary">
            Detail <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="dash-card-body">
        @if($totalKombinasi === 0)
            <div class="text-center py-4 text-muted">
                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                Belum ada jadwal KBM yang tercatat.
            </div>
        @else
            <div class="d-flex align-items-center gap-3">
                <div class="dash-stat-icon" style="background:{{ $belumPunyaRpp > 0 ? '#fdecea' : '#e8f5ee' }};color:{{ $belumPunyaRpp > 0 ? '#c62828' : '#00843D' }}">
                    <i class="bi {{ $belumPunyaRpp > 0 ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="dash-stat-val">{{ $belumPunyaRpp }} <span class="fs-6 text-muted fw-normal">dari {{ $totalKombinasi }}</span></div>
                    <div class="dash-stat-label">kombinasi guru-mapel belum punya RPP disetujui</div>
                </div>
            </div>
            <div class="progress mt-3" style="height:6px">
                <div class="progress-bar {{ $belumPunyaRpp > 0 ? 'bg-danger' : 'bg-success' }}" style="width: {{ $pctBelum }}%"></div>
            </div>
            <div class="text-muted small mt-1">{{ $sudahPunyaRpp }} sudah lengkap · {{ $pctBelum }}% belum</div>
        @endif
    </div>
</div>
