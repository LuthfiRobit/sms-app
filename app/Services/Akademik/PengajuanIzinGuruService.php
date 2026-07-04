<?php

namespace App\Services\Akademik;

use App\Models\Akademik\KalenderLibur;
use App\Models\Akademik\PengajuanIzinGuru;
use App\Models\Master\Guru;
use App\Repositories\Akademik\AbsensiGuruRepositoryInterface;
use App\Repositories\Akademik\PengajuanIzinGuruRepositoryInterface;
use App\Services\LogActivityService;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use RuntimeException;

class PengajuanIzinGuruService
{
    /** Guard lunak: cegah satu pengajuan menulis ratusan baris absensi_guru sekaligus. */
    protected const MAX_RENTANG_HARI = 90;

    public function __construct(
        protected PengajuanIzinGuruRepositoryInterface $repo,
        protected AbsensiGuruRepositoryInterface $absensiRepo,
        protected LogActivityService $logActivity,
    ) {}

    public function ajukan(Guru $guru, array $data, ?UploadedFile $lampiran = null): PengajuanIzinGuru
    {
        $mulai = $data['tanggal_mulai'];
        $akhir = $data['tanggal_selesai'];

        if (Carbon::parse($mulai)->diffInDays(Carbon::parse($akhir)) > self::MAX_RENTANG_HARI) {
            throw new RuntimeException('Rentang tanggal maksimal '.self::MAX_RENTANG_HARI.' hari.');
        }

        if ($this->repo->hasOverlap($guru->id, $mulai, $akhir)) {
            throw new RuntimeException('Anda sudah memiliki pengajuan izin/sakit yang tumpang tindih tanggal.');
        }

        $path = $lampiran ? $lampiran->store("pengajuan-izin/{$guru->id}", 'public') : null;

        $row = $this->repo->create([
            'guru_id' => $guru->id,
            'lembaga_id' => $guru->lembaga_id,
            'jenis' => $data['jenis'],
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $akhir,
            'alasan' => $data['alasan'],
            'lampiran' => $path,
            'status' => 'menunggu',
        ]);

        $this->logActivity->log('Ajukan Izin/Sakit Guru', "{$guru->nama_lengkap} mengajukan {$data['jenis']} {$mulai} s/d {$akhir}.");

        return $row;
    }

    public function daftarSaya(Guru $guru): mixed
    {
        return $this->repo->daftarMilikGuru($guru->id);
    }

    public function batalkan(Guru $guru, int $id): void
    {
        $row = $this->find($id);

        if ($row->guru_id !== $guru->id) {
            throw new RuntimeException('Bukan pengajuan Anda.');
        }

        if ($row->status !== 'menunggu') {
            throw new RuntimeException('Hanya pengajuan berstatus menunggu yang bisa dibatalkan.');
        }

        $row->delete();
    }

    public function datatable(?int $lembagaId, array $filters = []): mixed
    {
        if ($lembagaId) {
            $filters['lembaga_id'] = $lembagaId;
        }

        return $this->repo->datatable($filters);
    }

    public function find(int $id): PengajuanIzinGuru
    {
        $row = $this->repo->findById($id);

        if (! $row) {
            throw new RuntimeException('Data pengajuan tidak ditemukan.');
        }

        return $row;
    }

    public function setujui(int $id, ?string $catatan, int $adminUserId): PengajuanIzinGuru
    {
        $row = $this->find($id);

        if ($row->status !== 'menunggu') {
            throw new RuntimeException('Pengajuan ini sudah diproses.');
        }

        $row = $this->repo->update($row, [
            'status' => 'disetujui',
            'catatan_admin' => $catatan,
            'diproses_at' => now(),
            'diproses_oleh' => $adminUserId,
        ]);

        $this->syncKeAbsensi($row, $adminUserId);

        $this->logActivity->log('Setujui Izin/Sakit Guru', "Pengajuan #{$row->id} ({$row->guru->nama_lengkap}) disetujui.");

        return $row;
    }

    public function tolak(int $id, string $catatan, int $adminUserId): PengajuanIzinGuru
    {
        $row = $this->find($id);

        if ($row->status !== 'menunggu') {
            throw new RuntimeException('Pengajuan ini sudah diproses.');
        }

        $row = $this->repo->update($row, [
            'status' => 'ditolak',
            'catatan_admin' => $catatan,
            'diproses_at' => now(),
            'diproses_oleh' => $adminUserId,
        ]);

        $this->logActivity->log('Tolak Izin/Sakit Guru', "Pengajuan #{$row->id} ({$row->guru->nama_lengkap}) ditolak.");

        return $row;
    }

    /**
     * Tulis status ke absensi_guru untuk tiap tanggal dalam rentang (kecuali
     * Minggu/hari libur). updateOrCreate (bukan skip-if-exists) supaya baris
     * 'alpa' yang sudah dibuat auto-alpa job untuk tanggal itu ikut ditimpa.
     */
    protected function syncKeAbsensi(PengajuanIzinGuru $row, int $adminUserId): void
    {
        foreach (CarbonPeriod::create($row->tanggal_mulai, $row->tanggal_selesai) as $tanggal) {
            $t = $tanggal->toDateString();

            if (KalenderLibur::isLibur($t, $row->lembaga_id)) {
                continue;
            }

            $this->absensiRepo->updateOrCreateByGuruTanggal($row->guru_id, $row->lembaga_id, $t, [
                'status' => $row->jenis,
                'keterangan' => "Disetujui via pengajuan #{$row->id}",
                'is_koreksi_manual' => true,
                'dikoreksi_oleh' => $adminUserId,
            ]);
        }
    }
}
