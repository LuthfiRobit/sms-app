<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Akademik\JadwalMobileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function __construct(
        protected JadwalMobileService $service,
        protected ResponseService $response,
    ) {}

    /** Jadwal mengajar guru hari ini. */
    public function today(Request $request)
    {
        $guru = $this->resolveGuru($request);

        return $this->response->success($this->service->hariIni($guru), 'OK');
    }

    /** Jadwal mengajar guru sepekan (dikelompokkan per hari). */
    public function week(Request $request)
    {
        $guru = $this->resolveGuru($request);

        return $this->response->success($this->service->mingguan($guru), 'OK');
    }

    private function resolveGuru(Request $request)
    {
        $guru = $request->user()->guru;

        abort_if(! $guru, 403, 'Akun ini tidak tertaut ke data guru manapun.');

        return $guru;
    }
}
