<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AbsensiRejectedException;
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

    /** Absen masuk (GPS + video liveness). */
    public function masuk(Request $request)
    {
        $guru = $this->resolveGuru($request);
        $data = $this->validateAbsen($request);

        try {
            $absensi = $this->service->absenMasuk($guru, $data, $request->file('video'));
        } catch (AbsensiRejectedException $e) {
            return $this->response->error($e->getMessage(), 422, ['code' => $e->errorCode]);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($absensi, 'Absen masuk berhasil.');
    }

    /**
     * Cek kecocokan wajah SAJA dari video, tanpa mencatat absensi apapun —
     * dipanggil mobile sebelum submit final supaya guru bisa memilih
     * "rekam ulang" atau "tetap kirim" saat wajah tidak cocok/tidak
     * terdeteksi kedipan, alih-alih baru tahu setelah absen benar-benar
     * tersimpan.
     */
    public function cekWajah(Request $request)
    {
        $guru = $this->resolveGuru($request);

        $data = $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-m4v,video/3gpp|max:10240',
        ]);

        $hasil = $this->service->cekWajah($guru, $request->file('video'));

        return $this->response->success($hasil, 'Pemeriksaan wajah selesai.');
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

    /** Absen pulang (GPS + video liveness). */
    public function pulang(Request $request)
    {
        $guru = $this->resolveGuru($request);
        $data = $this->validateAbsen($request);

        try {
            $absensi = $this->service->absenPulang($guru, $data, $request->file('video'));
        } catch (AbsensiRejectedException $e) {
            return $this->response->error($e->getMessage(), 422, ['code' => $e->errorCode]);
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
            // Video pendek (~2 detik, tanpa audio) dipakai ganda: bukti audit
            // + input liveness/face-match. mimetypes (bukan mimes) karena
            // ekstensi file dari React Native tidak selalu konsisten per platform.
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-m4v,video/3gpp|max:10240',
        ]);
    }
}
