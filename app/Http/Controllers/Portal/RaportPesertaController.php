<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Akademik\PengajuanRaport;
use App\Models\Master\Lembaga;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\PengajuanRaportRepositoryInterface;
use App\Repositories\Peserta\PesertaRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RaportPesertaController extends Controller
{
    public function __construct(
        protected PesertaRepositoryInterface         $pesertaRepo,
        protected PengajuanRaportRepositoryInterface $raportRepo,
        protected AkademikSettingRepositoryInterface $settingRepo,
    ) {}

    /**
     * Daftar raport yang bisa diakses peserta (status disetujui di rombel yang bersangkutan).
     */
    public function index(Request $request)
    {
        $user    = auth()->user();
        $peserta = $this->pesertaRepo->findByUserId($user->id_user);

        if (! $peserta) {
            return view('portal.raport.index', ['raportList' => collect(), 'peserta' => null]);
        }

        // Ambil semua rombel_id yang dimiliki peserta ini
        $rombelIds = DB::table('rombel_siswa')
            ->where('peserta_id', $peserta->id)
            ->pluck('rombel_id');

        // Ambil pengajuan raport yang sudah disetujui untuk rombel-rombel tersebut
        $raportList = PengajuanRaport::whereIn('rombel_id', $rombelIds)
            ->where('status', 'disetujui')
            ->with(['rombel', 'semester', 'tahunPelajaran', 'lembaga'])
            ->orderByDesc('disetujui_at')
            ->get();

        return view('portal.raport.index', compact('raportList', 'peserta'));
    }

    /**
     * Pratinjau raport satu peserta (web view).
     */
    public function show(Request $request, int $pengajuanId)
    {
        $user    = auth()->user();
        $peserta = $this->pesertaRepo->findByUserId($user->id_user);

        if (! $peserta) {
            abort(403, 'Data peserta tidak ditemukan.');
        }

        $pengajuan = $this->raportRepo->findById($pengajuanId);

        $this->authorizeRaport($pengajuan, $peserta->id);

        $lembaga      = Lembaga::find($pengajuan->lembaga_id);
        $nilaiRows    = $this->raportRepo->getNilaiByPengajuan($pengajuanId)
            ->where('peserta_id', $peserta->id);
        $absensiRekap = $this->raportRepo->getAbsensiRekap($pengajuanId)
            ->firstWhere('peserta_id', $peserta->id);

        return view('portal.raport.show', compact(
            'pengajuan', 'lembaga', 'peserta', 'nilaiRows', 'absensiRekap'
        ));
    }

    /**
     * Download raport PDF untuk peserta ini.
     */
    public function download(int $pengajuanId)
    {
        $user    = auth()->user();
        $peserta = $this->pesertaRepo->findByUserId($user->id_user);

        if (! $peserta) {
            abort(403, 'Data peserta tidak ditemukan.');
        }

        $pengajuan = $this->raportRepo->findById($pengajuanId);

        $this->authorizeRaport($pengajuan, $peserta->id);

        $lembaga      = Lembaga::find($pengajuan->lembaga_id);
        $siswa        = $peserta;
        $nilaiRows    = $this->raportRepo->getNilaiByPengajuan($pengajuanId)
            ->where('peserta_id', $peserta->id);
        $absensiRekap = $this->raportRepo->getAbsensiRekap($pengajuanId)
            ->firstWhere('peserta_id', $peserta->id);

        $pdf = Pdf::loadView('pdf.raport', compact(
            'pengajuan', 'lembaga', 'siswa', 'nilaiRows', 'absensiRekap'
        ))->setPaper('A4', 'portrait');

        $fileName = 'Raport-'
            .$pengajuan->rombel->nama.'-'
            .str_replace(' ', '-', $peserta->nama_lengkap)
            .'-'.$pengajuan->semester->nama
            .'.pdf';

        return $pdf->download($fileName);
    }

    // ── Private ─────────────────────────────────────────────────────────────────

    private function authorizeRaport(?object $pengajuan, int $pesertaId): void
    {
        if (! $pengajuan || $pengajuan->status !== 'disetujui') {
            abort(403, 'Raport belum tersedia.');
        }

        // Pastikan peserta ini memang ada di rombel tersebut
        $inRombel = DB::table('rombel_siswa')
            ->where('rombel_id', $pengajuan->rombel_id)
            ->where('peserta_id', $pesertaId)
            ->exists();

        if (! $inRombel) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }
    }
}
