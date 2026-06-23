@once
@push('vendor-scripts')
<script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/apexcharts.min.js') }}"></script>
@endpush
@endonce

<div class="row g-3">

    {{-- Tren Pendaftar --}}
    <div class="col-xl-8">
        <div class="dash-card h-100">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Tren Pendaftar</div>
                    <div class="dash-card-sub">14 hari terakhir</div>
                </div>
                <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-outline-secondary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="dash-card-body">
                <div id="chartTren" style="min-height:220px"></div>
            </div>
        </div>
    </div>

    {{-- Status Donut --}}
    <div class="col-xl-4">
        <div class="dash-card h-100">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Status Pendaftaran</div>
                    <div class="dash-card-sub">Distribusi saat ini</div>
                </div>
            </div>
            <div class="dash-card-body">
                <div id="chartStatus" style="min-height:180px"></div>
                <div class="dash-legend mt-2">
                    @foreach([
                        ['Draft',        $stats['draft'],        '#9e9e9e'],
                        ['Dikirim',      $stats['submit'],       '#1565c0'],
                        ['Verifikasi',   $stats['verifikasi'],   '#f57c00'],
                        ['Lulus',        $stats['lulus'],        '#00843D'],
                        ['Tdk Lulus',    $stats['tidak_lulus'],  '#c62828'],
                        ['Daftar Ulang', $stats['daftar_ulang'], '#C8952A'],
                        ['Siswa Tetap',  $stats['siswa_tetap'],  '#2e7d32'],
                    ] as [$label, $count, $color])
                        @if($count > 0)
                        <div class="dash-legend-item">
                            <span class="dash-legend-dot" style="background:{{ $color }}"></span>
                            <span class="dash-legend-label">{{ $label }}</span>
                            <span class="dash-legend-count">{{ $count }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Per Lembaga Bar --}}
    <div class="col-12">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Pendaftar per Lembaga</div>
                    <div class="dash-card-sub">Tahun PPDB berjalan</div>
                </div>
            </div>
            <div class="dash-card-body">
                <div id="chartLembaga" style="min-height:200px"></div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const GREEN  = '#00843D';
    const GOLD   = '#C8952A';
    const BLUE   = '#1565c0';
    const ORANGE = '#f57c00';
    const RED    = '#c62828';
    const GREY   = '#9e9e9e';

    // Tren
    new ApexCharts(document.getElementById('chartTren'), {
        series: [{ name: 'Pendaftar', data: @json($trenValues) }],
        chart: {
            type: 'area', height: 220,
            toolbar: { show: false },
            animations: { speed: 600 },
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: .02, stops: [0, 95] },
        },
        colors: [GREEN],
        xaxis: {
            categories: @json($trenDates),
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
            axisBorder: { show: false }, axisTicks: { show: false },
        },
        yaxis: {
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
            min: 0,
        },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        tooltip: { theme: 'light' },
    }).render();

    // Status Donut
    const statusData = [
        { label: 'Draft',        val: {{ $stats['draft'] }},        color: GREY },
        { label: 'Dikirim',      val: {{ $stats['submit'] }},       color: BLUE },
        { label: 'Verifikasi',   val: {{ $stats['verifikasi'] }},   color: ORANGE },
        { label: 'Lulus',        val: {{ $stats['lulus'] }},        color: GREEN },
        { label: 'Tdk Lulus',    val: {{ $stats['tidak_lulus'] }},  color: RED },
        { label: 'Daftar Ulang', val: {{ $stats['daftar_ulang'] }}, color: GOLD },
        { label: 'Siswa Tetap',  val: {{ $stats['siswa_tetap'] }},  color: '#2e7d32' },
    ].filter(d => d.val > 0);

    if (statusData.length > 0) {
        new ApexCharts(document.getElementById('chartStatus'), {
            series: statusData.map(d => d.val),
            labels: statusData.map(d => d.label),
            colors: statusData.map(d => d.color),
            chart: { type: 'donut', height: 180, toolbar: { show: false } },
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '65%' } } },
            tooltip: { theme: 'light' },
        }).render();
    } else {
        document.getElementById('chartStatus').innerHTML =
            '<div style="height:180px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:.82rem">Belum ada data</div>';
    }

    // Per Lembaga Horizontal Bar
    new ApexCharts(document.getElementById('chartLembaga'), {
        series: [{ name: 'Pendaftar', data: @json(collect($perLembaga)->pluck('pendaftaran_count')) }],
        chart: {
            type: 'bar', height: 200,
            toolbar: { show: false },
        },
        plotOptions: {
            bar: { horizontal: true, borderRadius: 5, barHeight: '55%' },
        },
        dataLabels: {
            enabled: true,
            formatter: v => v || '–',
            style: { fontSize: '11px', fontWeight: 700 },
        },
        colors: [GREEN],
        xaxis: {
            categories: @json(collect($perLembaga)->pluck('kode')),
            labels: { style: { fontSize: '11px', colors: '#6b7280' } },
        },
        yaxis: { labels: { style: { fontSize: '11px', colors: '#374151' } } },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        tooltip: { theme: 'light' },
    }).render();

})();
</script>
@endpush
