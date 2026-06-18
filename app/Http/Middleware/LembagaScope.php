<?php

namespace App\Http\Middleware;

use App\Models\Master\Lembaga;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LembagaScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $lembagaIds = $user->getLembagaIds();
        $isSuperAdmin = $user->isSuperAdmin();

        if ($isSuperAdmin) {
            // Super admin: gunakan active_lembaga_id dari session (bisa null = lihat semua)
            $activeLembagaId = session('active_lembaga_id');
        } elseif (count($lembagaIds) === 1) {
            // Admin satu lembaga: auto-set
            $activeLembagaId = $lembagaIds[0];
            session(['active_lembaga_id' => $activeLembagaId]);
        } else {
            // Admin multi-lembaga: gunakan session atau default ke yang pertama
            $activeLembagaId = session('active_lembaga_id');
            if ($activeLembagaId && ! in_array($activeLembagaId, $lembagaIds)) {
                $activeLembagaId = $lembagaIds[0] ?? null;
                session(['active_lembaga_id' => $activeLembagaId]);
            }
        }

        // Bind ke container agar bisa di-inject via app('active_lembaga_id')
        app()->bind('active_lembaga_id', function () use ($activeLembagaId) {
            return $activeLembagaId;
        });

        // Share ke semua views
        if ($activeLembagaId) {
            $activeLembaga = Lembaga::find($activeLembagaId);
            view()->share('activeLembaga', $activeLembaga);
        } else {
            view()->share('activeLembaga', null);
        }

        view()->share('isSuperAdmin', $isSuperAdmin);
        view()->share('userLembagaIds', $lembagaIds);

        return $next($request);
    }
}
