<?php

namespace App\Services\ProgramKerja;

use App\Models\ProgramKerja\KegiatanProgramKerja;
use App\Repositories\ProgramKerja\ProgramKerjaRepositoryInterface;
use App\Services\LogActivityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgramKerjaService
{
    /**
     * Urutan status yang valid dalam alur approval.
     */
    protected static array $STATUS_FLOW = [
        'draft',
        'diajukan',
        'diverifikasi',
        'disetujui',
        'aktif',
        'selesai',
    ];

    /**
     * Label tampilan untuk setiap bidang program kerja.
     */
    protected static array $BIDANG_LABELS = [
        'kesiswaan'    => 'Kesiswaan',
        'kurikulum'    => 'Kurikulum',
        'sarpras'      => 'Sarana & Prasarana',
        'humas'        => 'Hubungan Masyarakat',
        'kepegawaian'  => 'Kepegawaian',
        'keuangan'     => 'Keuangan',
        'umum'         => 'Umum',
    ];

    /**
     * Nilai enum yang valid untuk status_kegiatan.
     */
    protected static array $STATUS_KEGIATAN = [
        'belum',
        'proses',
        'selesai',
        'dibatalkan',
    ];

    public function __construct(
        protected ProgramKerjaRepositoryInterface $repo,
        protected LogActivityService $logActivity
    ) {}

    // =========================================================================
    // Program Kerja — CRUD
    // =========================================================================

    /**
     * Membuat program kerja baru dengan status 'draft'.
     */
    public function create(array $data, int $userId): object
    {
        $data['dibuat_oleh'] = $userId;
        $data['status']      = 'draft';

        $prog = $this->repo->create($data);

        $this->logActivity->log(
            'Buat Program Kerja',
            "Program: {$prog->nama_program} ({$prog->bidang})"
        );

        return $prog;
    }

    /**
     * Memperbarui program kerja. Hanya boleh saat berstatus 'draft'.
     */
    public function update(int $id, array $data): object
    {
        $prog = $this->repo->findById($id);

        if ($prog->status !== 'draft') {
            throw new \Exception('Hanya program berstatus draft yang dapat diedit.');
        }

        $updated = $this->repo->update($id, $data);

        $this->logActivity->log(
            'Update Program Kerja',
            "Program: {$updated->nama_program} ({$updated->bidang})"
        );

        return $updated;
    }

    /**
     * Menghapus program kerja. Hanya boleh saat berstatus 'draft' atau 'ditolak'.
     */
    public function destroy(int $id): void
    {
        $prog = $this->repo->findById($id);

        if (! in_array($prog->status, ['draft', 'ditolak'])) {
            throw new \Exception('Program kerja hanya dapat dihapus saat berstatus draft atau ditolak.');
        }

        $this->logActivity->log(
            'Hapus Program Kerja',
            "Program: {$prog->nama_program} ({$prog->bidang})"
        );

        $this->repo->delete($id);
    }

    // =========================================================================
    // Alur Approval
    // =========================================================================

    /**
     * Mengajukan program kerja untuk diverifikasi.
     */
    public function submit(int $id, ?string $catatan, int $userId): object
    {
        $prog = $this->repo->findById($id);

        if (! in_array($prog->status, ['draft', 'ditolak'])) {
            throw new \Exception('Program kerja hanya dapat diajukan dari status draft atau ditolak.');
        }

        $updated = $this->repo->update($id, [
            'status'             => 'diajukan',
            'diajukan_at'        => now(),
            'catatan_pengajuan'  => $catatan,
            'dibuat_oleh'        => $userId,
        ]);

        $this->logActivity->log(
            'Ajukan Program Kerja',
            "Program: {$prog->nama_program} diajukan untuk verifikasi"
        );

        return $updated;
    }

    /**
     * Menarik kembali pengajuan ke status 'draft'.
     */
    public function withdraw(int $id): object
    {
        $prog = $this->repo->findById($id);

        if ($prog->status !== 'diajukan') {
            throw new \Exception('Hanya program berstatus diajukan yang dapat ditarik kembali.');
        }

        $updated = $this->repo->update($id, [
            'status'      => 'draft',
            'diajukan_at' => null,
        ]);

        $this->logActivity->log(
            'Tarik Pengajuan Program Kerja',
            "Program: {$prog->nama_program} ditarik kembali ke draft"
        );

        return $updated;
    }

    /**
     * Memverifikasi program kerja yang telah diajukan.
     */
    public function verifikasi(int $id, ?string $catatan, int $userId): object
    {
        $prog = $this->repo->findById($id);

        if ($prog->status !== 'diajukan') {
            throw new \Exception('Hanya program berstatus diajukan yang dapat diverifikasi.');
        }

        $updated = $this->repo->update($id, [
            'status'               => 'diverifikasi',
            'diverifikasi_at'      => now(),
            'diverifikasi_by'      => $userId,
            'catatan_verifikasi'   => $catatan,
        ]);

        $this->logActivity->log(
            'Verifikasi Program Kerja',
            "Program: {$prog->nama_program} berhasil diverifikasi"
        );

        return $updated;
    }

    /**
     * Menyetujui program kerja yang telah diverifikasi.
     */
    public function approval(int $id, ?string $catatan, int $userId): object
    {
        $prog = $this->repo->findById($id);

        if ($prog->status !== 'diverifikasi') {
            throw new \Exception('Hanya program berstatus diverifikasi yang dapat disetujui.');
        }

        $updated = $this->repo->update($id, [
            'status'           => 'disetujui',
            'disetujui_at'     => now(),
            'disetujui_by'     => $userId,
            'catatan_approval' => $catatan,
        ]);

        $this->logActivity->log(
            'Setujui Program Kerja',
            "Program: {$prog->nama_program} berhasil disetujui"
        );

        return $updated;
    }

    /**
     * Menolak program kerja yang sedang diajukan atau diverifikasi.
     */
    public function tolak(int $id, string $catatan): object
    {
        $prog = $this->repo->findById($id);

        if (! in_array($prog->status, ['diajukan', 'diverifikasi'])) {
            throw new \Exception('Program kerja hanya dapat ditolak dari status diajukan atau diverifikasi.');
        }

        $updated = $this->repo->update($id, [
            'status'              => 'ditolak',
            'catatan_penolakan'   => $catatan,
        ]);

        $this->logActivity->log(
            'Tolak Program Kerja',
            "Program: {$prog->nama_program} ditolak. Catatan: {$catatan}"
        );

        return $updated;
    }

    // =========================================================================
    // Kegiatan
    // =========================================================================

    /**
     * Menambah kegiatan ke program kerja.
     * Program harus berstatus 'draft', 'disetujui', atau 'aktif'.
     * Jika program 'disetujui', otomatis diubah ke 'aktif' saat kegiatan pertama dimulai.
     */
    public function addKegiatan(int $programId, array $data): object
    {
        $prog = $this->repo->findById($programId);

        if (! in_array($prog->status, ['draft', 'disetujui', 'aktif'])) {
            throw new \Exception('Kegiatan hanya dapat ditambahkan pada program berstatus draft, disetujui, atau aktif.');
        }

        // Tentukan urutan berikutnya
        $maxUrutan = KegiatanProgramKerja::where('program_kerja_id', $programId)
            ->max('urutan') ?? 0;

        $data['urutan'] = $maxUrutan + 1;

        // Bila program disetujui dan ada kegiatan baru ditambahkan → aktifkan
        if ($prog->status === 'disetujui') {
            $this->repo->update($programId, ['status' => 'aktif']);
        }

        $kegiatan = $this->repo->addKegiatan($programId, $data);

        $this->logActivity->log(
            'Tambah Kegiatan Program Kerja',
            "Kegiatan: {$kegiatan->nama_kegiatan} ditambahkan ke program: {$prog->nama_program}"
        );

        return $kegiatan;
    }

    /**
     * Memperbarui data kegiatan.
     * Program induk harus berstatus 'draft', 'disetujui', atau 'aktif'.
     */
    public function updateKegiatan(int $kegiatanId, array $data): object
    {
        $kegiatan = KegiatanProgramKerja::findOrFail($kegiatanId);
        $prog     = $this->repo->findById($kegiatan->program_kerja_id);

        if (! in_array($prog->status, ['draft', 'disetujui', 'aktif'])) {
            throw new \Exception('Kegiatan hanya dapat diubah pada program berstatus draft, disetujui, atau aktif.');
        }

        $updated = $this->repo->updateKegiatan($kegiatanId, $data);

        $this->logActivity->log(
            'Update Kegiatan Program Kerja',
            "Kegiatan: {$updated->nama_kegiatan} pada program: {$prog->nama_program} diperbarui"
        );

        return $updated;
    }

    /**
     * Menghapus kegiatan. Hanya boleh saat program berstatus 'draft'.
     */
    public function deleteKegiatan(int $kegiatanId): void
    {
        $kegiatan = KegiatanProgramKerja::findOrFail($kegiatanId);
        $prog     = $this->repo->findById($kegiatan->program_kerja_id);

        if ($prog->status !== 'draft') {
            throw new \Exception('Kegiatan hanya dapat dihapus saat program berstatus draft.');
        }

        $namaKegiatan = $kegiatan->nama_kegiatan;

        $this->logActivity->log(
            'Hapus Kegiatan Program Kerja',
            "Kegiatan: {$namaKegiatan} dihapus dari program: {$prog->nama_program}"
        );

        $this->repo->deleteKegiatan($kegiatanId);
    }

    /**
     * Memperbarui realisasi/progres pelaksanaan kegiatan.
     * Program induk harus berstatus 'disetujui' atau 'aktif'.
     *
     * Setelah update:
     * - Jika SEMUA kegiatan selesai → program berubah ke 'selesai'.
     * - Jika ADA kegiatan 'proses' dan program masih 'disetujui' → ubah ke 'aktif'.
     */
    public function updateRealisasi(int $kegiatanId, array $data): object
    {
        $kegiatan = KegiatanProgramKerja::with('programKerja')->findOrFail($kegiatanId);
        $prog     = $kegiatan->programKerja;

        if (! in_array($prog->status, ['disetujui', 'aktif'])) {
            throw new \Exception('Realisasi hanya dapat diperbarui pada program berstatus disetujui atau aktif.');
        }

        // Validasi realisasi_persen
        if (isset($data['realisasi_persen'])) {
            $persen = (int) $data['realisasi_persen'];
            if ($persen < 0 || $persen > 100) {
                throw new \Exception('Realisasi persen harus antara 0 hingga 100.');
            }
            $data['realisasi_persen'] = $persen;
        }

        // Validasi status_kegiatan
        if (isset($data['status_kegiatan']) && ! in_array($data['status_kegiatan'], static::$STATUS_KEGIATAN)) {
            throw new \Exception('Status kegiatan tidak valid. Nilai yang diizinkan: ' . implode(', ', static::$STATUS_KEGIATAN));
        }

        $updated = $this->repo->updateRealisasi($kegiatanId, $data);

        // Re-fetch semua kegiatan untuk evaluasi status program
        $allKegiatan = KegiatanProgramKerja::where('program_kerja_id', $prog->id)->get();

        $activeKegiatan = $allKegiatan->whereNotIn('status_kegiatan', ['dibatalkan']);

        if ($activeKegiatan->isNotEmpty()) {
            $semuaSelesai = $activeKegiatan->every(fn ($k) => $k->status_kegiatan === 'selesai');
            $adaProses    = $activeKegiatan->contains(fn ($k) => $k->status_kegiatan === 'proses');

            if ($semuaSelesai) {
                $this->repo->update($prog->id, ['status' => 'selesai']);
            } elseif ($adaProses && $prog->status === 'disetujui') {
                $this->repo->update($prog->id, ['status' => 'aktif']);
            }
        }

        $this->logActivity->log(
            'Update Realisasi Kegiatan',
            "Kegiatan: {$updated->nama_kegiatan} pada program: {$prog->nama_program} — realisasi diperbarui"
        );

        return $updated;
    }

    // =========================================================================
    // Summary / Statistik
    // =========================================================================

    /**
     * Menghitung ringkasan progres kegiatan untuk satu program kerja.
     *
     * @return array{total: int, selesai: int, proses: int, belum: int, dibatalkan: int, progress_persen: float}
     */
    public function getProgressSummary(int $programId): array
    {
        $kegiatan = KegiatanProgramKerja::where('program_kerja_id', $programId)->get();

        $total      = $kegiatan->count();
        $selesai    = $kegiatan->where('status_kegiatan', 'selesai')->count();
        $proses     = $kegiatan->where('status_kegiatan', 'proses')->count();
        $belum      = $kegiatan->where('status_kegiatan', 'belum')->count();
        $dibatalkan = $kegiatan->where('status_kegiatan', 'dibatalkan')->count();

        $aktif           = $total - $dibatalkan;
        $progressPersen  = $aktif > 0 ? round(($selesai / $aktif) * 100, 1) : 0;

        return [
            'total'              => $total,
            'selesai'            => $selesai,
            'proses'             => $proses,
            'belum'              => $belum,
            'dibatalkan'         => $dibatalkan,
            'progress_persen'    => $progressPersen,
            'total_anggaran'     => (float) $kegiatan->sum('anggaran'),
            'realisasi_anggaran' => (float) $kegiatan->whereNotNull('realisasi_anggaran')->sum('realisasi_anggaran'),
        ];
    }
}
