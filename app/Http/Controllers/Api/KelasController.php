<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master\JadwalKbm;
use App\Services\Akademik\KelasMobileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;

class KelasController extends Controller
{
    public function __construct(
        protected KelasMobileService $service,
        protected ResponseService $response,
    ) {}

    public function siswa(Request $request, int $jadwal)
    {
        $jadwal = $this->resolveJadwal($request, $jadwal);

        return $this->response->success($this->service->daftarSiswa($jadwal), 'OK');
    }

    public function absensiStore(Request $request, int $jadwal)
    {
        $jadwal = $this->resolveJadwal($request, $jadwal);
        $guru = $request->user()->guru;

        $data = $request->validate([
            'detail' => 'required|array|min:1',
            'detail.*.peserta_id' => 'required|integer',
            'detail.*.status' => 'required|in:hadir,sakit,izin,alpa',
        ]);

        try {
            $sesi = $this->service->simpanAbsensi($jadwal, $guru, $data['detail']);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($sesi, 'Absensi berhasil disimpan.');
    }

    public function absensiScan(Request $request, int $jadwal)
    {
        $jadwal = $this->resolveJadwal($request, $jadwal);
        $guru = $request->user()->guru;

        $data = $request->validate(['token' => 'required|string']);

        try {
            $hasil = $this->service->scanQr($jadwal, $guru, $data['token']);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($hasil, "{$hasil['nama']} berhasil dicatat hadir.");
    }

    public function materi(Request $request, int $jadwal)
    {
        $jadwal = $this->resolveJadwal($request, $jadwal);

        return $this->response->success($this->service->materi($jadwal), 'OK');
    }

    public function nilaiSheet(Request $request, int $jadwal)
    {
        $jadwal = $this->resolveJadwal($request, $jadwal);

        try {
            $sheet = $this->service->nilaiSheet($jadwal);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 423); // 423 Locked
        }

        return $this->response->success($sheet, 'OK');
    }

    public function nilaiStore(Request $request, int $jadwal)
    {
        $jadwal = $this->resolveJadwal($request, $jadwal);

        $data = $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.peserta_id' => 'required|integer',
            'rows.*.nilai_harian' => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uts' => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uas' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $this->service->simpanNilai($jadwal, $data['rows']);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 423);
        }

        return $this->response->success(null, 'Nilai berhasil disimpan.');
    }

    /** Resolve jadwal & pastikan milik guru yang login. */
    private function resolveJadwal(Request $request, int $jadwalId): JadwalKbm
    {
        $guru = $request->user()->guru;
        abort_if(! $guru, 403, 'Akun ini tidak tertaut ke data guru manapun.');

        $jadwal = JadwalKbm::find($jadwalId);
        abort_if(! $jadwal, 404, 'Jadwal tidak ditemukan.');
        abort_if($jadwal->guru_id !== $guru->id, 403, 'Jadwal ini bukan milik Anda.');

        return $jadwal;
    }
}
