<?php

namespace App\Http\Controllers\ProgramKerja;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use App\Models\ProgramKerja\ProgramKerja;
use App\Repositories\ProgramKerja\ProgramKerjaRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\ProgramKerja\ProgramKerjaService;
use App\Services\ResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProgramKerjaController extends Controller
{
    public function __construct(
        protected ProgramKerjaRepositoryInterface $repo,
        protected ProgramKerjaService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    // =========================================================================
    // Index & List
    // =========================================================================

    public function index()
    {
        $lembagaList     = Lembaga::orderBy('urutan')->get(['id', 'kode', 'nama']);
        $tahunList       = TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama', 'status']);
        $activeLembagaId = app('active_lembaga_id');
        $bidangConfig    = ProgramKerja::bidangConfig();

        $this->logActivity->log('Akses Menu Program Kerja', 'Membuka halaman daftar program kerja.');

        return view('admin.program-kerja.index', compact(
            'lembagaList',
            'tahunList',
            'activeLembagaId',
            'bidangConfig'
        ));
    }

    public function list(Request $request)
    {
        $builder = $this->repo->datatable(
            $request->only(['lembaga_id', 'tahun_pelajaran_id', 'bidang', 'status'])
        );

        $bidangConfig = ProgramKerja::bidangConfig();
        $statusConfig = ProgramKerja::statusConfig();

        return DataTables::of($builder)
            ->addIndexColumn()
            ->addColumn('action', fn ($row) => view('admin.program-kerja._action', compact('row'))->render())
            ->addColumn('bidang_label', fn ($row) => $bidangConfig[$row->bidang] ?? ucfirst($row->bidang))
            ->addColumn('status_badge', function ($row) use ($statusConfig) {
                $cfg   = $statusConfig[$row->status] ?? ['class' => 'secondary', 'label' => ucfirst($row->status)];
                return "<span class=\"badge bg-{$cfg['class']}\">{$cfg['label']}</span>";
            })
            ->addColumn('progress', function ($row) {
                $kegiatan = $row->kegiatan ?? $row->kegiatanProgramKerja ?? collect();
                $total    = $kegiatan->count();
                $selesai  = $kegiatan->where('status_kegiatan', 'selesai')->count();
                $persen   = $total > 0 ? (int) round(($selesai / $total) * 100) : 0;

                $bar = "<div class=\"progress\" style=\"height:8px;\">"
                    . "<div class=\"progress-bar bg-success\" style=\"width:{$persen}%\"></div>"
                    . "</div>"
                    . "<small class=\"text-muted\">{$selesai}/{$total} kegiatan selesai</small>";

                return $bar;
            })
            ->addColumn('anggaran_fmt', fn ($row) => 'Rp&nbsp;' . number_format($row->total_anggaran ?? 0, 0, ',', '.'))
            ->addColumn('tahun_nama', fn ($row) => optional($row->tahunPelajaran)->nama ?? '-')
            ->addColumn('lembaga_nama', fn ($row) => optional($row->lembaga)->nama ?? '-')
            ->rawColumns(['action', 'status_badge', 'progress', 'anggaran_fmt'])
            ->make(true);
    }

    // =========================================================================
    // Store
    // =========================================================================

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'         => 'required|integer|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'bidang'             => 'required|string|in:kesiswaan,sarpras,humas,kurikulum,umum',
            'nama_program'       => 'required|string|max:255',
            'deskripsi'          => 'nullable|string',
            'tujuan'             => 'nullable|string|max:500',
        ]);

        try {
            $prog = $this->service->create($data, auth()->user()->id_user);
            return $this->response->success($prog, 'Program kerja berhasil dibuat.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // =========================================================================
    // Show (Detail)
    // =========================================================================

    public function show(int $id)
    {
        $prog = $this->repo->findById($id, ['lembaga', 'tahunPelajaran', 'kegiatan']);

        if (! $prog) {
            return $this->response->error('Program kerja tidak ditemukan.', 404);
        }

        $summary = $this->service->getProgressSummary($id);

        $this->logActivity->log(
            'Akses Detail Program Kerja',
            "Membuka detail program kerja id={$id} '{$prog->nama_program}'."
        );

        return view('admin.program-kerja.detail', compact('prog', 'summary'));
    }

    // =========================================================================
    // Update
    // =========================================================================

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'bidang'       => 'nullable|string|in:kesiswaan,sarpras,humas,kurikulum,umum',
            'nama_program' => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'tujuan'       => 'nullable|string|max:500',
        ]);

        try {
            $this->service->update($id, $data);
            return $this->response->success(null, 'Program kerja berhasil diperbarui.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // =========================================================================
    // Destroy
    // =========================================================================

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success(null, 'Program kerja berhasil dihapus.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // =========================================================================
    // Workflow: Submit / Withdraw / Verifikasi / Approval / Tolak
    // =========================================================================

    public function submit(Request $request, int $id)
    {
        $catatan = $request->input('catatan');

        try {
            $this->service->submit($id, $catatan, auth()->user()->id_user);
            return $this->response->success(null, 'Program kerja berhasil diajukan.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function withdraw(int $id)
    {
        try {
            $this->service->withdraw($id);
            return $this->response->success(null, 'Pengajuan ditarik kembali.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function verifikasi(Request $request, int $id)
    {
        try {
            $this->service->verifikasi($id, $request->input('catatan'), auth()->user()->id_user);
            return $this->response->success(null, 'Program kerja berhasil diverifikasi.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function approval(Request $request, int $id)
    {
        try {
            $this->service->approval($id, $request->input('catatan'), auth()->user()->id_user);
            return $this->response->success(null, 'Program kerja berhasil disetujui.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function tolak(Request $request, int $id)
    {
        $request->validate(['catatan' => 'required|string|max:1000']);

        try {
            $this->service->tolak($id, $request->input('catatan'));
            return $this->response->success(null, 'Program kerja ditolak.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // =========================================================================
    // Kegiatan
    // =========================================================================

    public function addKegiatan(Request $request, int $programId)
    {
        $data = $request->validate([
            'nama_kegiatan'    => 'required|string|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
            'target'           => 'nullable|string',
            'indikator'        => 'nullable|string',
            'anggaran'         => 'nullable|numeric|min:0',
            'bulan_mulai'      => 'required|integer|min:1|max:12',
            'bulan_selesai'    => 'required|integer|min:1|max:12',
            'deskripsi'        => 'nullable|string',
        ]);

        try {
            $kegiatan = $this->service->addKegiatan($programId, $data);
            return $this->response->success($kegiatan, 'Kegiatan berhasil ditambahkan.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function updateKegiatan(Request $request, int $programId, int $kegiatanId)
    {
        $data = $request->validate([
            'nama_kegiatan'    => 'required|string|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
            'target'           => 'nullable|string',
            'indikator'        => 'nullable|string',
            'anggaran'         => 'nullable|numeric|min:0',
            'bulan_mulai'      => 'required|integer|min:1|max:12',
            'bulan_selesai'    => 'required|integer|min:1|max:12',
            'deskripsi'        => 'nullable|string',
        ]);

        try {
            $this->service->updateKegiatan($kegiatanId, $data);
            return $this->response->success(null, 'Kegiatan berhasil diperbarui.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function deleteKegiatan(int $programId, int $kegiatanId)
    {
        try {
            $this->service->deleteKegiatan($kegiatanId);
            return $this->response->success(null, 'Kegiatan dihapus.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function updateRealisasi(Request $request, int $programId, int $kegiatanId)
    {
        $data = $request->validate([
            'realisasi_persen'   => 'required|integer|min:0|max:100',
            'realisasi_anggaran' => 'nullable|numeric',
            'status_kegiatan'    => 'required|string|in:belum,proses,selesai,dibatalkan',
            'catatan_realisasi'  => 'nullable|string',
        ]);

        try {
            $kegiatan = $this->service->updateRealisasi($kegiatanId, $data);
            return $this->response->success($kegiatan, 'Realisasi berhasil diperbarui.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    // =========================================================================
    // Cetak PDF
    // =========================================================================

    public function cetak(int $id)
    {
        $prog = $this->repo->findById($id, ['lembaga', 'tahunPelajaran', 'kegiatan']);

        if (! $prog || ! in_array($prog->status, ['disetujui', 'aktif', 'selesai'])) {
            abort(403, 'Program kerja belum disetujui.');
        }

        $summary = $this->service->getProgressSummary($id);

        $pdf = Pdf::loadView('pdf.program-kerja', compact('prog', 'summary'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Program-Kerja-' . $prog->bidang . '-' . ($prog->tahunPelajaran->nama ?? 'unknown') . '.pdf';

        return $pdf->download($filename);
    }
}
