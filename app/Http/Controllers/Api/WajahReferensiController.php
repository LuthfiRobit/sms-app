<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesGuru;
use App\Http\Controllers\Controller;
use App\Services\Integrations\FaceRecognitionService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class WajahReferensiController extends Controller
{
    use ResolvesGuru;

    public function __construct(
        protected FaceRecognitionService $faceRecognition,
        protected ResponseService $response,
    ) {}

    /** Status enrollment wajah referensi guru yang login. */
    public function show(Request $request)
    {
        $guru = $this->resolveGuru($request);

        return $this->response->success([
            'terdaftar' => (bool) $guru->wajah_terdaftar_at,
            'terdaftar_at' => $guru->wajah_terdaftar_at?->toIso8601String(),
            'foto_url' => $guru->foto ? asset('storage/'.$guru->foto) : null,
        ], 'OK');
    }

    /**
     * Daftarkan/perbarui wajah referensi. Berbeda dari absen (fail-mode
     * permisif) — enrollment WAJIB gagal keras kalau layanan tidak bisa
     * dihubungi, supaya tidak ada guru yang "merasa" terdaftar padahal
     * sebenarnya belum.
     */
    public function store(Request $request)
    {
        $guru = $this->resolveGuru($request);

        $request->validate([
            'foto' => 'required|image|max:4096',
        ]);

        $result = $this->faceRecognition->enroll($guru, $request->file('foto'));

        if ($result['status'] === 'unavailable') {
            return $this->response->error('Layanan verifikasi wajah sedang tidak tersedia. Coba lagi nanti.', 503);
        }

        if ($result['status'] === 'fail') {
            $message = match ($result['reason'] ?? null) {
                'no_face' => 'Tidak ada wajah terdeteksi pada foto. Coba lagi dengan pencahayaan yang lebih baik.',
                'multiple_faces' => 'Terdeteksi lebih dari satu wajah. Pastikan hanya ada satu orang dalam foto.',
                default => $result['message'] ?? 'Gagal mendaftarkan wajah.',
            };

            return $this->response->error($message, 422);
        }

        $path = $request->file('foto')->store("wajah-referensi/{$guru->id}", 'public');
        $guru->update(['foto' => $path, 'wajah_terdaftar_at' => now()]);

        return $this->response->success([
            'terdaftar' => true,
            'terdaftar_at' => $guru->wajah_terdaftar_at->toIso8601String(),
        ], 'Wajah referensi berhasil didaftarkan.');
    }
}
