<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Transaksi\PendaftaranService;
use App\Services\LogActivityService;
use App\Models\Ppdb\JalurPendaftaran;
use App\Repositories\Ppdb\JalurPendaftaranRepositoryInterface;
use App\Repositories\Master\TahunPelajaranRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class PendaftaranController extends Controller
{
    public function __construct(
        protected PendaftaranService $pendaftaranService,
        protected JalurPendaftaranRepositoryInterface $jalurRepo,
        protected TahunPelajaranRepositoryInterface $tahunRepo,
        protected DokumenPesertaRepositoryInterface $dokumenRepo,
        protected LogActivityService $logActivity,
    ) {
    }

    public function index()
    {
        $activeLembagaId = app('active_lembaga_id');
        $jalur           = JalurPendaftaran::when($activeLembagaId, fn ($q) => $q->whereHas('pembukaanPpdb', fn ($q2) => $q2->where('lembaga_id', $activeLembagaId)))->get();
        $tahunPelajaran = $this->tahunRepo->getAll();

        return view('admin.pendaftaran.index', compact('jalur', 'tahunPelajaran'));
    }

    public function list(Request $request)
    {
        $filters = [
            'status' => $request->status,
            'jalur_id' => $request->jalur_id,
            'tahun_id' => $request->tahun_id,
            'nama_peserta' => $request->nama_peserta,
            'lembaga_id' => app('active_lembaga_id'),
        ];

        $result = $this->pendaftaranService->index(array_filter($filters));

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 500);
        }

        return DataTables::of($result['data'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="form-check-input bulk-select" value="' . $row->id . '">';
            })
            ->addColumn('peserta', function ($row) {
                $foto = $row->peserta->foto ? \Storage::url($row->peserta->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($row->peserta->nama_lengkap ?? 'User') . '&background=random';
                return '<div class="d-flex align-items-center">
                            <img src="' . $foto . '" alt="foto" class="rounded-circle avatar-sm me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0">' . ($row->peserta->nama_lengkap ?? '-') . '</h6>
                                <small class="text-muted">No: ' . ($row->no_pendaftaran ?? '-') . '</small>
                            </div>
                        </div>';
            })
            ->addColumn('jalur', function ($row) {
                return $row->jalurPendaftaran->nama ?? '-';
            })
            ->addColumn('tanggal_daftar', function ($row) {
                return $row->tanggal_daftar ? $row->tanggal_daftar->format('d/m/Y H:i') : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $statusMap = [
                    'draft' => 'secondary',
                    'submit' => 'warning',
                    'verifikasi' => 'info',
                    'lulus' => 'success',
                    'tidak_lulus' => 'danger',
                    'daftar_ulang' => 'primary',
                    'siswa_tetap' => 'dark',
                ];
                $color = $statusMap[$row->status] ?? 'secondary';
                $label = ucfirst(str_replace('_', ' ', $row->status));
                return '<span class="badge bg-' . $color . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('admin.pendaftaran.show', $row->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="ri-eye-line"></i> Detail</a>';
            })
            ->rawColumns(['checkbox', 'peserta', 'status_badge', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $result = $this->pendaftaranService->getAdminVerifikasiView($id);

        if (!$result['success']) {
            return redirect()->route('admin.pendaftaran.index')->with('error', $result['message']);
        }

        $data = $result['data'];
        return view('admin.pendaftaran.detail', compact('data'));
    }

    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan' => 'required_if:action,reject|string|nullable'
        ]);

        $userId = Auth::user()->id_user ?? Auth::id();
        $result = $this->pendaftaranService->verifikasi($id, $request->action, $request->catatan, $userId);

        if ($result['success']) {
            $nama  = Auth::user()->name ?? 'Admin';
            $aksi  = $request->action === 'approve' ? 'approve' : 'reject';
            $this->logActivity->log(
                "Admin {$nama} verifikasi pendaftaran",
                "Admin {$nama} {$aksi} pendaftaran ID #{$id}" . ($request->catatan ? " — Catatan: {$request->catatan}" : '')
            );
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    public function verifikasiDokumen(Request $request, $id, $dokumenId)
    {
        $request->validate([
            'status' => 'required|in:valid,invalid',
            'keterangan' => 'nullable|string'
        ]);

        $userId = Auth::user()->id_user ?? Auth::id();
        $updated = $this->dokumenRepo->verifikasi($dokumenId, $request->status, $request->keterangan, $userId);

        if ($updated) {
            $nama = Auth::user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} verifikasi dokumen",
                "Admin {$nama} verifikasi dokumen ID #{$dokumenId} pada pendaftaran #{$id} → status: {$request->status}"
            );
            return response()->json(['success' => true, 'message' => 'Status dokumen berhasil disimpan']);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan status dokumen'], 500);
    }

    public function konfirmasiSiswaTetap(Request $request, int $id)
    {
        $userId = Auth::user()->id_user ?? Auth::id();
        $result = $this->pendaftaranService->konfirmasiSiswaTetap($id, $userId);

        if ($result['success']) {
            return response()->json([
                'success'     => true,
                'message'     => $result['message'],
                'auto_rombel' => $result['auto_rombel'] ?? false,
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }
}
