<?php

namespace App\Services\Integrations;

use App\Models\Master\Guru;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Klien HTTP ke microservice Flask (self-hosted) untuk verifikasi wajah 1:1
 * (liveness + face match) dan pendaftaran wajah referensi guru.
 *
 * Membedakan tegas antara respons bisnis dari layanan (mis. guru belum
 * enroll, wajah tidak terdeteksi) vs kegagalan infrastruktur (timeout,
 * koneksi putus, 5xx) — supaya pemanggil (AbsensiGuruService) bisa
 * menerapkan kebijakan fail-mode-nya sendiri dengan benar, bukan
 * menyamakan "layanan mati" dengan "wajah tidak cocok".
 */
class FaceRecognitionService
{
    /** Verifikasi 1:1 — cocokkan video absen dengan wajah referensi guru. */
    public function verify(Guru $guru, UploadedFile $video): array
    {
        if (! config('face_recognition.enabled')) {
            return ['status' => 'unavailable', 'reason' => 'disabled'];
        }

        try {
            $response = Http::withHeaders(['X-API-Key' => config('face_recognition.api_key')])
                ->connectTimeout(config('face_recognition.connect_timeout'))
                ->timeout(config('face_recognition.timeout'))
                ->attach('video', file_get_contents($video->getRealPath()), $video->getClientOriginalName())
                ->post(config('face_recognition.base_url').'/verify', [
                    'guru_id' => $guru->id,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('face_recognition.verify.unavailable', ['guru_id' => $guru->id, 'error' => $e->getMessage()]);

            return ['status' => 'unavailable', 'reason' => 'connection_error'];
        }

        if (! $response->successful()) {
            Log::warning('face_recognition.verify.bad_response', ['guru_id' => $guru->id, 'http_status' => $response->status()]);

            return ['status' => 'unavailable', 'reason' => 'bad_response'];
        }

        $body = $response->json();

        if (! is_array($body)) {
            return ['status' => 'unavailable', 'reason' => 'bad_response'];
        }

        if (($body['reason'] ?? null) === 'guru_not_enrolled') {
            return ['status' => 'not_enrolled'];
        }

        return [
            'status' => 'ok',
            'liveness_ok' => (bool) ($body['liveness_ok'] ?? false),
            'match_ok' => (bool) ($body['match_ok'] ?? false),
            'confidence' => $body['confidence'] ?? null,
            'distance' => $body['distance'] ?? null,
            'reason' => $body['reason'] ?? null,
        ];
    }

    /** Daftarkan/perbarui wajah referensi guru. Gagal keras (tidak ada fail-mode permisif di sini). */
    public function enroll(Guru $guru, UploadedFile $photo): array
    {
        if (! config('face_recognition.enabled')) {
            return ['status' => 'unavailable', 'reason' => 'disabled'];
        }

        try {
            $response = Http::withHeaders(['X-API-Key' => config('face_recognition.api_key')])
                ->connectTimeout(config('face_recognition.connect_timeout'))
                ->timeout(config('face_recognition.enroll_timeout'))
                ->attach('foto', file_get_contents($photo->getRealPath()), $photo->getClientOriginalName())
                ->post(config('face_recognition.base_url').'/enroll', [
                    'guru_id' => $guru->id,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('face_recognition.enroll.unavailable', ['guru_id' => $guru->id, 'error' => $e->getMessage()]);

            return ['status' => 'unavailable', 'reason' => 'connection_error'];
        }

        if (! $response->successful()) {
            Log::warning('face_recognition.enroll.bad_response', ['guru_id' => $guru->id, 'http_status' => $response->status()]);

            return ['status' => 'unavailable', 'reason' => 'bad_response'];
        }

        $body = $response->json();

        if (($body['status'] ?? null) === 'fail') {
            return ['status' => 'fail', 'reason' => $body['reason'] ?? null, 'message' => $body['message'] ?? null];
        }

        return ['status' => 'ok'];
    }

    /** Hapus wajah referensi guru dari layanan. */
    public function deleteEnrollment(int $guruId): array
    {
        if (! config('face_recognition.enabled')) {
            return ['status' => 'unavailable'];
        }

        try {
            $response = Http::withHeaders(['X-API-Key' => config('face_recognition.api_key')])
                ->connectTimeout(config('face_recognition.connect_timeout'))
                ->timeout(config('face_recognition.enroll_timeout'))
                ->delete(config('face_recognition.base_url')."/enroll/{$guruId}");
        } catch (ConnectionException $e) {
            Log::warning('face_recognition.delete.unavailable', ['guru_id' => $guruId, 'error' => $e->getMessage()]);

            return ['status' => 'unavailable'];
        }

        return ['status' => $response->successful() ? 'ok' : 'unavailable'];
    }
}
