<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Route-name aliases: helper/AJAX routes yang permission-nya
     * didelegasikan ke route induk. Tambahkan di sini agar tidak
     * perlu entry DB baru setiap ada route helper baru.
     */
    protected array $permissionAliases = [
        'admin.master.rombel.guru-by-lembaga' => 'admin.master.rombel.index',
        'admin.master.guru.list' => 'admin.master.guru.index',
        'admin.master.kalender-libur.list' => 'admin.master.kalender-libur.index',
        'admin.master.kalender-libur.show' => 'admin.master.kalender-libur.index',
        'admin.master.kalender-libur.rentang' => 'admin.master.kalender-libur.index',
        'admin.master.kalender-libur.store' => 'admin.master.kalender-libur.index',
        'admin.master.kalender-libur.update' => 'admin.master.kalender-libur.index',
        'admin.master.kalender-libur.destroy' => 'admin.master.kalender-libur.index',
        'admin.master.guru.akses-mobile.reset-password' => 'admin.master.guru.akses-mobile',
        'admin.master.guru.akses-mobile.toggle-status' => 'admin.master.guru.akses-mobile',
        'admin.master.guru.akses-mobile.revoke-sesi' => 'admin.master.guru.akses-mobile',
        'admin.master.jurusan-mapel.show' => 'admin.master.jurusan-mapel.index',
        'admin.master.jurusan-mapel.sync' => 'admin.master.jurusan-mapel.index',
        'admin.master.jadwal-kbm.list' => 'admin.master.jadwal-kbm.index',
        'admin.master.rombel-siswa.by-rombel' => 'admin.master.rombel-siswa.index',
        'admin.master.rombel-siswa.assign' => 'admin.master.rombel-siswa.index',
        'admin.master.rombel-siswa.unassign' => 'admin.master.rombel-siswa.index',
        'admin.master.rombel-siswa.update-absen' => 'admin.master.rombel-siswa.index',
        'admin.master.siswa.list' => 'admin.master.siswa.index',
        'admin.master.siswa.stats' => 'admin.master.siswa.index',
        'admin.master.siswa.export' => 'admin.master.siswa.index',

        // Akademik — helper routes
        'admin.akademik.perangkat-mengajar.list' => 'admin.akademik.perangkat-mengajar.index',
        'admin.akademik.materi-belajar.list' => 'admin.akademik.materi-belajar.index',
        'admin.akademik.verifikasi-materi.list' => 'admin.akademik.verifikasi-materi.index',
        'admin.akademik.verifikasi-materi.aksi' => 'admin.akademik.verifikasi-materi.index',
        'admin.akademik.absensi.list' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.detail' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.siswa' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.rekap' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.rekap-pdf' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.rekap-excel' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.tap' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.tap.scan' => 'admin.akademik.absensi.index',
        'admin.akademik.absensi-guru.list' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.absensi-guru.detail' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.absensi-guru.export' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.absensi-guru.rekap-pdf' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.absensi-guru.store' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.absensi-guru.update' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.absensi-guru.destroy' => 'admin.akademik.absensi-guru.index',
        'admin.akademik.pengajuan-izin-guru.list' => 'admin.akademik.pengajuan-izin-guru.index',
        'admin.akademik.pengajuan-izin-guru.detail' => 'admin.akademik.pengajuan-izin-guru.index',
        'admin.akademik.pengajuan-izin-guru.approve' => 'admin.akademik.pengajuan-izin-guru.index',
        'admin.akademik.pengajuan-izin-guru.reject' => 'admin.akademik.pengajuan-izin-guru.index',
        'admin.akademik.nilai.sheet' => 'admin.akademik.nilai.index',
        'admin.akademik.nilai.save' => 'admin.akademik.nilai.index',
        'admin.akademik.nilai.rekap' => 'admin.akademik.nilai.index',
        'admin.akademik.nilai.rekap-pdf' => 'admin.akademik.nilai.index',
        'admin.akademik.nilai.rekap-excel' => 'admin.akademik.nilai.index',

        // Raport — helper / action routes delegate to index
        'admin.akademik.raport.pengajuan.list' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.store' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.show' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.submit' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.withdraw' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.refresh-nilai' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.update-nilai' => 'admin.akademik.raport.pengajuan.index',
        'admin.akademik.raport.pengajuan.destroy' => 'admin.akademik.raport.pengajuan.index',

        // Verifikasi & Approval helper routes
        'admin.akademik.raport.verifikasi.list' => 'admin.akademik.raport.verifikasi.index',
        'admin.akademik.raport.verifikasi.verify' => 'admin.akademik.raport.verifikasi.index',
        'admin.akademik.raport.verifikasi.reject' => 'admin.akademik.raport.verifikasi.index',
        'admin.akademik.raport.approval.list' => 'admin.akademik.raport.approval.index',
        'admin.akademik.raport.approval.approve' => 'admin.akademik.raport.approval.index',
        'admin.akademik.raport.approval.reject' => 'admin.akademik.raport.approval.index',

        // Setting & Raport cetak
        'admin.akademik.setting.update' => 'admin.akademik.setting.index',
        'admin.akademik.raport.preview' => 'admin.akademik.raport.cetak-satu',
        'admin.akademik.raport.cetak-semua' => 'admin.akademik.raport.cetak-satu',

        // Program Kerja
        'admin.program-kerja.list' => 'admin.program-kerja.index',
        'admin.program-kerja.show' => 'admin.program-kerja.index',
        'admin.program-kerja.update' => 'admin.program-kerja.index',
        'admin.program-kerja.destroy' => 'admin.program-kerja.index',
        'admin.program-kerja.store' => 'admin.program-kerja.index',
        'admin.program-kerja.submit' => 'admin.program-kerja.index',
        'admin.program-kerja.withdraw' => 'admin.program-kerja.index',
        'admin.program-kerja.verifikasi' => 'admin.program-kerja.index',
        'admin.program-kerja.approval' => 'admin.program-kerja.index',
        'admin.program-kerja.tolak' => 'admin.program-kerja.index',
        'admin.program-kerja.cetak' => 'admin.program-kerja.index',
        'admin.program-kerja.kegiatan.store' => 'admin.program-kerja.index',
        'admin.program-kerja.kegiatan.update' => 'admin.program-kerja.index',
        'admin.program-kerja.kegiatan.destroy' => 'admin.program-kerja.index',
        'admin.program-kerja.kegiatan.realisasi' => 'admin.program-kerja.index',
        // Pendaftaran admin actions
        'admin.ppdb.pendaftaran.list' => 'admin.ppdb.pendaftaran.index',
        'admin.ppdb.pendaftaran.show' => 'admin.ppdb.pendaftaran.index',
        'admin.ppdb.pendaftaran.verifikasi' => 'admin.ppdb.pendaftaran.index',
        'admin.ppdb.pendaftaran.dokumen.verifikasi' => 'admin.ppdb.pendaftaran.index',
        'admin.ppdb.pendaftaran.konfirmasi-siswa-tetap' => 'admin.ppdb.pendaftaran.index',

        // Kinerja
        'admin.kinerja.dashboard' => 'admin.kinerja.index',
        'admin.kinerja.manage' => 'admin.kinerja.index',
        'admin.kinerja.list' => 'admin.kinerja.index',
        'admin.kinerja.show' => 'admin.kinerja.index',
        'admin.kinerja.store' => 'admin.kinerja.index',
        'admin.kinerja.update' => 'admin.kinerja.index',
        'admin.kinerja.destroy' => 'admin.kinerja.index',
        'admin.kinerja.inputRealisasi' => 'admin.kinerja.index',
        'admin.kinerja.syncAuto' => 'admin.kinerja.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthenticated. Please login.',
                ], 401);
            }

            return redirect()->route('login');
        }

        $routeName = $request->route()?->getName();

        if ($routeName) {
            // Cek alias dulu; jika ada, gunakan permission induknya
            $checkAs = $this->permissionAliases[$routeName] ?? $routeName;

            if (! auth()->user()->hasPermissionTo($checkAs)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 403,
                        'message' => 'Unauthorized Access. You do not have permission to access '.$routeName,
                    ], 403);
                }

                abort(403, 'Unauthorized Access. You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }
}
