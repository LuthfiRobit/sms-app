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

            // // Template Dokumen
            // Route::get('template/list', [App\Http\Controllers\Ppdb\TemplateDokumenController::class, 'list'])->name('template.list');
            // Route::post('template/{id}/toggle-status', [App\Http\Controllers\Ppdb\TemplateDokumenController::class, 'toggleStatus'])->name('template.toggle');
            // Route::resource('template', App\Http\Controllers\Ppdb\TemplateDokumenController::class)->except(['create', 'edit']);

            // // Kuota Jurusan Standalone
            // Route::get('kuota/list', [App\Http\Controllers\Ppdb\KuotaJurusanController::class, 'list'])->name('kuota.list');
            // Route::resource('kuota', App\Http\Controllers\Ppdb\KuotaJurusanController::class)->except(['create', 'edit']);
        });

    });
});
