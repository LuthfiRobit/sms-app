<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Akademik\AbsensiController;
use App\Http\Controllers\Akademik\AbsensiGuruController;
use App\Http\Controllers\Akademik\AkademikSettingController;
use App\Http\Controllers\Akademik\ApprovalRaportController;
use App\Http\Controllers\Akademik\CetakRaportController;
use App\Http\Controllers\Akademik\MateriBelajarController;
use App\Http\Controllers\Akademik\NilaiController;
use App\Http\Controllers\Akademik\PengajuanIzinGuruController;
use App\Http\Controllers\Akademik\PengajuanRaportController;
use App\Http\Controllers\Akademik\PerangkatMengajarController;
use App\Http\Controllers\Akademik\RppComplianceController;
use App\Http\Controllers\Akademik\RppController;
use App\Http\Controllers\Akademik\RppTemplateController;
use App\Http\Controllers\Akademik\VerifikasiMateriController;
use App\Http\Controllers\Akademik\VerifikasiRaportController;
use App\Http\Controllers\Akademik\VerifikasiRppController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Kinerja\KinerjaController;
use App\Http\Controllers\Master\GuruController;
use App\Http\Controllers\Master\JadwalKbmController;
use App\Http\Controllers\Master\JurusanController;
use App\Http\Controllers\Master\JurusanMapelController;
use App\Http\Controllers\Master\KalenderLiburController;
use App\Http\Controllers\Master\KurikulumController;
use App\Http\Controllers\Master\LembagaController;
use App\Http\Controllers\Master\MataPelajaranController;
use App\Http\Controllers\Master\ModelPembelajaranController;
use App\Http\Controllers\Master\ProfilSekolahController;
use App\Http\Controllers\Master\RombelController;
use App\Http\Controllers\Master\RombelSiswaController;
use App\Http\Controllers\Master\SemesterController;
use App\Http\Controllers\Master\SiswaController;
use App\Http\Controllers\Master\SwitchLembagaController;
use App\Http\Controllers\Master\TahunPelajaranController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\Peserta\PesertaController;
use App\Http\Controllers\Portal\AuthPesertaController;
use App\Http\Controllers\Portal\DaftarUlangPesertaController;
use App\Http\Controllers\Portal\DashboardPesertaController;
use App\Http\Controllers\Portal\PembayaranPesertaController;
use App\Http\Controllers\Portal\PendaftaranPesertaController;
use App\Http\Controllers\Portal\PengumumanPesertaController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\ProfilPesertaController;
use App\Http\Controllers\Portal\RaportPesertaController;
use App\Http\Controllers\Ppdb\BiayaRegistrasiController;
use App\Http\Controllers\Ppdb\FormulirPendaftaranController;
use App\Http\Controllers\Ppdb\JadwalPendaftaranController;
use App\Http\Controllers\Ppdb\JalurPendaftaranController;
use App\Http\Controllers\Ppdb\KuotaJurusanController;
use App\Http\Controllers\Ppdb\PembukaanPpdbController;
use App\Http\Controllers\Ppdb\SyaratPendaftaranController;
use App\Http\Controllers\Ppdb\TemplateDokumenController;
use App\Http\Controllers\ProgramKerja\ProgramKerjaController;
use App\Http\Controllers\Rbac\PermissionController;
use App\Http\Controllers\Rbac\RoleController;
use App\Http\Controllers\Rbac\UserController;
use App\Http\Controllers\System\LogActivityController;
use App\Http\Controllers\System\PermissionSyncController;
use App\Http\Controllers\System\WhatsappController;
use App\Http\Controllers\Transaksi\PembayaranController;
use App\Http\Controllers\Transaksi\PendaftaranController;
use App\Http\Controllers\Transaksi\SeleksiController;
use App\Http\Controllers\Webhook\WebhookController;
use App\Services\NotifikasiService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'loginView'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'permission', 'lembaga.scope'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/widget/{id}', [DashboardController::class, 'widget'])->name('dashboard.widget');

        // RBAC System
        Route::prefix('rbac')->name('rbac.')->group(function () {
            // Roles
            Route::get('role/list', [RoleController::class, 'list'])->name('role.list');
            Route::get('role/{role}/permissions', [RoleController::class, 'getPermissions'])->name('role.permissions.list');
            Route::post('role/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('role.permissions.assign');
            Route::resource('role', RoleController::class)->except(['create', 'edit']);
            Route::get('role/{role}/edit', [RoleController::class, 'edit'])->name('role.edit');

            // Permissions
            Route::get('permission/list', [PermissionController::class, 'list'])->name('permission.list');
            Route::post('permission/{permission}/toggle-status', [PermissionController::class, 'toggleStatus'])->name('permission.toggle');
            Route::resource('permission', PermissionController::class)->except(['create', 'edit']);

            // Users Role Management
            Route::get('user/list', [UserController::class, 'list'])->name('user.list');
            Route::post('user/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggle');
            Route::resource('user', UserController::class)->except(['create', 'edit']);
        });

        // System Routes
        Route::prefix('system')->name('system.')->group(function () {
            Route::post('sync-permissions', [PermissionSyncController::class, 'sync'])->name('sync-permissions');

            Route::get('log-activity/list', [LogActivityController::class, 'list'])->name('log-activity.list');
            Route::delete('log-activity/delete-all', [LogActivityController::class, 'deleteAll'])->name('log-activity.delete-all');
            Route::resource('log-activity', LogActivityController::class)->only(['index', 'show']);

            // WhatsApp Testing Routes
            Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
                Route::get('/', [WhatsappController::class, 'index'])->name('index');
                Route::get('/status', [WhatsappController::class, 'status'])->name('status');
                Route::post('/send', [WhatsappController::class, 'send'])->name('send');
            });
        });

        // Notifikasi Routes
        Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
            Route::get('/', [NotifikasiController::class, 'index'])->name('index');
            Route::get('/unread-count', [NotifikasiController::class, 'getUnreadCount'])->name('unread-count');
            Route::post('/{id}/read', [NotifikasiController::class, 'markRead'])->name('read');
            Route::post('/mark-all-read', [NotifikasiController::class, 'markAllRead'])->name('mark-all-read');
        });

        // Switch Lembaga Aktif (super admin)
        Route::post('switch-lembaga', SwitchLembagaController::class)->name('switch-lembaga');

        // Master Data
        Route::prefix('master')->name('master.')->group(function () {
            // Lembaga (multi-tenant root)
            Route::get('lembaga/list', [LembagaController::class, 'list'])->name('lembaga.list');
            Route::post('lembaga/{id}/toggle-status', [LembagaController::class, 'toggleStatus'])->name('lembaga.toggle');
            Route::post('lembaga/{id}/set-admin', [LembagaController::class, 'setAdmin'])->name('lembaga.set-admin');
            Route::resource('lembaga', LembagaController::class)->except(['create', 'edit']);

            // Profil Sekolah
            Route::get('profil-sekolah', [ProfilSekolahController::class, 'index'])->name('profil-sekolah.index');
            Route::post('profil-sekolah', [ProfilSekolahController::class, 'store'])->name('profil-sekolah.store');

            // Kurikulum
            Route::get('kurikulum/list', [KurikulumController::class, 'list'])->name('kurikulum.list');
            Route::post('kurikulum/{kurikulum}/toggle-status', [KurikulumController::class, 'toggleStatus'])->name('kurikulum.toggle');
            Route::resource('kurikulum', KurikulumController::class)->except(['create', 'edit']);

            // Tahun Pelajaran & Semester
            Route::get('tahun-pelajaran/list', [TahunPelajaranController::class, 'list'])->name('tahun-pelajaran.list');
            Route::post('tahun-pelajaran/{tahun_pelajaran}/toggle-status', [TahunPelajaranController::class, 'toggleStatus'])->name('tahun-pelajaran.toggle');
            Route::resource('tahun-pelajaran', TahunPelajaranController::class)->except(['create', 'edit']);

            // Semester
            Route::get('semester/list', [SemesterController::class, 'list'])->name('semester.list');
            Route::post('semester/{semester}/toggle-status', [SemesterController::class, 'toggleStatus'])->name('semester.toggle');
            Route::resource('semester', SemesterController::class)->except(['create', 'edit']);

            // Kalender Libur
            Route::get('kalender-libur/list', [KalenderLiburController::class, 'list'])->name('kalender-libur.list');
            Route::post('kalender-libur/rentang', [KalenderLiburController::class, 'storeRentang'])->name('kalender-libur.rentang');
            Route::resource('kalender-libur', KalenderLiburController::class)->except(['create', 'edit']);

            // Jurusan / Program Studi
            Route::get('jurusan/list', [JurusanController::class, 'list'])->name('jurusan.list');
            Route::resource('jurusan', JurusanController::class)->except(['create', 'edit']);

            // Mata Pelajaran
            Route::get('mata-pelajaran/list', [MataPelajaranController::class, 'list'])->name('mata-pelajaran.list');
            Route::resource('mata-pelajaran', MataPelajaranController::class)->except(['create', 'edit']);

            // Model Pembelajaran (master, dipakai bagian Inti pada RPP terstruktur)
            Route::get('model-pembelajaran/list', [ModelPembelajaranController::class, 'list'])->name('model-pembelajaran.list');
            Route::get('model-pembelajaran/{id}/sintaks', [ModelPembelajaranController::class, 'sintaks'])->name('model-pembelajaran.sintaks');
            Route::post('model-pembelajaran/{id}/sintaks', [ModelPembelajaranController::class, 'syncSintaks'])->name('model-pembelajaran.sintaks.sync');
            Route::resource('model-pembelajaran', ModelPembelajaranController::class)->except(['create', 'edit']);

            // Rombel / Kelas
            Route::get('rombel/list', [RombelController::class, 'list'])->name('rombel.list');
            Route::get('rombel/guru/{lembaga}', [RombelController::class, 'guruByLembaga'])->name('rombel.guru-by-lembaga');
            Route::resource('rombel', RombelController::class)->except(['create', 'edit']);

            // Guru
            Route::get('guru/list', [GuruController::class, 'list'])->name('guru.list');
            Route::get('guru/{id}/akses-mobile', [GuruController::class, 'aksesMobile'])->name('guru.akses-mobile');
            Route::post('guru/{id}/akses-mobile/reset-password', [GuruController::class, 'aksesMobileResetPassword'])->name('guru.akses-mobile.reset-password');
            Route::post('guru/{id}/akses-mobile/toggle-status', [GuruController::class, 'aksesMobileToggleStatus'])->name('guru.akses-mobile.toggle-status');
            Route::post('guru/{id}/akses-mobile/revoke-sesi', [GuruController::class, 'aksesMobileRevokeSesi'])->name('guru.akses-mobile.revoke-sesi');
            Route::post('guru/{id}/akses-mobile/reset-wajah', [GuruController::class, 'aksesMobileResetWajah'])->name('guru.akses-mobile.reset-wajah');
            Route::resource('guru', GuruController::class)->except(['create', 'edit']);

            // Mapping Jurusan ↔ Mata Pelajaran
            Route::get('jurusan-mapel', [JurusanMapelController::class, 'index'])->name('jurusan-mapel.index');
            Route::get('jurusan-mapel/{jurusan}', [JurusanMapelController::class, 'show'])->name('jurusan-mapel.show');
            Route::post('jurusan-mapel/{jurusan}/sync', [JurusanMapelController::class, 'sync'])->name('jurusan-mapel.sync');

            // Jadwal KBM
            Route::get('jadwal-kbm/list', [JadwalKbmController::class, 'list'])->name('jadwal-kbm.list');
            Route::resource('jadwal-kbm', JadwalKbmController::class)->except(['create', 'edit']);

            // Rombel Siswa
            Route::get('rombel-siswa', [RombelSiswaController::class, 'index'])->name('rombel-siswa.index');
            Route::get('rombel-siswa/{rombel}/siswa', [RombelSiswaController::class, 'getByRombel'])->name('rombel-siswa.by-rombel');
            Route::post('rombel-siswa/{rombel}/assign', [RombelSiswaController::class, 'assign'])->name('rombel-siswa.assign');
            Route::post('rombel-siswa/{rombel}/unassign', [RombelSiswaController::class, 'unassign'])->name('rombel-siswa.unassign');
            Route::post('rombel-siswa/{rombel}/update-absen', [RombelSiswaController::class, 'updateNoAbsen'])->name('rombel-siswa.update-absen');

            // Master Siswa (view-only, filter dari peserta siswa_tetap)
            Route::get('siswa/list', [SiswaController::class, 'list'])->name('siswa.list');
            Route::get('siswa/stats', [SiswaController::class, 'stats'])->name('siswa.stats');
            Route::get('siswa/export', [SiswaController::class, 'exportCsv'])->name('siswa.export');
            Route::get('siswa', [SiswaController::class, 'index'])->name('siswa.index');
        });

        // Akademik
        Route::prefix('akademik')->name('akademik.')->group(function () {
            // Perangkat Mengajar
            Route::get('perangkat-mengajar/list', [PerangkatMengajarController::class, 'list'])->name('perangkat-mengajar.list');
            Route::get('perangkat-mengajar/guru/{guruId}/mapel', [PerangkatMengajarController::class, 'getMapelByGuru'])->name('perangkat-mengajar.guru-mapel');
            Route::resource('perangkat-mengajar', PerangkatMengajarController::class)->except(['create', 'edit']);

            // Materi Belajar
            Route::get('materi-belajar/list', [MateriBelajarController::class, 'list'])->name('materi-belajar.list');
            Route::resource('materi-belajar', MateriBelajarController::class)->except(['create', 'edit']);

            // Verifikasi Materi Ajar
            Route::get('verifikasi-materi', [VerifikasiMateriController::class, 'index'])->name('verifikasi-materi.index');
            Route::get('verifikasi-materi/list', [VerifikasiMateriController::class, 'list'])->name('verifikasi-materi.list');
            Route::post('verifikasi-materi/{id}/aksi', [VerifikasiMateriController::class, 'verifikasi'])->name('verifikasi-materi.aksi');

            // RPP Terstruktur
            Route::get('rpp/list', [RppController::class, 'list'])->name('rpp.list');
            Route::get('rpp/guru/{guruId}/mapel', [RppController::class, 'getMapelByGuru'])->name('rpp.guru-mapel');
            Route::post('rpp/{id}/duplicate', [RppController::class, 'duplicate'])->name('rpp.duplicate');
            Route::resource('rpp', RppController::class);

            // Kelola Bagian & Poin RPP (templat dinamis)
            Route::prefix('rpp-template')->name('rpp-template.')->group(function () {
                Route::get('/', [RppTemplateController::class, 'index'])->name('index');
                Route::post('bagian', [RppTemplateController::class, 'storeBagian'])->name('bagian.store');
                Route::put('bagian/{id}', [RppTemplateController::class, 'updateBagian'])->name('bagian.update');
                Route::delete('bagian/{id}', [RppTemplateController::class, 'destroyBagian'])->name('bagian.destroy');
                Route::post('poin', [RppTemplateController::class, 'storePoin'])->name('poin.store');
                Route::put('poin/{id}', [RppTemplateController::class, 'updatePoin'])->name('poin.update');
                Route::delete('poin/{id}', [RppTemplateController::class, 'destroyPoin'])->name('poin.destroy');
                Route::post('poin/reorder', [RppTemplateController::class, 'reorderPoin'])->name('poin.reorder');
                Route::get('opsi/{kategori}', [RppTemplateController::class, 'opsiIndex'])->name('opsi.index');
                Route::post('opsi', [RppTemplateController::class, 'storeOpsi'])->name('opsi.store');
                Route::put('opsi/{id}', [RppTemplateController::class, 'updateOpsi'])->name('opsi.update');
                Route::delete('opsi/{id}', [RppTemplateController::class, 'destroyOpsi'])->name('opsi.destroy');
            });

            // Kepatuhan RPP (laporan read-only)
            Route::get('rpp-compliance', [RppComplianceController::class, 'index'])->name('rpp-compliance.index');
            Route::get('rpp-compliance/list', [RppComplianceController::class, 'list'])->name('rpp-compliance.list');

            // Verifikasi RPP
            Route::get('verifikasi-rpp', [VerifikasiRppController::class, 'index'])->name('verifikasi-rpp.index');
            Route::get('verifikasi-rpp/list', [VerifikasiRppController::class, 'list'])->name('verifikasi-rpp.list');
            Route::post('verifikasi-rpp/{id}/aksi', [VerifikasiRppController::class, 'verifikasi'])->name('verifikasi-rpp.aksi');

            // Absensi Siswa
            Route::get('absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
            Route::get('absensi/rekap-pdf', [AbsensiController::class, 'rekapPdf'])->name('absensi.rekap-pdf');
            Route::get('absensi/rekap-excel', [AbsensiController::class, 'rekapExcel'])->name('absensi.rekap-excel');
            Route::get('absensi/tap', [AbsensiController::class, 'tapScan'])->name('absensi.tap');
            Route::post('absensi/tap/scan', [AbsensiController::class, 'tapRecord'])->name('absensi.tap.scan');
            Route::get('absensi/list', [AbsensiController::class, 'list'])->name('absensi.list');
            Route::get('absensi/{absensi}/detail', [AbsensiController::class, 'detail'])->name('absensi.detail');
            Route::get('absensi/siswa/{rombel}', [AbsensiController::class, 'getSiswa'])->name('absensi.siswa');
            Route::resource('absensi', AbsensiController::class)->except(['create', 'edit']);

            // Absensi Guru (rekap dari mobile + koreksi manual admin)
            Route::get('absensi-guru/list', [AbsensiGuruController::class, 'list'])->name('absensi-guru.list');
            Route::get('absensi-guru/export', [AbsensiGuruController::class, 'export'])->name('absensi-guru.export');
            Route::get('absensi-guru/rekap-pdf', [AbsensiGuruController::class, 'rekapPdf'])->name('absensi-guru.rekap-pdf');
            Route::get('absensi-guru/{id}/detail', [AbsensiGuruController::class, 'detail'])->name('absensi-guru.detail');
            Route::resource('absensi-guru', AbsensiGuruController::class)->except(['create', 'edit', 'show']);

            // Pengajuan Izin/Sakit Guru
            Route::prefix('pengajuan-izin-guru')->name('pengajuan-izin-guru.')->group(function () {
                Route::get('/', [PengajuanIzinGuruController::class, 'index'])->name('index');
                Route::get('list', [PengajuanIzinGuruController::class, 'list'])->name('list');
                Route::get('{id}/detail', [PengajuanIzinGuruController::class, 'detail'])->name('detail');
                Route::post('{id}/approve', [PengajuanIzinGuruController::class, 'approve'])->name('approve');
                Route::post('{id}/reject', [PengajuanIzinGuruController::class, 'reject'])->name('reject');
            });

            // Input Nilai
            Route::get('nilai', [NilaiController::class, 'index'])->name('nilai.index');
            Route::get('nilai/rekap', [NilaiController::class, 'rekap'])->name('nilai.rekap');
            Route::get('nilai/rekap-pdf', [NilaiController::class, 'rekapPdf'])->name('nilai.rekap-pdf');
            Route::get('nilai/rekap-excel', [NilaiController::class, 'rekapExcel'])->name('nilai.rekap-excel');
            Route::get('nilai/sheet', [NilaiController::class, 'sheet'])->name('nilai.sheet');
            Route::post('nilai/save', [NilaiController::class, 'save'])->name('nilai.save');

            // Setting Akademik
            Route::get('setting', [AkademikSettingController::class, 'index'])->name('setting.index');
            Route::post('setting/{lembagaId}', [AkademikSettingController::class, 'update'])->name('setting.update');

            // Modul Raport
            Route::prefix('raport')->name('raport.')->group(function () {
                // Pengajuan Raport (Wali Kelas / Guru)
                Route::get('pengajuan', [PengajuanRaportController::class, 'index'])->name('pengajuan.index');
                Route::get('pengajuan/list', [PengajuanRaportController::class, 'list'])->name('pengajuan.list');
                Route::post('pengajuan', [PengajuanRaportController::class, 'store'])->name('pengajuan.store');
                Route::get('pengajuan/{id}', [PengajuanRaportController::class, 'show'])->name('pengajuan.show');
                Route::post('pengajuan/{id}/submit', [PengajuanRaportController::class, 'submit'])->name('pengajuan.submit');
                Route::post('pengajuan/{id}/withdraw', [PengajuanRaportController::class, 'withdraw'])->name('pengajuan.withdraw');
                Route::post('pengajuan/{id}/refresh-nilai', [PengajuanRaportController::class, 'refreshNilai'])->name('pengajuan.refresh-nilai');
                Route::put('pengajuan/{id}/nilai', [PengajuanRaportController::class, 'updateNilai'])->name('pengajuan.update-nilai');
                Route::delete('pengajuan/{id}', [PengajuanRaportController::class, 'destroy'])->name('pengajuan.destroy');

                // Verifikasi (Wakasek/Koordinator)
                Route::get('verifikasi', [VerifikasiRaportController::class, 'index'])->name('verifikasi.index');
                Route::get('verifikasi/list', [VerifikasiRaportController::class, 'list'])->name('verifikasi.list');
                Route::post('verifikasi/{id}/verify', [VerifikasiRaportController::class, 'verify'])->name('verifikasi.verify');
                Route::post('verifikasi/{id}/reject', [VerifikasiRaportController::class, 'reject'])->name('verifikasi.reject');

                // Approval (Kepala Sekolah)
                Route::get('approval', [ApprovalRaportController::class, 'index'])->name('approval.index');
                Route::get('approval/list', [ApprovalRaportController::class, 'list'])->name('approval.list');
                Route::post('approval/{id}/approve', [ApprovalRaportController::class, 'approve'])->name('approval.approve');
                Route::post('approval/{id}/reject', [ApprovalRaportController::class, 'reject'])->name('approval.reject');

                // Cetak Raport (PDF & Preview)
                Route::get('{id}/preview', [CetakRaportController::class, 'preview'])->name('preview');
                Route::get('{id}/cetak-satu/{pesertaId}', [CetakRaportController::class, 'cetakSatu'])->name('cetak-satu');
                Route::get('{id}/cetak-semua', [CetakRaportController::class, 'cetakSemua'])->name('cetak-semua');
            });
        });

        // PPDB
        Route::prefix('ppdb')->name('ppdb.')->group(function () {
            Route::get('pembukaan/list', [PembukaanPpdbController::class, 'list'])->name('pembukaan.list');
            Route::post('pembukaan/{id}/toggle-status', [PembukaanPpdbController::class, 'toggleStatus'])->name('pembukaan.toggle');
            Route::post('pembukaan/{id}/duplikasi', [PembukaanPpdbController::class, 'duplikasi'])->name('pembukaan.duplikasi');
            Route::resource('pembukaan', PembukaanPpdbController::class)->except(['create', 'edit']);

            Route::get('jalur/list', [JalurPendaftaranController::class, 'list'])->name('jalur.list');
            Route::post('jalur/{id}/sync-kuota', [JalurPendaftaranController::class, 'syncKuotaJurusan'])->name('jalur.sync_kuota');
            Route::resource('jalur', JalurPendaftaranController::class)->except(['create', 'edit']);

            // Jadwal Pendaftaran
            Route::get('jadwal/list', [JadwalPendaftaranController::class, 'list'])->name('jadwal.list');
            Route::resource('jadwal', JadwalPendaftaranController::class)->except(['create', 'edit']);

            // Syarat Pendaftaran
            Route::get('syarat/list', [SyaratPendaftaranController::class, 'list'])->name('syarat.list');
            Route::post('syarat/reorder', [SyaratPendaftaranController::class, 'reorder'])->name('syarat.reorder');
            Route::resource('syarat', SyaratPendaftaranController::class)->except(['create', 'edit']);

            // Biaya Registrasi
            Route::get('biaya/list', [BiayaRegistrasiController::class, 'list'])->name('biaya.list');
            Route::resource('biaya', BiayaRegistrasiController::class)->except(['create', 'edit']);

            // Template Dokumen
            Route::get('template/list', [TemplateDokumenController::class, 'list'])->name('template.list');
            Route::post('template/{id}/set-aktif', [TemplateDokumenController::class, 'setAktif'])->name('template.set-aktif');
            Route::resource('template', TemplateDokumenController::class)->except(['create', 'edit']);

            // Kuota Jurusan
            Route::get('kuota', [KuotaJurusanController::class, 'index'])->name('kuota.index');
            Route::post('kuota/upsert', [KuotaJurusanController::class, 'upsert'])->name('kuota.upsert');
            Route::get('kuota/status', [KuotaJurusanController::class, 'status'])->name('kuota.status');

            // Formulir Pendaftaran
            Route::get('formulir/list', [FormulirPendaftaranController::class, 'list'])->name('formulir.list');
            Route::post('formulir/{id}/toggle-aktif', [FormulirPendaftaranController::class, 'toggleAktif'])->name('formulir.toggle-aktif');
            Route::get('formulir/{id}/builder', [FormulirPendaftaranController::class, 'builder'])->name('formulir.builder');
            Route::post('formulir/{formulirId}/fields', [FormulirPendaftaranController::class, 'addField'])->name('formulir.field.add');
            Route::post('formulir/{formulirId}/reorder-fields', [FormulirPendaftaranController::class, 'reorderFields'])->name('formulir.field.reorder');
            Route::get('formulir/{formulirId}/preview-fields', [FormulirPendaftaranController::class, 'getFieldsForPendaftaran'])->name('formulir.field.preview');
            Route::resource('formulir', FormulirPendaftaranController::class)->except(['create', 'edit']);
            // Field CRUD routes (independent dari resource formulir)
            Route::get('formulir/field/{fieldId}', [FormulirPendaftaranController::class, 'showField'])->name('formulir.field.show');
            Route::put('formulir/field/{fieldId}', [FormulirPendaftaranController::class, 'updateField'])->name('formulir.field.update');
            Route::delete('formulir/field/{fieldId}', [FormulirPendaftaranController::class, 'deleteField'])->name('formulir.field.delete');
        });

        // Peserta (Data Calon Peserta Didik — Dapodik)
        Route::prefix('peserta')->name('peserta.')->group(function () {
            // Endpoint non-resource harus SEBELUM route berparameter agar tidak konfllik
            Route::get('list', [PesertaController::class, 'list'])->name('list');
            Route::post('import-csv', [PesertaController::class, 'importCsv'])->name('import-csv');
            Route::get('export-csv', [PesertaController::class, 'exportCsv'])->name('export-csv');

            // CRUD — explicit routes dengan parameter {id} yang jelas
            Route::get('/', [PesertaController::class, 'index'])->name('index');
            Route::post('/', [PesertaController::class, 'store'])->name('store');
            Route::get('/{id}', [PesertaController::class, 'show'])->name('show');
            Route::put('/{id}', [PesertaController::class, 'update'])->name('update');
            Route::put('/{id}/orang-tua/{tipe}', [PesertaController::class, 'updateOrangTuaKontak'])->name('orang-tua.update');
            Route::delete('/{id}', [PesertaController::class, 'destroy'])->name('destroy');
        });

        // Pendaftaran & Transaksi
        Route::prefix('pendaftaran')->name('pendaftaran.')->group(function () {
            Route::get('list', [PendaftaranController::class, 'list'])->name('list');
            Route::post('{id}/verifikasi', [PendaftaranController::class, 'verifikasi'])->name('verifikasi');
            Route::post('{id}/dokumen/{dokumenId}/verifikasi', [PendaftaranController::class, 'verifikasiDokumen'])->name('dokumen.verifikasi');
            Route::post('{id}/konfirmasi-siswa-tetap', [PendaftaranController::class, 'konfirmasiSiswaTetap'])->name('konfirmasi-siswa-tetap');
            Route::get('/', [PendaftaranController::class, 'index'])->name('index');
            Route::get('/{id}', [PendaftaranController::class, 'show'])->name('show');
        });

        // Pembayaran
        Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
            Route::get('list', [PembayaranController::class, 'list'])->name('list');
            Route::post('{id}/konfirmasi', [PembayaranController::class, 'konfirmasiManual'])->name('konfirmasi');
            Route::get('/', [PembayaranController::class, 'index'])->name('index');
            Route::get('/{id}', [PembayaranController::class, 'show'])->name('show');
        });

        // Program Kerja
        Route::prefix('program-kerja')->name('program-kerja.')->group(function () {
            Route::get('/', [ProgramKerjaController::class, 'index'])->name('index');
            Route::get('/list', [ProgramKerjaController::class, 'list'])->name('list');
            Route::post('/', [ProgramKerjaController::class, 'store'])->name('store');
            Route::get('/{id}', [ProgramKerjaController::class, 'show'])->name('show');
            Route::put('/{id}', [ProgramKerjaController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProgramKerjaController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/submit', [ProgramKerjaController::class, 'submit'])->name('submit');
            Route::post('/{id}/withdraw', [ProgramKerjaController::class, 'withdraw'])->name('withdraw');
            Route::post('/{id}/verifikasi', [ProgramKerjaController::class, 'verifikasi'])->name('verifikasi');
            Route::post('/{id}/approval', [ProgramKerjaController::class, 'approval'])->name('approval');
            Route::post('/{id}/tolak', [ProgramKerjaController::class, 'tolak'])->name('tolak');
            Route::get('/{id}/cetak', [ProgramKerjaController::class, 'cetak'])->name('cetak');
            Route::post('/{programId}/kegiatan', [ProgramKerjaController::class, 'addKegiatan'])->name('kegiatan.store');
            Route::put('/{programId}/kegiatan/{kegiatanId}', [ProgramKerjaController::class, 'updateKegiatan'])->name('kegiatan.update');
            Route::delete('/{programId}/kegiatan/{kegiatanId}', [ProgramKerjaController::class, 'deleteKegiatan'])->name('kegiatan.destroy');
            Route::put('/{programId}/kegiatan/{kegiatanId}/realisasi', [ProgramKerjaController::class, 'updateRealisasi'])->name('kegiatan.realisasi');
        });

        // Kinerja (KPI Dashboard)
        Route::prefix('kinerja')->name('kinerja.')->group(function () {
            Route::get('/', [KinerjaController::class, 'index'])->name('index');
            Route::get('/dashboard', [KinerjaController::class, 'dashboard'])->name('dashboard');
            Route::get('/manage', [KinerjaController::class, 'manage'])->name('manage');
            Route::get('/list', [KinerjaController::class, 'list'])->name('list');
            Route::post('/', [KinerjaController::class, 'store'])->name('store');
            Route::get('/{id}', [KinerjaController::class, 'show'])->name('show');
            Route::put('/{id}', [KinerjaController::class, 'update'])->name('update');
            Route::delete('/{id}', [KinerjaController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/realisasi', [KinerjaController::class, 'inputRealisasi'])->name('inputRealisasi');
            Route::post('/sync-auto', [KinerjaController::class, 'syncAuto'])->name('syncAuto');
        });

        // Seleksi
        Route::prefix('seleksi')->name('seleksi.')->group(function () {
            Route::get('jalur/{jalurId}', [SeleksiController::class, 'index'])->name('index');
            Route::get('pendaftaran/{pendaftaranId}/penilaian', [SeleksiController::class, 'penilaian'])->name('penilaian');
            Route::post('pendaftaran/{pendaftaranId}/nilai', [SeleksiController::class, 'inputNilai'])->name('nilai.store');
            Route::post('jalur/{jalurId}/hitung-ranking', [SeleksiController::class, 'hitungRanking'])->name('hitung-ranking');
            Route::get('jalur/{jalurId}/hasil', [SeleksiController::class, 'hasil'])->name('hasil');
            Route::post('jalur/{jalurId}/pengumuman', [SeleksiController::class, 'pengumuman'])->name('pengumuman');
            Route::get('jalur/{jalurId}/download-pengumuman', [SeleksiController::class, 'downloadPengumuman'])->name('download-pengumuman');
            Route::get('pendaftaran/{pendaftaranId}/download-kartu', [SeleksiController::class, 'downloadKartu'])->name('download-kartu');
        });
    });
});

// =========================================================================
// WEBHOOK ROUTES — Tanpa auth middleware (server-to-server)
// =========================================================================
// Rate limit: 60 requests per minute per IP
// CSRF: excluded via bootstrap/app.php validateCsrfTokens
Route::prefix('webhook')
    ->name('webhook.')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::post('midtrans', [WebhookController::class, 'midtrans'])->name('midtrans');
    });

// =============================================================================
// PORTAL PESERTA PPDB — Route Terpisah dari Admin Panel
// Guard: web (sama), Middleware: peserta.auth & peserta.aktif (BUKAN permission)
// =============================================================================

Route::prefix('ppdb')->name('ppdb.')->group(function () {

    // === PUBLIK (tidak perlu login) ===
    Route::get('/', [PortalController::class, 'beranda'])->name('beranda');
    Route::get('/info', [PortalController::class, 'info'])->name('info');

    // === AUTH ROUTES — guest only ===
    Route::middleware('guest')->group(function () {
        Route::get('/register', [AuthPesertaController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthPesertaController::class, 'register'])->name('register.post');
        Route::get('/login', [AuthPesertaController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthPesertaController::class, 'login'])->name('login.post');
    });

    // === VERIFIKASI EMAIL (perlu login peserta, belum perlu aktif) ===
    Route::middleware('peserta.auth')->group(function () {
        Route::get('/verify-email', [AuthPesertaController::class, 'showVerifyEmail'])->name('verify-email');
        Route::post('/verify-email', [AuthPesertaController::class, 'verifyEmail'])->name('verify-email.post');
        Route::post('/resend-otp', [AuthPesertaController::class, 'resendOtp'])->name('resend-otp');
        Route::post('/logout', [AuthPesertaController::class, 'logout'])->name('logout');
    });

    // === AREA TERAUTENTIKASI + AKTIF ===
    Route::middleware(['peserta.auth', 'peserta.aktif'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardPesertaController::class, 'index'])->name('dashboard');

        // Profil & Data Dapodik
        Route::prefix('/profil')->name('profil.')->group(function () {
            Route::get('/', [ProfilPesertaController::class, 'index'])->name('index');
            Route::put('/akun', [ProfilPesertaController::class, 'updateAkun'])->name('akun');
            Route::put('/dapodik', [ProfilPesertaController::class, 'updateDapodik'])->name('dapodik');
            Route::put('/password', [ProfilPesertaController::class, 'updatePassword'])->name('password');
        });

        // Pendaftaran
        Route::prefix('/pendaftaran')->name('pendaftaran.')->group(function () {
            Route::get('/', [PendaftaranPesertaController::class, 'index'])->name('index');
            Route::get('/pilih-jalur', [PendaftaranPesertaController::class, 'pilihJalur'])->name('pilih');
            Route::post('/pilih-jalur', [PendaftaranPesertaController::class, 'store'])->name('store');
            Route::get('/{id}', [PendaftaranPesertaController::class, 'show'])->name('show');
            Route::post('/{id}/formulir', [PendaftaranPesertaController::class, 'saveFormulir'])->name('formulir');
            Route::post('/{id}/dokumen/{syaratId}', [PendaftaranPesertaController::class, 'uploadDokumen'])->name('dokumen.upload');
            Route::delete('/{id}/dokumen/{dokumenId}', [PendaftaranPesertaController::class, 'hapusDokumen'])->name('dokumen.hapus');
            Route::post('/{id}/submit', [PendaftaranPesertaController::class, 'submit'])->name('submit');
        });

        // Pembayaran — index & token (placeholder M7) + konfirmasi manual (aktif sekarang)
        Route::prefix('/pembayaran')->name('pembayaran.')->group(function () {
            Route::get('/{id}', [PembayaranPesertaController::class, 'index'])->name('index');
            Route::post('/{id}/token', [PembayaranPesertaController::class, 'getToken'])->name('token');
            Route::post('/{id}/sync', [PembayaranPesertaController::class, 'syncStatus'])->name('sync');
            Route::post('/{id}/cancel-pending', [PembayaranPesertaController::class, 'cancelPending'])->name('cancel-pending');
            Route::post('/{id}/konfirmasi-manual', [PembayaranPesertaController::class, 'konfirmasiManual'])->name('konfirmasi-manual');
        });

        // Pengumuman
        Route::prefix('/pengumuman')->name('pengumuman.')->group(function () {
            Route::get('/', [PengumumanPesertaController::class, 'index'])->name('index');
            Route::get('/{pendaftaranId}/kartu', [PengumumanPesertaController::class, 'downloadKartu'])->name('download');
        });

        // Daftar Ulang
        Route::prefix('/daftar-ulang')->name('daftar-ulang.')->group(function () {
            Route::get('/{pendaftaranId}', [DaftarUlangPesertaController::class, 'index'])->name('index');
            Route::post('/{pendaftaranId}', [DaftarUlangPesertaController::class, 'store'])->name('store');
        });

        // Raport Online
        Route::prefix('/raport')->name('raport.')->group(function () {
            Route::get('/', [RaportPesertaController::class, 'index'])->name('index');
            Route::get('/{pengajuanId}', [RaportPesertaController::class, 'show'])->name('show');
            Route::get('/{pengajuanId}/download', [RaportPesertaController::class, 'download'])->name('download');
        });
    });
});

// =============================================================================
// TEST NOTIFIKASI ROUTE
// =============================================================================
Route::get('/test-notif', function () {
    try {
        // Default target user ID 1
        $userId = 13;

        app(NotifikasiService::class)->kirim($userId, 'pendaftaran_submit', [
            'no_pendaftaran' => 'PPDB202600001',
            'nama_peserta' => 'Ahmad',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi pendaftaran_submit berhasil di-dispatch ke queue.',
            'info' => 'Silakan cek tabel `jobs` (karena queue driver database) atau jalankan `php artisan queue:work`.',
            'payload' => [
                'user_id' => $userId,
                'event' => 'pendaftaran_submit',
                'no_pendaftaran' => 'PPDB202600001',
                'nama_peserta' => 'Ahmad',
            ],
        ]);

        Log::info('Notifikasi pendaftaran_submit berhasil di-dispatch ke queue.', [
            'user_id' => $userId,
            'event' => 'pendaftaran_submit',
            'no_pendaftaran' => 'PPDB202600001',
            'nama_peserta' => 'Ahmad',
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim notifikasi: '.$e->getMessage(),
        ], 500);
    }
});
