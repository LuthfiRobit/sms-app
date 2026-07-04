<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;

trait ResolvesGuru
{
    /** Resolve record guru dari user yang login; abort 403 jika tidak tertaut. */
    private function resolveGuru(Request $request)
    {
        $guru = $request->user()->guru()->with('lembaga')->first();

        abort_if(! $guru, 403, 'Akun ini tidak tertaut ke data guru manapun.');

        return $guru;
    }
}
