<?php

namespace App\Http\Middleware;

use App\Services\PesertaAccountService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PesertaAuth
{
    public function __construct(protected PesertaAccountService $pesertaAccountService) {}

    /**
     * Pastikan request berasal dari user yang:
     * 1. Sudah login (auth check)
     * 2. Memiliki role 'peserta' (bukan admin/guru/dll)
     *
     * CATATAN: Middleware ini TIDAK menggunakan checkPermission.
     * checkPermission hanya dipakai untuk admin panel (route-based).
     * Portal peserta menggunakan autentikasi berbasis role saja.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('ppdb.login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!$this->pesertaAccountService->isPeserta(auth()->user())) {
            auth()->logout();

            return redirect()->route('ppdb.login')
                ->with('error', 'Akun ini bukan akun peserta PPDB.');
        }

        return $next($request);
    }
}
