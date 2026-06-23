<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\PengajuanRaportRepositoryInterface;
use App\Services\LogActivityService;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakRaportController extends Controller
{
    public function __construct(
        protected PengajuanRaportRepositoryInterface $repo,
        protected AkademikSettingRepositoryInterface $settingRepo,
        protected LogActivityService $logActivity,
    ) {}

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Build per-siswa data array from a loaded PengajuanRaport.
     *
     * Returns: array of [ 'peserta' => object, 'nilaiRows' => Collection, 'absensiRekap' => object|null ]
     */
    private function buildSiswaData(object $pengajuan): array
    {
        $nilaiAll    = $this->repo->getNilaiByPengajuan($pengajuan->id);
        $absensiAll  = $this->repo->getAbsensiRekap($pengajuan->id);

        // Index absensi by peserta_id for O(1) lookup
        $absensiMap = $absensiAll->keyBy('peserta_id');

        // Group nilai by peserta_id
        $nilaiByPeserta = $nilaiAll->groupBy('peserta_id');

        $siswaData = [];

        foreach ($nilaiByPeserta as $pesertaId => $nilaiRows) {
            $firstRow  = $nilaiRows->first();
            $peserta   = $firstRow->peserta;

            $siswaData[] = [
                'peserta'      => $peserta,
                'nilaiRows'    => $nilaiRows,
                'absensiRekap' => $absensiMap->get($pesertaId),
            ];
        }

        // Sort by nama_lengkap for consistent ordering
        usort($siswaData, fn ($a, $b) => strcmp(
            $a['peserta']?->nama_lengkap ?? '',
            $b['peserta']?->nama_lengkap ?? ''
        ));

        return $siswaData;
    }

    /**
     * Return a default AkademikSetting object when no DB record exists yet.
     */
    private function defaultSetting(): object
    {
        return (object) [
            'allow_manual_nilai' => false,
            'bobot_harian'       => 40.00,
            'bobot_uts'          => 30.00,
            'bobot_uas'          => 30.00,
            'kkm_default'        => 70,
        ];
    }

    // ── Actions ─────────────────────────────────────────────────────────────────

    /**
     * Web preview — show all students' rapor for one PengajuanRaport.
     */
    public function preview(int $id)
    {
        $pengajuan = $this->repo->findById($id);

        if (! $pengajuan || $pengajuan->status !== 'disetujui') {
            abort(403, 'Raport belum disetujui.');
        }

        $lembaga  = Lembaga::find($pengajuan->lembaga_id);
        $setting  = $this->settingRepo->findByLembaga($pengajuan->lembaga_id) ?? $this->defaultSetting();
        $siswaData = $this->buildSiswaData($pengajuan);

        $this->logActivity->log(
            'Pratinjau Raport',
            'Membuka pratinjau raport untuk pengajuan #'.$id.' ('.$pengajuan->rombel->nama.' — '.$pengajuan->semester->nama.')'
        );

        return view('admin.akademik.raport.preview', compact(
            'pengajuan', 'lembaga', 'setting', 'siswaData'
        ));
    }

    /**
     * Download PDF for a single student.
     */
    public function cetakSatu(int $id, int $pesertaId)
    {
        $pengajuan = $this->repo->findById($id);

        if (! $pengajuan || $pengajuan->status !== 'disetujui') {
            abort(403, 'Raport belum disetujui.');
        }

        $lembaga   = Lembaga::find($pengajuan->lembaga_id);
        $siswaData = $this->buildSiswaData($pengajuan);

        // Find the specific student
        $found = collect($siswaData)->firstWhere(
            fn ($s) => $s['peserta']?->id === $pesertaId
        );

        if (! $found) {
            abort(404, 'Data siswa tidak ditemukan pada raport ini.');
        }

        $siswa        = $found['peserta'];
        $nilaiRows    = $found['nilaiRows'];
        $absensiRekap = $found['absensiRekap'];

        $this->logActivity->log(
            'Cetak Raport Siswa',
            'Cetak raport siswa '.$siswa->nama_lengkap.' pada pengajuan #'.$id
        );

        $pdf = Pdf::loadView('pdf.raport', compact(
            'pengajuan', 'lembaga', 'siswa', 'nilaiRows', 'absensiRekap'
        ))->setPaper('A4', 'portrait');

        $fileName = 'Raport-'
            .$pengajuan->rombel->nama.'-'
            .str_replace(' ', '-', $siswa->nama_lengkap)
            .'.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Download PDF for all students in one file.
     */
    public function cetakSemua(int $id)
    {
        $pengajuan = $this->repo->findById($id);

        if (! $pengajuan || $pengajuan->status !== 'disetujui') {
            abort(403, 'Raport belum disetujui.');
        }

        $lembaga   = Lembaga::find($pengajuan->lembaga_id);
        $siswaData = $this->buildSiswaData($pengajuan);

        $this->logActivity->log(
            'Cetak Semua Raport',
            'Cetak raport semua siswa untuk pengajuan #'.$id.' ('.$pengajuan->rombel->nama.')'
        );

        $pdf = Pdf::loadView('pdf.raport-semua', compact(
            'pengajuan', 'lembaga', 'siswaData'
        ))->setPaper('A4', 'portrait');

        $fileName = 'Raport-'
            .$pengajuan->rombel->nama.'-'
            .$pengajuan->semester->nama
            .'.pdf';

        return $pdf->download($fileName);
    }
}
