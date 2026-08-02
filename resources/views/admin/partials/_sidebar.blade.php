<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('admin.dashboard') }}" class="b-brand">
                <img src="{{ asset('assets/sekolah-refaktor-template/images/logo/logomaarif.png') }}"
                     alt="Logo Marifat" class="logo-lg">
                <div>
                    <span class="brand-name">Marifat</span>
                    <span class="brand-sub">Ma'arif Integrated Facility</span>
                </div>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <!-- 1. Dashboard -->
                <li class="pc-item pc-caption"><label>Dashboard</label><i class="bi bi-speedometer2"></i></li>
                <li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-speedometer2"></i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>
                @if(auth()->user()->canViewMenu('admin.keuangan.index'))
                <li class="pc-item">
                    <a href="#" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-pie-chart-fill"></i></span>
                        <span class="pc-mtext">Keuangan</span>
                    </a>
                </li>
                @endif
                <li class="pc-item {{ request()->routeIs('admin.notifikasi.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.notifikasi.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-bell-fill"></i></span>
                        <span class="pc-mtext">Notifikasi Saya</span>
                        @php
                            $unreadCount = app(\App\Services\NotifikasiService::class)->getUnreadCount(auth()->id());
                        @endphp
                        @if($unreadCount > 0)
                            <span class="pc-badge bg-danger">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                <!-- 2. Data Master -->
                @if(auth()->user()->canViewAnyMenu([
                    'admin.master.lembaga.index',
                    'admin.master.profil-sekolah.index',
                    'admin.master.tahun-pelajaran.index',
                    'admin.master.kurikulum.index',
                    'admin.master.model-pembelajaran.index',
                    'admin.master.jurusan.index',
                    'admin.master.mata-pelajaran.index',
                    'admin.master.rombel.index',
                    'admin.master.guru.index',
                    'admin.master.jurusan-mapel.index',
                    'admin.master.jadwal-kbm.index',
                    'admin.master.kalender-libur.index'
                ]))
                <li class="pc-item pc-caption"><label>Data Master</label><i class="bi bi-database"></i></li>

                @if(auth()->user()->canViewAnyMenu([
                    'admin.master.lembaga.index',
                    'admin.master.profil-sekolah.index',
                    'admin.master.tahun-pelajaran.index',
                    'admin.master.kurikulum.index',
                    'admin.master.model-pembelajaran.index'
                ]))
                <li class="pc-item pc-hasmenu {{ request()->routeIs('admin.master.*') ? 'active pc-trigger' : '' }}">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-building"></i></span><span
                            class="pc-mtext">Identitas & Wilayah</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->canViewMenu('admin.master.lembaga.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.lembaga.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.lembaga.index') }}">Data Lembaga</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.profil-sekolah.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.profil-sekolah.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.profil-sekolah.index') }}">Profil
                                    Sekolah</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.tahun-pelajaran.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.tahun-pelajaran.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.tahun-pelajaran.index') }}">Tahun Ajaran &
                                    Semester</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.kurikulum.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.kurikulum.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.kurikulum.index') }}">Kurikulum</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.model-pembelajaran.index'))
                            <li class="pc-item {{ request()->routeIs('admin.master.model-pembelajaran.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.master.model-pembelajaran.index') }}">Model Pembelajaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasRole('super_admin'))
                            <li class="pc-item"><a class="pc-link" href="#">Referensi Wilayah</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->canViewAnyMenu([
                    'admin.master.jurusan.index',
                    'admin.master.mata-pelajaran.index',
                    'admin.master.rombel.index',
                    'admin.master.guru.index',
                    'admin.master.jurusan-mapel.index',
                    'admin.master.jadwal-kbm.index'
                ]))
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-mortarboard"></i></span><span
                            class="pc-mtext">Organisasi &
                            Akademik</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->canViewMenu('admin.master.jurusan.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.jurusan.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.jurusan.index') }}">Jurusan / Program Studi</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.mata-pelajaran.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.mata-pelajaran.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.mata-pelajaran.index') }}">Mata Pelajaran</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.rombel.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.rombel.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.rombel.index') }}">Rombel / Kelas</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.guru.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.guru.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.guru.index') }}">Data Guru</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.jurusan-mapel.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.jurusan-mapel.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.jurusan-mapel.index') }}">Pemetaan Jurusan & Mata Pelajaran</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.jadwal-kbm.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.jadwal-kbm.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.jadwal-kbm.index') }}">Jadwal KBM</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->canViewAnyMenu([
                    'admin.master.kalender-libur.index'
                ]))
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-calendar3"></i></span><span
                            class="pc-mtext">Sarana &
                            Jadwal</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->hasRole('super_admin'))
                            <li class="pc-item"><a class="pc-link" href="#">Gedung</a></li>
                            <li class="pc-item"><a class="pc-link" href="#">Ruang Kelas / Lab</a></li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.kalender-libur.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.kalender-libur.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.kalender-libur.index') }}">Kalender Akademik</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->hasRole('super_admin'))
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-shield-shaded"></i></span><span
                            class="pc-mtext">Kedisiplinan &
                            Prestasi</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Jenis Pelanggaran</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Jenis Prestasi</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Tindakan / Sanksi</a></li>
                    </ul>
                </li>
                @endif

                @if(auth()->user()->hasRole('super_admin'))
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
                @endif
                @endif

                <!-- 3. Manajemen Pengguna -->
                @if(auth()->user()->canViewAnyMenu([
                    'admin.rbac.user.list',
                    'admin.peserta.index',
                    'admin.rbac.role.list',
                    'admin.rbac.permission.list'
                ]))
                <li class="pc-item pc-caption"><label>Manajemen Pengguna</label><i class="bi bi-people-fill"></i></li>
                @if(auth()->user()->canViewMenu('admin.rbac.user.list'))
                    <li class="pc-item {{ request()->routeIs('admin.rbac.user.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.rbac.user.index') }}" class="pc-link">
                            <span class="pc-micon"><i class="bi bi-people-fill"></i></span>
                            <span class="pc-mtext">Data Pengguna</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->canViewMenu('admin.peserta.index'))
                <li class="pc-item {{ request()->routeIs('admin.peserta.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.peserta.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="bi bi-person-badge-fill"></i></span>
                        <span class="pc-mtext">Data Peserta</span>
                    </a>
                </li>
                @endif
                @if(auth()->user()->canViewAnyMenu(['admin.rbac.role.list', 'admin.rbac.permission.list']))
                    <li
                        class="pc-item pc-hasmenu {{ request()->routeIs('admin.rbac.*') && !request()->routeIs('admin.rbac.user.*') ? 'active pc-trigger' : '' }}">
                        <a href="#!" class="pc-link"><span class="pc-micon"><i
                                    class="bi bi-shield-lock-fill"></i></span><span class="pc-mtext">Hak Akses &
                                Role</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                        <ul class="pc-submenu">
                            @if(auth()->user()->canViewMenu('admin.rbac.role.list'))
                                <li class="pc-item {{ request()->routeIs('admin.rbac.role.*') ? 'active' : '' }}"><a
                                        class="pc-link" href="{{ route('admin.rbac.role.index') }}">Manajemen Role</a></li>
                            @endif
                            @if(auth()->user()->canViewMenu('admin.rbac.permission.list'))
                                <li class="pc-item {{ request()->routeIs('admin.rbac.permission.*') ? 'active' : '' }}"><a
                                        class="pc-link" href="{{ route('admin.rbac.permission.index') }}">Permissions</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @endif

                <!-- 4. Operasional -->
                @if(auth()->user()->canViewAnyMenu([
                    'admin.ppdb.pembukaan.index',
                    'admin.ppdb.jalur.index',
                    'admin.ppdb.jadwal.index',
                    'admin.ppdb.syarat.index',
                    'admin.ppdb.biaya.index',
                    'admin.ppdb.template.index',
                    'admin.ppdb.kuota.index',
                    'admin.ppdb.formulir.index',
                    'admin.pendaftaran.index',
                    'admin.pembayaran.index',
                    'admin.seleksi.index',
                    'admin.master.rombel-siswa.index',
                    'admin.master.siswa.index',
                    'admin.akademik.setting.index',
                    'admin.akademik.absensi.index',
                    'admin.akademik.absensi-guru.index',
                    'admin.akademik.pengajuan-izin-guru.index',
                    'admin.akademik.nilai.index',
                    'admin.akademik.raport.pengajuan.index',
                    'admin.akademik.raport.verifikasi.index',
                    'admin.akademik.raport.approval.index',
                    'admin.akademik.perangkat-mengajar.index',
                    'admin.akademik.materi-belajar.index',
                    'admin.akademik.rpp.index',
                    'admin.akademik.verifikasi-rpp.index',
                    'admin.akademik.supervisi-rpp.index',
                    'admin.akademik.rpp-template.index'
                ]))
                <li class="pc-item pc-caption"><label>Operasional</label><i class="bi bi-briefcase"></i></li>

                @if(auth()->user()->canViewAnyMenu([
                    'admin.ppdb.pembukaan.index',
                    'admin.ppdb.jalur.index',
                    'admin.ppdb.jadwal.index',
                    'admin.ppdb.syarat.index',
                    'admin.ppdb.biaya.index',
                    'admin.ppdb.template.index',
                    'admin.ppdb.kuota.index',
                    'admin.ppdb.formulir.index',
                    'admin.pendaftaran.index',
                    'admin.pembayaran.index',
                    'admin.seleksi.index'
                ]))
                <li class="pc-item pc-hasmenu {{ request()->routeIs('admin.ppdb.*') ? 'active pc-trigger' : '' }}">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-person-plus-fill"></i></span><span class="pc-mtext">PPDB</span><span
                            class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->canViewMenu('admin.ppdb.pembukaan.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.pembukaan.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.pembukaan.index') }}">Pembukaan PPDB</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.jalur.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.jalur.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.jalur.index') }}">Jalur Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.jadwal.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.jadwal.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.jadwal.index') }}">Jadwal Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.syarat.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.syarat.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.syarat.index') }}">Syarat Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.biaya.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.biaya.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.biaya.index') }}">Biaya Registrasi</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.template.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.template.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.template.index') }}">Template Dokumen</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.kuota.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.kuota.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.kuota.index') }}">Kuota Jurusan</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.ppdb.formulir.index'))
                            <li class="pc-item {{ request()->routeIs('admin.ppdb.formulir.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.formulir.index') }}">Formulir Pendaftaran</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.pendaftaran.index'))
                            <li class="pc-item {{ request()->routeIs('admin.pendaftaran.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.pendaftaran.index') }}">Data Pendaftar</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.pembayaran.index'))
                            <li class="pc-item {{ request()->routeIs('admin.pembayaran.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.pembayaran.index') }}">Pembayaran PPDB</a>
                            </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.seleksi.index'))
                            <li class="pc-item {{ request()->routeIs('admin.seleksi.*') ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.ppdb.jalur.index') }}">Verifikasi & Seleksi</a>
                            </li>
                        @endif
                        @if(auth()->user()->hasRole('super_admin'))
                            <li class="pc-item"><a class="pc-link" href="#">Daftar Ulang</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->canViewAnyMenu([
                    'admin.master.rombel-siswa.index',
                    'admin.master.siswa.index',
                    'admin.akademik.setting.index',
                    'admin.akademik.absensi.index',
                    'admin.akademik.absensi-guru.index',
                    'admin.akademik.pengajuan-izin-guru.index',
                    'admin.akademik.nilai.index',
                    'admin.akademik.raport.pengajuan.index',
                    'admin.akademik.raport.verifikasi.index',
                    'admin.akademik.raport.approval.index',
                    'admin.akademik.perangkat-mengajar.index',
                    'admin.akademik.materi-belajar.index',
                    'admin.akademik.rpp.index',
                    'admin.akademik.verifikasi-rpp.index',
                    'admin.akademik.supervisi-rpp.index',
                    'admin.akademik.rpp-template.index'
                ]))
                <li class="pc-item pc-hasmenu {{ request()->routeIs('admin.master.rombel-siswa.*') || request()->routeIs('admin.akademik.*') ? 'active pc-trigger' : '' }}">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-journal-text"></i></span><span
                            class="pc-mtext">Akademik</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->canViewMenu('admin.master.rombel-siswa.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.rombel-siswa.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.rombel-siswa.index') }}">Pengelolaan Siswa</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.master.siswa.index'))
                        <li class="pc-item {{ request()->routeIs('admin.master.siswa.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.master.siswa.index') }}">Daftar Siswa Aktif</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.setting.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.setting.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.setting.index') }}">Pengaturan Akademik</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.absensi.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.absensi.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.absensi.index') }}">Absensi Siswa</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.absensi-guru.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.absensi-guru.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.absensi-guru.index') }}">Absensi Guru</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.pengajuan-izin-guru.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.pengajuan-izin-guru.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.pengajuan-izin-guru.index') }}">Izin / Sakit Guru</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.nilai.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.nilai.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.nilai.index') }}">Input Nilai</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.raport.pengajuan.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.raport.pengajuan.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.raport.pengajuan.index') }}">Pengajuan Rapor</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.raport.verifikasi.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.raport.verifikasi.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.raport.verifikasi.index') }}">Verifikasi Rapor</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.raport.approval.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.raport.approval.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.raport.approval.index') }}">Persetujuan Rapor</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.perangkat-mengajar.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.perangkat-mengajar.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.perangkat-mengajar.index') }}">Perangkat Mengajar</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.materi-belajar.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.materi-belajar.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.materi-belajar.index') }}">Materi Belajar</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.rpp.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.rpp.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.rpp.index') }}">RPP</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.rpp.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.rpp-compliance.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.rpp-compliance.index') }}">Kepatuhan RPP</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.verifikasi-rpp.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.verifikasi-rpp.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.verifikasi-rpp.index') }}">Verifikasi RPP</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.supervisi-rpp.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.supervisi-rpp.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.supervisi-rpp.index') }}">Supervisi RPP</a>
                        </li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.akademik.rpp-template.index'))
                        <li class="pc-item {{ request()->routeIs('admin.akademik.rpp-template.*') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.akademik.rpp-template.index') }}">Kelola Bagian & Poin RPP</a>
                        </li>
                        @endif
                        @if(auth()->user()->hasRole('super_admin'))
                            <li class="pc-item"><a class="pc-link" href="#">E-Rapor</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->canViewMenu('admin.keuangan.index'))
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
                @endif

                @if(auth()->user()->canViewMenu('admin.kesiswaan.index'))
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-person-lines-fill"></i></span><span class="pc-mtext">Kesiswaan</span><span
                            class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Profil & Riwayat</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Kedisiplinan & SIP</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Prestasi & Organisasi</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Bimbingan Konseling</a></li>
                    </ul>
                </li>
                @endif
                @endif

                <!-- 4b. Program Kerja -->
                @if(auth()->user()->canViewAnyMenu(['admin.program-kerja.index', 'admin.kinerja.index']))
                <li class="pc-item pc-caption"><label>Program & Kinerja</label><i class="bi bi-clipboard2-check"></i></li>
                @if(auth()->user()->canViewMenu('admin.program-kerja.index'))
                <li class="pc-item pc-hasmenu {{ request()->routeIs('admin.program-kerja.*') ? 'active' : '' }}">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i class="bi bi-clipboard2-check"></i></span><span class="pc-mtext">Program Kerja</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ request()->routeIs('admin.program-kerja.index') ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('admin.program-kerja.index') }}">Semua Program</a>
                        </li>
                    </ul>
                </li>
                @endif
                @if(auth()->user()->canViewMenu('admin.kinerja.index'))
                <li class="pc-item {{ request()->routeIs('admin.kinerja.*') ? 'active' : '' }}">
                    <a class="pc-link" href="{{ route('admin.kinerja.index') }}"><span class="pc-micon"><i class="bi bi-graph-up-arrow"></i></span><span class="pc-mtext">Dashboard Kinerja</span></a>
                </li>
                @if(auth()->user()->canViewMenu('admin.kinerja.manage'))
                <li class="pc-item {{ request()->routeIs('admin.kinerja.manage*') ? 'active' : '' }}">
                    <a class="pc-link" href="{{ route('admin.kinerja.manage') }}"><span class="pc-micon"><i class="bi bi-sliders"></i></span><span class="pc-mtext">Kelola Indikator KPI</span></a>
                </li>
                @endif
                @endif
                @endif

                <!-- 5. Laporan & Pengaturan -->
                @if(auth()->user()->canViewAnyMenu([
                    'admin.system.log-activity.list',
                    'admin.system.whatsapp.index'
                ]))
                <li class="pc-item pc-caption"><label>Laporan & Pengaturan</label><i
                        class="bi bi-file-earmark-bar-graph"></i></li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-file-earmark-bar-graph"></i></span><span class="pc-mtext">Laporan &
                            Audit</span><span class="pc-arrow"><i class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        @if(auth()->user()->canViewMenu('admin.system.log-activity.list'))
                            <li class="pc-item {{ request()->routeIs('admin.system.log-activity.*') ? 'active' : '' }}"><a
                                    class="pc-link" href="{{ route('admin.system.log-activity.index') }}">Log Aktivitas
                                    Sistem</a></li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.system.whatsapp.index'))
                            <li class="pc-item {{ request()->routeIs('admin.system.whatsapp.*') ? 'active' : '' }}"><a
                                    class="pc-link" href="{{ route('admin.system.whatsapp.index') }}">Uji Coba WhatsApp (Fonnte)</a></li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.reports.rekap.index'))
                            <li class="pc-item"><a class="pc-link" href="#">Rekap Laporan</a></li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.reports.akademik.index'))
                            <li class="pc-item"><a class="pc-link" href="#">Laporan Akademik</a></li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.reports.kesiswaan.index'))
                            <li class="pc-item"><a class="pc-link" href="#">Laporan Kesiswaan</a></li>
                        @endif
                        @if(auth()->user()->canViewMenu('admin.reports.ppdb.index'))
                            <li class="pc-item"><a class="pc-link" href="#">Laporan PPDB</a></li>
                        @endif
                    </ul>
                </li>
                @if(auth()->user()->canViewAnyMenu([
                    'admin.system.config.index',
                    'admin.system.profile.index',
                    'admin.system.db-maintenance.index'
                ]))
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"><i
                                class="bi bi-gear-wide-connected"></i></span><span
                            class="pc-mtext">Pengaturan</span><span class="pc-arrow"><i
                                class="bi bi-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="#">Konfigurasi Sistem</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Profil Pengguna</a></li>
                        <li class="pc-item"><a class="pc-link" href="#">Pemeliharaan Database</a></li>
                    </ul>
                </li>
                @endif
                @endif

            </ul>
        </div>
    </div>
</nav>