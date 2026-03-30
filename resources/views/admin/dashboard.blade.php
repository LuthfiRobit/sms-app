@extends('admin.layouts.app')

@section('title', 'Dashboard Akademik')

@section('content')
<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold text-primary">Dashboard Akademik 📚</h3>
        <p class="text-muted">Selamat datang, {{ auth()->user()->name ?? 'Admin Sekolah' }}! Mode Aktif: <strong>Semester Genap</strong>.
            Prioritas hari ini: Presensi & Jurnal.</p>
    </div>
</div>

<!-- Statistic Cards (Template Style) -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white dashnum-card overflow-hidden">
            <span class="round small"></span>
            <span class="round big"></span>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="avtar avtar-lg bg-light-success">
                            <i class="bi bi-person-check text-success f-24"></i>
                        </div>
                    </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">38/40</span>
                <p class="mb-0 opacity-75">Kehadiran Guru (95%)</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white dashnum-card overflow-hidden">
            <span class="round small"></span>
            <span class="round big"></span>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="avtar avtar-lg bg-light-warning">
                            <i class="bi bi-journal-text text-warning f-24"></i>
                        </div>
                    </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">12/18</span>
                <p class="mb-0 opacity-75">Jurnal Terisi (67%)</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white dashnum-card overflow-hidden">
            <span class="round small"></span>
            <span class="round big"></span>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="avtar avtar-lg bg-light-primary">
                            <i class="bi bi-wallet2 text-primary f-24"></i>
                        </div>
                    </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">Rp 2.5jt</span>
                <p class="mb-0 opacity-75">Pemasukan Hari Ini</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white dashnum-card overflow-hidden">
            <span class="round small"></span>
            <span class="round big"></span>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="avtar avtar-lg bg-light-info">
                            <i class="bi bi-person-plus text-info f-24"></i>
                        </div>
                    </div>
                </div>
                <span class="text-white d-block f-34 f-w-500 my-2">156</span>
                <p class="mb-0 opacity-75">Pendaftar PPDB</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Main Chart: Kehadiran Mingguan -->
    <div class="col-xl-8 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Statistik Kehadiran Mingguan</h5>
            </div>
            <div class="card-body">
                <div id="attendanceChart"></div>
            </div>
        </div>
    </div>
    <!-- Pie Chart: Status Jurnal -->
    <div class="col-xl-4 col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Status Jurnal Hari Ini</h5>
            </div>
            <div class="card-body">
                <div id="jurnalChart"></div>
                <div class="text-center mt-3">
                    <p class="mb-1"><span
                            class="badge rounded-pill bg-light-success text-success me-2">●</span> Terisi:
                        12</p>
                    <p class="mb-1"><span
                            class="badge rounded-pill bg-light-warning text-warning me-2">●</span> Pending:
                        4</p>
                    <p class="mb-1"><span
                            class="badge rounded-pill bg-light-danger text-danger me-2">●</span> Kosong: 2
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal & Shortcuts Row -->
<div class="row">
    <!-- Jadwal Pelajaran Hari Ini -->
    <div class="col-md-8 col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Jadwal Pelajaran Hari Ini (Senin)</h5>
                <a href="#" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="jadwalTable" class="table table-hover table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" class="form-check-input"></th>
                                <th width="8%">Aksi</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Mapel</th>
                                <th>Guru</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td><button class="btn btn-sm btn-icon btn-light-primary"><i
                                            class="bi bi-pencil"></i></button></td>
                                <td>07:30 - 09:00</td>
                                <td><span class="badge bg-light-primary text-primary">VII-A</span></td>
                                <td>Matematika</td>
                                <td>Budi Santoso</td>
                                <td><span
                                        class="badge rounded-pill bg-light-success text-success">Hadir</span>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td><button class="btn btn-sm btn-icon btn-light-primary"><i
                                            class="bi bi-pencil"></i></button></td>
                                <td>07:30 - 09:00</td>
                                <td><span class="badge bg-light-warning text-warning">VIII-1</span></td>
                                <td>Fisika</td>
                                <td>Siti Aminah</td>
                                <td><span
                                        class="badge rounded-pill bg-light-warning text-warning">Mengajar</span>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td><button class="btn btn-sm btn-icon btn-light-primary"><i
                                            class="bi bi-pencil"></i></button></td>
                                <td>09:15 - 10:45</td>
                                <td><span class="badge bg-light-info text-info">IX-2</span></td>
                                <td>Sosiologi</td>
                                <td>Andi Pratama</td>
                                <td><span
                                        class="badge rounded-pill bg-light-secondary text-secondary">Pending</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Shortcuts & Notifications -->
    <div class="col-md-4 col-lg-4 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="#"
                            class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-journal-text fs-2 mb-1"></i><span class="small">Isi
                                Jurnal</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#"
                            class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-person-check fs-2 mb-1"></i><span class="small">Absensi</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#"
                            class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-wallet2 fs-2 mb-1"></i><span class="small">Bayar SPP</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#"
                            class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-person-plus fs-2 mb-1"></i><span class="small">Data PPDB</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between pb-0">
                <h5 class="card-title m-0">Pemberitahuan</h5>
                <small class="text-muted">Hari Ini</small>
            </div>
            <div class="card-body mt-3">
                <ul class="p-0 m-0">
                    <li class="d-flex mb-3 pb-1">
                        <div class="avatar flex-shrink-0 me-3"><span
                                class="avatar-initial rounded bg-light-primary"><i
                                    class="bi bi-bell"></i></span></div>
                        <div
                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Rapat Harian Guru</h6><small class="text-muted">Di Ruang
                                    A1, Jam 13:00</small>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex mb-3 pb-1">
                        <div class="avatar flex-shrink-0 me-3"><span
                                class="avatar-initial rounded bg-light-danger"><i
                                    class="bi bi-exclamation-circle"></i></span></div>
                        <div
                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Jurnal Kosong</h6><small class="text-muted">3 Kelas belum
                                    input jurnal</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('vendor-scripts')
<script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/apexcharts.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        // DataTable Init
        $('#jadwalTable').DataTable({
            "language": {
                "sEmptyTable": "Tidak ada data", "sLengthMenu": "Tampilkan _MENU_ entri",
                "sZeroRecords": "Tidak ditemukan data yang sesuai", "sSearch": "Cari:",
                "oPaginate": { "sFirst": "Pertama", "sPrevious": "Sebelumnya", "sNext": "Selanjutnya", "sLast": "Terakhir" }
            },
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f><t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
        });

        // ApexCharts: Attendance (Area Chart)
        var attendanceOptions = {
            series: [{ name: 'Guru Hadir', data: [38, 40, 39, 37, 40, 38, 40] }, { name: 'Siswa Hadir (%)', data: [92, 95, 90, 88, 94, 91, 93] }],
            chart: { height: 300, type: 'area' },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: { categories: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] },
            colors: ['#28a745', '#007bff'],
            tooltip: { y: { formatter: function (val) { return val } } }
        };
        new ApexCharts(document.querySelector("#attendanceChart"), attendanceOptions).render();

        // ApexCharts: Jurnal Status (Pie Chart)
        var jurnalOptions = {
            series: [12, 4, 2],
            chart: { width: 280, type: 'pie' },
            labels: ['Terisi', 'Pending', 'Kosong'],
            colors: ['#28a745', '#ffc107', '#dc3545'],
            responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
        };
        new ApexCharts(document.querySelector("#jurnalChart"), jurnalOptions).render();
    });
</script>
@endpush
