<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Admin panel — route-based permission check
            'permission'    => \App\Http\Middleware\CheckPermission::class,
            // Multi-tenant lembaga scope
            'lembaga.scope' => \App\Http\Middleware\LembagaScope::class,
            // Portal peserta PPDB — role & status check
            'peserta.auth'  => \App\Http\Middleware\PesertaAuth::class,
            'peserta.aktif' => \App\Http\Middleware\PesertaAktif::class,
        ]);

        // Exclude webhook routes from CSRF verification
        // Midtrans sends POST callbacks without CSRF token
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
