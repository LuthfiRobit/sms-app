<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('admin.dashboard') }}" class="b-brand text-primary">
                <h3 class="m-0 fw-bold text-primary">SIMS Terpadu</h3>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <!-- 1. Dashboard -->
                <li class="pc-item pc-caption"><label>Dashboard</label><i class="bi bi-speedometer2"></i></li>
                <li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-speedometer2"></i></span>
                        <span class="pc-mtext">Akademik</span>
                    </a>
                </li>
                <li class="pc-item">
                    <a href="#" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-pie-chart-fill"></i></span>
                        <span class="pc-mtext">Keuangan</span>
                    </a>
                </li>

                <!-- 2. Data Master -->
                <li class="pc-item pc-caption"><label>Data Master</label><i class="bi bi-database"></i></li>
                <li class="pc-item pc-hasmenu {{ request()->routeIs('admin.master.*') ? 'active pc-trigger' : '' }}">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-building"></i></span><span
                            class="pc-mtext">Identitas & Wilayah</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->hasPermissionTo('admin.master.profil-sekolah.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.profil-sekolah.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.profil-sekolah.index') }}">Profil
                                    Sekolah</a>
                            </li>
                        @endif
                        <li class="pc-item {{ request()->routeIs('admin.master.tahun-pelajaran.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.tahun-pelajaran.index') }}">Tahun Ajaran &
                                Semester</a>
                        </li>
                        @if(auth()->user()->hasPermissionTo('admin.master.kurikulum.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.kurikulum.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.kurikulum.index') }}">Kurikulum</a>
                            </li>
                        @endif
                        <li class="pc-item"><a class="pc-link" href="#">Referensi Wilayah</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-mortarboard"></i></span><span
                            class="pc-mtext">Org. &
                            Akademik</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Jurusan / Peminatan</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Tingkat Kelas</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Rombel (Kelas)</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Mata Pelajaran</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Mapping Jurusan <-> Mapel</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-calendar3"></i></span><span
                            class="pc-mtext">Sarana &
                            Jadwal</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Gedung</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Ruang Kelas / Lab</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Slot Jam Pelajaran</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Kalender Akademik</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-shield-shaded"></i></span><span
                            class="pc-mtext">Kedisiplinan &
                            Pres.</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Jenis Pelanggaran</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Jenis Prestasi</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Tindakan / Sanksi</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-wallet2"></i></span><span
                            class="pc-mtext">Master Keuangan</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Komponen Biaya</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Kategori Biaya</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Metode Pembayaran</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Diskon & Beasiswa</a></li>
                    </ul>
                </li>

                <!-- 3. Manajemen Pengguna -->
                <li class="pc-item pc-caption"><label>Manajemen Pengguna</label><i class="bi bi-people-fill"></i></li>
                @if(auth()->check() && auth()->user()->hasPermissionTo('admin.rbac.user.list'))
                    <li class="pc-item {{ request()->routeIs('admin.rbac.user.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.rbac.user.index') }}" class="pc-link">
                            <span class="pc-micon"><i class="bi bi-people-fill"></i></span>
                            <span class="pc-mtext">Data Pengguna</span>
                        </a>
                    </li>
                @endif
                @if(auth()->check() && auth()->user()->hasPermissionTo('admin.peserta.index'))
                <li class="pc-item {{ request()->routeIs('admin.peserta.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.peserta.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-person-badge-fill"></i></span>
                        <span class="pc-mtext">Data Peserta</span>
                    </a>
                </li>
                @endif
                @if(auth()->check() && auth()->user()->hasAnyPermission(['admin.rbac.role.list', 'admin.rbac.permission.list']))
                    <li
                        class="pc-item pc-hasmenu {{ request()->routeIs('admin.rbac.*') && !request()->routeIs('admin.rbac.user.*') ? 'active pc-trigger' : '' }}">
                        <a href="#!" class="pc-link"><span class="pc-micon"><i
                                    class="bi bi-shield-lock-fill"></i></span><span class="pc-mtext">Hak Akses &
                                Role</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                        <ul class="pc-submenu">
                            @if(auth()->user()->hasPermissionTo('admin.rbac.role.list'))
                                <li class="pc-item {{ request()->routeIs('admin.rbac.role.*') ? 'active' : '' }}"><a
                                        class="pc-link" href="{{ route('admin.rbac.role.index') }}">Role Management</a></li>
                            @endif
                            @if(auth()->user()->hasPermissionTo('admin.rbac.permission.list'))
                                <li class="pc-item {{ request()->routeIs('admin.rbac.permission.*') ? 'active' : '' }}"><a
                                        class="pc-link" href="{{ route('admin.rbac.permission.index') }}">Permissions</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- 4. Operasional -->
                <li class="pc-item pc-caption"><label>Operasional</label><i class="bi bi-briefcase"></i></li>
                <li class="pc-item pc-hasmenu {{ request()->routeIs('admin.ppdb.*') ? 'active pc-trigger' : '' }}">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-person-plus-fill"></i></span><span class="pc-mtext">PPDB</span><span
                            class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.pembukaan.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.pembukaan.index') }}">Pembukaan PPDB</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.jalur.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.jalur.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.jalur.index') }}">Jalur Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.jadwal.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.jadwal.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.jadwal.index') }}">Jadwal Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.syarat.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.syarat.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.syarat.index') }}">Syarat Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.biaya.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.biaya.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.biaya.index') }}">Biaya Registrasi</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.template.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.template.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.template.index') }}">Template Dokumen</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.kuota.index'))
                            <li class="pc-item {{ request()->is('admin/ppdb/kuota*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.kuota.index') }}">Kuota Jurusan</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasPermissionTo('admin.ppdb.formulir.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.formulir.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.formulir.index') }}">Formulir Pendaftaran</a>
                            </li>
                        @endif
                        <li class="pc-item"><a class="pc-link" href="#">Data Pendaftar</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Verifikasi & Seleksi</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Daftar Ulang</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-journal-text"></i></span><span
                            class="pc-mtext">Akademik</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Penjadwalan KBM</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Presensi Siswa</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Presensi & Jurnal Guru</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Nilai (F & S)</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">E-Rapor</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-bank"></i></span><span
                            class="pc-mtext">Keuangan</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Tagihan Siswa</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Transaksi Pembayaran</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Tunggakan</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Laporan & Arus Kas</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-emoji-smile-fill"></i></span><span class="pc-mtext">Kesiswaan</span><span
                            class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Profil & Riwayat</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Kedisiplinan & SIP</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Prestasi & Organisasi</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Bimbingan Konseling</a></li>
                    </ul>
                </li>

                <!-- 5. Laporan & Pengaturan -->
                <li class="pc-item pc-caption"><label>Laporan & Pengaturan</label><i
                        class="bi bi-file-earmark-bar-graph"></i></li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-file-earmark-bar-graph"></i></span><span class="pc-mtext">Laporan &
                            Audit</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->check() && auth()->user()->hasPermissionTo('admin.system.log-activity.list'))
                            <li class="pc-item {{ request()->routeIs('admin.system.log-activity.*') ? 'active' : '' }}"><a
                                    class="pc-link" href="{{ route('admin.system.log-activity.index') }}">Log Aktivitas
                                    Sistem</a></li>
                        @endif
                        <li class="pc-item"><a class="pc-link" href="#">Rekap Laporan</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Laporan Akademik</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Laporan Kesiswaan</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Laporan PPDB</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-gear-wide-connected"></i></span><span
                            class="pc-mtext">Pengaturan</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Konfigurasi Sistem</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Profile Pengguna</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Database Maintenance</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>