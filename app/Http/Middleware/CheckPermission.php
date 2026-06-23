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
        'admin.master.rombel.guru-by-lembaga'    => 'admin.master.rombel.index',
        'admin.master.guru.list'                 => 'admin.master.guru.index',
        'admin.master.jurusan-mapel.show'        => 'admin.master.jurusan-mapel.index',
        'admin.master.jurusan-mapel.sync'        => 'admin.master.jurusan-mapel.index',
        'admin.master.jadwal-kbm.list'           => 'admin.master.jadwal-kbm.index',
        'admin.master.rombel-siswa.by-rombel'    => 'admin.master.rombel-siswa.index',
        'admin.master.rombel-siswa.assign'       => 'admin.master.rombel-siswa.index',
        'admin.master.rombel-siswa.unassign'     => 'admin.master.rombel-siswa.index',
        'admin.master.rombel-siswa.update-absen' => 'admin.master.rombel-siswa.index',

        // Akademik — helper routes
        'admin.akademik.perangkat-mengajar.list'  => 'admin.akademik.perangkat-mengajar.index',
        'admin.akademik.materi-belajar.list'       => 'admin.akademik.materi-belajar.index',
        'admin.akademik.absensi.list'              => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.detail'            => 'admin.akademik.absensi.index',
        'admin.akademik.absensi.siswa'             => 'admin.akademik.absensi.index',
        'admin.akademik.nilai.sheet'               => 'admin.akademik.nilai.index',
        'admin.akademik.nilai.save'                => 'admin.akademik.nilai.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 401,
                    'message' => 'Unauthenticated. Please login.',
                ], 401);
            }
            return redirect()->route('login');
        }

        $routeName = $request->route()?->getName();

        if ($routeName) {
            // Cek alias dulu; jika ada, gunakan permission induknya
            $checkAs = $this->permissionAliases[$routeName] ?? $routeName;

            if (!auth()->user()->hasPermissionTo($checkAs)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status'  => 403,
                        'message' => 'Unauthorized Access. You do not have permission to access ' . $routeName,
                    ], 403);
                }

                abort(403, 'Unauthorized Access. You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }
}
