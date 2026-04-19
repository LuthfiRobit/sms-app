<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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

Route::middleware(['auth', 'permission'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        // RBAC System
        Route::prefix('rbac')->name('rbac.')->group(function () {
            // Roles
            Route::get('role/list', [\App\Http\Controllers\Rbac\RoleController::class, 'list'])->name('role.list');
            Route::get('role/{role}/permissions', [\App\Http\Controllers\Rbac\RoleController::class, 'getPermissions'])->name('role.permissions.list');
            Route::post('role/{role}/permissions', [\App\Http\Controllers\Rbac\RoleController::class, 'assignPermissions'])->name('role.permissions.assign');
            Route::resource('role', \App\Http\Controllers\Rbac\RoleController::class)->except(['create', 'edit']);
            Route::get('role/{role}/edit', [\App\Http\Controllers\Rbac\RoleController::class, 'edit'])->name('role.edit');

            // Permissions
            Route::get('permission/list', [\App\Http\Controllers\Rbac\PermissionController::class, 'list'])->name('permission.list');
            Route::post('permission/{permission}/toggle-status', [\App\Http\Controllers\Rbac\PermissionController::class, 'toggleStatus'])->name('permission.toggle');
            Route::resource('permission', \App\Http\Controllers\Rbac\PermissionController::class)->except(['create', 'edit']);

            // Users Role Management
            Route::get('user/list', [\App\Http\Controllers\Rbac\UserController::class, 'list'])->name('user.list');
            Route::post('user/{user}/toggle-status', [\App\Http\Controllers\Rbac\UserController::class, 'toggleStatus'])->name('user.toggle');
            Route::resource('user', \App\Http\Controllers\Rbac\UserController::class)->except(['create', 'edit']);
        });

        // System Routes
        Route::prefix('system')->name('system.')->group(function () {
            Route::post('sync-permissions', [\App\Http\Controllers\System\PermissionSyncController::class, 'sync'])->name('sync-permissions');

            Route::get('log-activity/list', [\App\Http\Controllers\System\LogActivityController::class, 'list'])->name('log-activity.list');
            Route::delete('log-activity/delete-all', [\App\Http\Controllers\System\LogActivityController::class, 'deleteAll'])->name('log-activity.delete-all');
            Route::resource('log-activity', \App\Http\Controllers\System\LogActivityController::class)->only(['index', 'show']);
        });

        // Notifikasi Routes
        Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\NotifikasiController::class, 'index'])->name('index');
            Route::get('/unread-count', [\App\Http\Controllers\NotifikasiController::class, 'getUnreadCount'])->name('unread-count');
            Route::post('/{id}/read', [\App\Http\Controllers\NotifikasiController::class, 'markRead'])->name('read');
            Route::post('/mark-all-read', [\App\Http\Controllers\NotifikasiController::class, 'markAllRead'])->name('mark-all-read');
        });

        // Master Data
        Route::prefix('master')->name('master.')->group(function () {
            // Profil Sekolah
            Route::get('profil-sekolah', [\App\Http\Controllers\Master\ProfilSekolahController::class, 'index'])->name('profil-sekolah.index');
            Route::post('profil-sekolah', [\App\Http\Controllers\Master\ProfilSekolahController::class, 'store'])->name('profil-sekolah.store');

            // Kurikulum
            Route::get('kurikulum/list', [\App\Http\Controllers\Master\KurikulumController::class, 'list'])->name('kurikulum.list');
            Route::post('kurikulum/{kurikulum}/toggle-status', [\App\Http\Controllers\Master\KurikulumController::class, 'toggleStatus'])->name('kurikulum.toggle');
            Route::resource('kurikulum', \App\Http\Controllers\Master\KurikulumController::class)->except(['create', 'edit']);

            // Tahun Pelajaran & Semester
            Route::get('tahun-pelajaran/list', [\App\Http\Controllers\Master\TahunPelajaranController::class, 'list'])->name('tahun-pelajaran.list');
            Route::post('tahun-pelajaran/{tahun_pelajaran}/toggle-status', [\App\Http\Controllers\Master\TahunPelajaranController::class, 'toggleStatus'])->name('tahun-pelajaran.toggle');
            Route::resource('tahun-pelajaran', \App\Http\Controllers\Master\TahunPelajaranController::class)->except(['create', 'edit']);

            // Semester
            Route::get('semester/list', [\App\Http\Controllers\Master\SemesterController::class, 'list'])->name('semester.list');
            Route::post('semester/{semester}/toggle-status', [\App\Http\Controllers\Master\SemesterController::class, 'toggleStatus'])->name('semester.toggle');
            Route::resource('semester', \App\Http\Controllers\Master\SemesterController::class)->except(['create', 'edit']);
        });

        // PPDB
        Route::prefix('ppdb')->name('ppdb.')->group(function () {
            Route::get('pembukaan/list', [App\Http\Controllers\Ppdb\PembukaanPpdbController::class, 'list'])->name('pembukaan.list');
            Route::post('pembukaan/{id}/toggle-status', [App\Http\Controllers\Ppdb\PembukaanPpdbController::class, 'toggleStatus'])->name('pembukaan.toggle');
            Route::resource('pembukaan', App\Http\Controllers\Ppdb\PembukaanPpdbController::class)->except(['create', 'edit']);

            Route::get('jalur/list', [App\Http\Controllers\Ppdb\JalurPendaftaranController::class, 'list'])->name('jalur.list');
            Route::post('jalur/{id}/sync-kuota', [App\Http\Controllers\Ppdb\JalurPendaftaranController::class, 'syncKuotaJurusan'])->name('jalur.sync_kuota');
            Route::resource('jalur', App\Http\Controllers\Ppdb\JalurPendaftaranController::class)->except(['create', 'edit']);

            // Jadwal Pendaftaran
            Route::get('jadwal/list', [App\Http\Controllers\Ppdb\JadwalPendaftaranController::class, 'list'])->name('jadwal.list');
            Route::resource('jadwal', App\Http\Controllers\Ppdb\JadwalPendaftaranController::class)->except(['create', 'edit']);

            // Syarat Pendaftaran
            Route::get('syarat/list', [App\Http\Controllers\Ppdb\SyaratPendaftaranController::class, 'list'])->name('syarat.list');
            Route::post('syarat/reorder', [App\Http\Controllers\Ppdb\SyaratPendaftaranController::class, 'reorder'])->name('syarat.reorder');
            Route::resource('syarat', App\Http\Controllers\Ppdb\SyaratPendaftaranController::class)->except(['create', 'edit']);

            // Biaya Registrasi
            Route::get('biaya/list', [App\Http\Controllers\Ppdb\BiayaRegistrasiController::class, 'list'])->name('biaya.list');
            Route::resource('biaya', App\Http\Controllers\Ppdb\BiayaRegistrasiController::class)->except(['create', 'edit']);

            // Template Dokumen
            Route::get('template/list', [App\Http\Controllers\Ppdb\TemplateDokumenController::class, 'list'])->name('template.list');
            Route::post('template/{id}/set-aktif', [App\Http\Controllers\Ppdb\TemplateDokumenController::class, 'setAktif'])->name('template.set-aktif');
            Route::resource('template', App\Http\Controllers\Ppdb\TemplateDokumenController::class)->except(['create', 'edit']);

            // Kuota Jurusan
            Route::get('kuota', [App\Http\Controllers\Ppdb\KuotaJurusanController::class, 'index'])->name('kuota.index');
            Route::post('kuota/upsert', [App\Http\Controllers\Ppdb\KuotaJurusanController::class, 'upsert'])->name('kuota.upsert');
            Route::get('kuota/status', [App\Http\Controllers\Ppdb\KuotaJurusanController::class, 'status'])->name('kuota.status');

            // Formulir Pendaftaran
            Route::get('formulir/list', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'list'])->name('formulir.list');
            Route::post('formulir/{id}/toggle-aktif', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'toggleAktif'])->name('formulir.toggle-aktif');
            Route::get('formulir/{id}/builder', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'builder'])->name('formulir.builder');
            Route::post('formulir/{formulirId}/fields', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'addField'])->name('formulir.field.add');
            Route::post('formulir/{formulirId}/reorder-fields', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'reorderFields'])->name('formulir.field.reorder');
            Route::get('formulir/{formulirId}/preview-fields', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'getFieldsForPendaftaran'])->name('formulir.field.preview');
            Route::resource('formulir', App\Http\Controllers\Ppdb\FormulirPendaftaranController::class)->except(['create', 'edit']);
            // Field CRUD routes (independent dari resource formulir)
            Route::get('formulir/field/{fieldId}', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'showField'])->name('formulir.field.show');
            Route::put('formulir/field/{fieldId}', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'updateField'])->name('formulir.field.update');
            Route::delete('formulir/field/{fieldId}', [App\Http\Controllers\Ppdb\FormulirPendaftaranController::class, 'deleteField'])->name('formulir.field.delete');
        });

        // Peserta (Data Calon Peserta Didik — Dapodik)
        Route::prefix('peserta')->name('peserta.')->group(function () {
            // Endpoint non-resource harus SEBELUM route berparameter agar tidak konfllik
            Route::get('list', [App\Http\Controllers\Peserta\PesertaController::class, 'list'])->name('list');
            Route::get('list', [App\Http\Controllers\Peserta\PesertaController::class, 'list'])->name('list');
            Route::post('import-csv', [App\Http\Controllers\Peserta\PesertaController::class, 'importCsv'])->name('import-csv');
            Route::get('export-csv', [App\Http\Controllers\Peserta\PesertaController::class, 'exportCsv'])->name('export-csv');
            Route::get('export-csv', [App\Http\Controllers\Peserta\PesertaController::class, 'exportCsv'])->name('export-csv');
            // CRUD — explicit routes dengan parameter {id} yang jelas
            Route::get('/', [App\Http\Controllers\Peserta\PesertaController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Peserta\PesertaController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Peserta\PesertaController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Peserta\PesertaController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Peserta\PesertaController::class, 'destroy'])->name('destroy');
            Route::get('/', [App\Http\Controllers\Peserta\PesertaController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Peserta\PesertaController::class, 'store'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Peserta\PesertaController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Peserta\PesertaController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Peserta\PesertaController::class, 'destroy'])->name('destroy');
        });


        // Pendaftaran & Transaksi
        Route::prefix('pendaftaran')->name('pendaftaran.')->group(function () {
            Route::get('list', [App\Http\Controllers\Transaksi\PendaftaranController::class, 'list'])->name('list');
            Route::post('{id}/verifikasi', [App\Http\Controllers\Transaksi\PendaftaranController::class, 'verifikasi'])->name('verifikasi');
            Route::post('{id}/dokumen/{dokumenId}/verifikasi', [App\Http\Controllers\Transaksi\PendaftaranController::class, 'verifikasiDokumen'])->name('dokumen.verifikasi');
            Route::get('/', [App\Http\Controllers\Transaksi\PendaftaranController::class, 'index'])->name('index');
            Route::get('/{id}', [App\Http\Controllers\Transaksi\PendaftaranController::class, 'show'])->name('show');
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
        Route::post('midtrans', [App\Http\Controllers\Webhook\WebhookController::class, 'midtrans'])->name('midtrans');
    });

// =============================================================================
// PORTAL PESERTA PPDB — Route Terpisah dari Admin Panel
// Guard: web (sama), Middleware: peserta.auth & peserta.aktif (BUKAN permission)
// =============================================================================
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\AuthPesertaController;
use App\Http\Controllers\Portal\DashboardPesertaController;
use App\Http\Controllers\Portal\ProfilPesertaController;
use App\Http\Controllers\Portal\PendaftaranPesertaController;
use App\Http\Controllers\Portal\PembayaranPesertaController;
use App\Http\Controllers\Portal\PengumumanPesertaController;
use App\Http\Controllers\Portal\DaftarUlangPesertaController;

Route::prefix('ppdb')->name('ppdb.')->group(function () {

    // === PUBLIK (tidak perlu login) ===
    Route::get('/', [PortalController::class, 'beranda'])->name('beranda');
    Route::get('/', [PortalController::class, 'beranda'])->name('beranda');
    Route::get('/info', [PortalController::class, 'info'])->name('info');

    // === AUTH ROUTES — guest only ===
    Route::middleware('guest')->group(function () {
        Route::get('/register', [AuthPesertaController::class, 'showRegister'])->name('register');
        Route::get('/register', [AuthPesertaController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthPesertaController::class, 'register'])->name('register.post');
        Route::get('/login', [AuthPesertaController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthPesertaController::class, 'login'])->name('login.post');
        Route::get('/login', [AuthPesertaController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthPesertaController::class, 'login'])->name('login.post');
    });

    // === VERIFIKASI EMAIL (perlu login peserta, belum perlu aktif) ===
    Route::middleware('peserta.auth')->group(function () {
        Route::get('/verify-email', [AuthPesertaController::class, 'showVerifyEmail'])->name('verify-email');
        Route::get('/verify-email', [AuthPesertaController::class, 'showVerifyEmail'])->name('verify-email');
        Route::post('/verify-email', [AuthPesertaController::class, 'verifyEmail'])->name('verify-email.post');
        Route::post('/resend-otp', [AuthPesertaController::class, 'resendOtp'])->name('resend-otp');
        Route::post('/logout', [AuthPesertaController::class, 'logout'])->name('logout');
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
            Route::get('/{id}', [PembayaranPesertaController::class, 'index'])->name('index');
            Route::post('/{id}/token', [PembayaranPesertaController::class, 'getToken'])->name('token');
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
            Route::get('/{pendaftaranId}', [DaftarUlangPesertaController::class, 'index'])->name('index');
            Route::post('/{pendaftaranId}', [DaftarUlangPesertaController::class, 'store'])->name('store');
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

        app(\App\Services\NotifikasiService::class)->kirim($userId, 'pendaftaran_submit', [
            'no_pendaftaran' => 'PPDB202600001',
            'nama_peserta' => 'Ahmad'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi pendaftaran_submit berhasil di-dispatch ke queue.',
            'info' => 'Silakan cek tabel `jobs` (karena queue driver database) atau jalankan `php artisan queue:work`.',
            'payload' => [
                'user_id' => $userId,
                'event' => 'pendaftaran_submit',
                'no_pendaftaran' => 'PPDB202600001',
                'nama_peserta' => 'Ahmad'
            ]
        ]);

        Log::info('Notifikasi pendaftaran_submit berhasil di-dispatch ke queue.', [
            'user_id' => $userId,
            'event' => 'pendaftaran_submit',
            'no_pendaftaran' => 'PPDB202600001',
            'nama_peserta' => 'Ahmad'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim notifikasi: ' . $e->getMessage()
        ], 500);
    }
});

