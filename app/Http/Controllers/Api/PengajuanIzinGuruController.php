<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesGuru;
use App\Http\Controllers\Controller;
use App\Services\Akademik\PengajuanIzinGuruService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;

class PengajuanIzinGuruController extends Controller
{
    use ResolvesGuru;

    public function __construct(
        protected PengajuanIzinGuruService $service,
        protected ResponseService $response,
    ) {}

    /** Daftar pengajuan izin/sakit milik guru yang login. */
    public function index(Request $request)
    {
        $guru = $this->resolveGuru($request);

        return $this->response->success($this->service->daftarSaya($guru), 'OK');
    }

    public function store(Request $request)
    {
        $guru = $this->resolveGuru($request);

        $data = $request->validate([
            'jenis' => 'required|in:izin,sakit',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $row = $this->service->ajukan($guru, $data, $request->file('lampiran'));
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($row, 'Pengajuan berhasil dikirim.');
    }

    public function show(Request $request, int $id)
    {
        $guru = $this->resolveGuru($request);

        try {
            $row = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        abort_if($row->guru_id !== $guru->id, 403, 'Bukan pengajuan Anda.');

        return $this->response->success($row, 'OK');
    }

    /** Batalkan pengajuan (hanya selagi masih berstatus menunggu). */
    public function destroy(Request $request, int $id)
    {
        $guru = $this->resolveGuru($request);

        try {
            $this->service->batalkan($guru, $id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success(null, 'Pengajuan berhasil dibatalkan.');
    }
}
