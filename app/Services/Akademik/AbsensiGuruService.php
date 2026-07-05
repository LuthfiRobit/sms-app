<?php

namespace App\Services\Akademik;

use App\Models\Akademik\AbsensiGuru;
use App\Models\Master\Guru;
use App\Repositories\Akademik\AbsensiGuruRepositoryInterface;
use App\Services\Integrations\FaceRecognitionService;
use App\Services\LogActivityService;
use App\Support\WaktuSekolah;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class AbsensiGuruService
{
    public function __construct(
        protected AbsensiGuruRepositoryInterface $repo,
        protected LogActivityService $logActivity,
        protected FaceRecognitionService $faceRecognition,
    ) {}

    /**
     * Status absensi guru hari ini: belum absen / sudah masuk / sudah pulang.
     */
    public function today(Guru $guru): array
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $absensi = $this->repo->findByGuruTanggal($guru->id, $tanggal);

        return [
            'tanggal' => $tanggal,
            'sudah_masuk' => (bool) $absensi?->jam_masuk,
            'sudah_pulang' => (bool) $absensi?->jam_pulang,
            'absensi' => $absensi,
        ];
    }

    /**
     * Absen masuk. Validasi: belum absen, bukan mock GPS, dalam radius geofence.
     * Melempar RuntimeException (dengan pesan siap-tampil) jika gagal. Video
     * dipakai ganda: disimpan sebagai bukti audit (kolom selfie_masuk) DAN
     * dikirim ke layanan face-verification untuk cek liveness + kecocokan wajah.
     */
    public function absenMasuk(Guru $guru, array $payload, UploadedFile $video): object
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $existing = $this->repo->findByGuruTanggal($guru->id, $tanggal);

        if ($existing && $existing->jam_masuk) {
            throw new RuntimeException('Anda sudah melakukan absen masuk hari ini.');
        }

        $this->guardMockLocation($payload['is_mock'] ?? false);
        $jarak = $this->guardGeofence($guru, (float) $payload['latitude'], (float) $payload['longitude']);
        $faceResult = $this->guardFaceMatch($guru, $video, 'masuk');

        $path = $this->storeMedia($video, $guru->id, 'masuk');
        $status = $this->tentukanStatusMasuk($guru);

        $data = [
            'guru_id' => $guru->id,
            'lembaga_id' => $guru->lembaga_id,
            'tanggal' => $tanggal,
            'jam_masuk' => WaktuSekolah::now()->format('H:i:s'),
            'lat_masuk' => $payload['latitude'],
            'lng_masuk' => $payload['longitude'],
            'jarak_masuk_m' => $jarak,
            'akurasi_masuk_m' => isset($payload['accuracy']) ? (int) round($payload['accuracy']) : null,
            'selfie_masuk' => $path,
            'status' => $status,
            ...$faceResult,
        ];

        // findByGuruTanggal + create: unique(guru_id,tanggal) sudah menjaga duplikat.
        $absensi = $existing
            ? $this->repo->update($existing, $data)
            : $this->repo->create($data);

        $this->logActivity->log('Absen Masuk Guru', "{$guru->nama_lengkap} absen masuk ({$status}) — jarak {$jarak}m, wajah: {$faceResult['face_verified_masuk']}.");

        return $absensi;
    }

    /**
     * Absen pulang. Harus sudah absen masuk lebih dulu.
     */
    public function absenPulang(Guru $guru, array $payload, UploadedFile $video): object
    {
        $tanggal = WaktuSekolah::now()->toDateString();
        $existing = $this->repo->findByGuruTanggal($guru->id, $tanggal);

        if (! $existing || ! $existing->jam_masuk) {
            throw new RuntimeException('Anda belum melakukan absen masuk hari ini.');
        }

        if ($existing->jam_pulang) {
            throw new RuntimeException('Anda sudah melakukan absen pulang hari ini.');
        }

        $this->guardMockLocation($payload['is_mock'] ?? false);
        $jarak = $this->guardGeofence($guru, (float) $payload['latitude'], (float) $payload['longitude']);
        $faceResult = $this->guardFaceMatch($guru, $video, 'pulang');

        $path = $this->storeMedia($video, $guru->id, 'pulang');

        $absensi = $this->repo->update($existing, [
            'jam_pulang' => WaktuSekolah::now()->format('H:i:s'),
            'lat_pulang' => $payload['latitude'],
            'lng_pulang' => $payload['longitude'],
            'jarak_pulang_m' => $jarak,
            'akurasi_pulang_m' => isset($payload['accuracy']) ? (int) round($payload['accuracy']) : null,
            'selfie_pulang' => $path,
            ...$faceResult,
        ]);

        $this->logActivity->log('Absen Pulang Guru', "{$guru->nama_lengkap} absen pulang — jarak {$jarak}m, wajah: {$faceResult['face_verified_pulang']}.");

        return $absensi;
    }

    /** Riwayat absensi guru sendiri untuk satu bulan (mobile). */
    public function riwayat(Guru $guru, int $bulan, int $tahun): array
    {
        $awalBulan = Carbon::createFromDate($tahun, $bulan, 1);

        return $this->repo->datatable([
            'guru_id' => $guru->id,
            'tanggal_mulai' => $awalBulan->toDateString(),
            'tanggal_akhir' => $awalBulan->copy()->endOfMonth()->toDateString(),
        ])->get(['id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status'])
            ->map(fn ($r) => [
                'tanggal' => $r->tanggal->toDateString(),
                'jam_masuk' => $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : null,
                'jam_pulang' => $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : null,
                'status' => $r->status,
            ])
            ->values()
            ->all();
    }

    // ── Admin (rekap/laporan) ────────────────────────────────────────────────

    public function datatable(?int $lembagaId, array $filters = []): mixed
    {
        if ($lembagaId) {
            $filters['lembaga_id'] = $lembagaId;
        }

        return $this->repo->datatable($filters);
    }

    public function find(int $id): AbsensiGuru
    {
        $absensi = $this->repo->findById($id);

        if (! $absensi) {
            throw new RuntimeException('Data absensi guru tidak ditemukan.');
        }

        return $absensi;
    }

    /**
     * Tambah/ubah data absensi guru secara manual oleh admin (mis. guru lupa
     * HP, atau butuh koreksi status). Tidak menyentuh lat/lng/selfie — data
     * GPS tidak boleh difabrikasi manual.
     */
    public function koreksiManual(array $data, int $adminUserId, ?int $id = null): AbsensiGuru
    {
        $data['is_koreksi_manual'] = true;
        $data['dikoreksi_oleh'] = $adminUserId;

        if ($id) {
            $existing = $this->find($id);

            return $this->repo->update($existing, $data);
        }

        if ($this->repo->findByGuruTanggal($data['guru_id'], $data['tanggal'])) {
            throw new RuntimeException('Guru ini sudah memiliki data absensi pada tanggal tersebut. Gunakan mode edit.');
        }

        return $this->repo->create($data);
    }

    public function destroy(int $id): void
    {
        $this->find($id)->delete();
    }

    // ── Guard helpers ────────────────────────────────────────────────────────

    /** Tolak jika perangkat melaporkan lokasi palsu (mock/fake GPS). */
    protected function guardMockLocation(bool $isMock): void
    {
        if ($isMock) {
            throw new RuntimeException('Lokasi palsu terdeteksi. Nonaktifkan mock location untuk absen.');
        }
    }

    /**
     * Hitung jarak ke titik sekolah (server-side, tidak percaya HP) dan tolak
     * jika di luar radius. Mengembalikan jarak dalam meter.
     */
    protected function guardGeofence(Guru $guru, float $lat, float $lng): int
    {
        $lembaga = $guru->lembaga;

        if (! $lembaga || $lembaga->latitude === null || $lembaga->longitude === null) {
            throw new RuntimeException('Titik lokasi sekolah belum diatur. Hubungi admin.');
        }

        $jarak = $this->haversine((float) $lembaga->latitude, (float) $lembaga->longitude, $lat, $lng);
        $radius = (int) ($lembaga->radius_meter ?: 100);

        if ($jarak > $radius) {
            throw new RuntimeException("Anda berada di luar area sekolah (jarak {$jarak}m, maksimal {$radius}m).");
        }

        return $jarak;
    }

    /** Jarak dua koordinat dalam meter (formula Haversine). */
    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earth = 6_371_000; // radius bumi (meter)
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return (int) round($earth * 2 * asin(min(1.0, sqrt($a))));
    }

    /**
     * Verifikasi wajah (liveness + face-match) via layanan eksternal —
     * BEDA dari guard lain, method ini TIDAK PERNAH throw. Sesuai
     * kebijakan fail-mode permisif: absen selalu diizinkan lanjut, hasil
     * verifikasi cuma ditandai untuk direview admin, bukan dipakai untuk
     * memblokir. Mengembalikan array (key ber-suffix _masuk/_pulang) untuk
     * digabung ke data absensi_guru — kolom terpisah per sesi karena masuk
     * dan pulang adalah dua kejadian independen yang masing-masing
     * dievaluasi sendiri (satu kolom gabungan akan membuat hasil masuk
     * tertimpa saat pulang, atau sebaliknya).
     */
    protected function guardFaceMatch(Guru $guru, UploadedFile $video, string $sesi): array
    {
        $result = $this->faceRecognition->verify($guru, $video);

        $mapped = match ($result['status']) {
            'ok' => [
                'face_verified' => ($result['liveness_ok'] && $result['match_ok']) ? 'cocok' : 'tidak_cocok',
                'face_confidence' => $result['confidence'],
                'face_liveness_ok' => $result['liveness_ok'],
            ],
            'not_enrolled' => [
                'face_verified' => 'tidak_terdaftar',
                'face_confidence' => null,
                'face_liveness_ok' => null,
            ],
            default => [
                'face_verified' => 'layanan_error',
                'face_confidence' => null,
                'face_liveness_ok' => null,
            ],
        };

        return [
            "face_verified_{$sesi}" => $mapped['face_verified'],
            "face_confidence_{$sesi}" => $mapped['face_confidence'],
            "face_liveness_ok_{$sesi}" => $mapped['face_liveness_ok'],
        ];
    }

    /** Hadir vs terlambat berdasarkan jam_masuk_batas lembaga. */
    protected function tentukanStatusMasuk(Guru $guru): string
    {
        $batas = $guru->lembaga?->jam_masuk_batas;

        if (! $batas) {
            return 'hadir';
        }

        $now = WaktuSekolah::now();
        $batasWaktu = Carbon::parse($now->toDateString().' '.$batas, config('sekolah.timezone'));

        return $now->greaterThan($batasWaktu) ? 'terlambat' : 'hadir';
    }

    protected function storeMedia(UploadedFile $file, int $guruId, string $sesi): string
    {
        return $file->store("absensi-guru/{$guruId}/{$sesi}", 'public');
    }
}
