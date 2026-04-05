<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PesertaAktif
{
    /**
     * Verifikasi status akun peserta.
     *
     * Di-chain setelah PesertaAuth (yang sudah menjamin user login & role peserta).
     * Middleware ini hanya menangani state transisi status akun:
     *
     * - 'pending' → redirect ke halaman verifikasi OTP
     * - 'active'  → lanjutkan request
     * - lainnya   → logout dan tampilkan pesan error (suspended, banned, dll)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user->status === 'pending') {
            return redirect()->route('ppdb.verify-email')
                ->with('info', 'Akun Anda belum diverifikasi. Masukkan kode OTP yang dikirim ke email.');
        }

        if ($user->status !== 'active') {
            auth()->logout();

            return redirect()->route('ppdb.login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi admin.');
        }

        return $next($request);
    }
}
