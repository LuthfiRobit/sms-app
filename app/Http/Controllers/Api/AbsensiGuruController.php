<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesGuru;
use App\Http\Controllers\Controller;
use App\Services\Akademik\AbsensiGuruService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;

class AbsensiGuruController extends Controller
{
    use ResolvesGuru;

    public function __construct(
        protected AbsensiGuruService $service,
        protected ResponseService $response,
    ) {}

    /** Status absensi guru hari ini. */
    public function today(Request $request)
    {
        $guru = $this->resolveGuru($request);

        return $this->response->success($this->service->today($guru), 'OK');
    }

    /** Absen masuk (GPS + selfie). */
    public function masuk(Request $request)
    {
        $guru = $this->resolveGuru($request);
        $data = $this->validateAbsen($request);

        try {
            $absensi = $this->service->absenMasuk($guru, $data, $request->file('selfie'));
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($absensi, 'Absen masuk berhasil.');
    }

    /** Riwayat absensi guru sendiri untuk satu bulan. */
    public function riwayat(Request $request)
    {
        $guru = $this->resolveGuru($request);

        $data = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
        ]);

        return $this->response->success(
            $this->service->riwayat($guru, (int) $data['bulan'], (int) $data['tahun']),
            'OK'
        );
    }

    /** Absen pulang (GPS + selfie). */
    public function pulang(Request $request)
    {
        $guru = $this->resolveGuru($request);
        $data = $this->validateAbsen($request);

        try {
            $absensi = $this->service->absenPulang($guru, $data, $request->file('selfie'));
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($absensi, 'Absen pulang berhasil.');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function validateAbsen(Request $request): array
    {
        return $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_mock' => 'sometimes|boolean',
            'accuracy' => 'nullable|numeric|min:0',
            'selfie' => 'required|image|max:5120', // maks 5MB
        ]);
    }
}
