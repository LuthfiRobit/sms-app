<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use App\Services\LogActivityService;
use App\Services\Ppdb\PembukaanPpdbService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PembukaanPpdbController extends Controller
{
    protected $pembukaanService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        PembukaanPpdbService $pembukaanService,
        LogActivityService $logActivity,
        ResponseService $responseService
    ) {
        $this->pembukaanService = $pembukaanService;
        $this->logActivity = $logActivity;
        $this->responseService = $responseService;

        // Middleware handled in routes/web.php via 'permission' middleware
    }

    public function index()
    {
        $this->logActivity->log('View Pembukaan PPDB', 'Membuka halaman Pembukaan PPDB');
        $tahunPelajaran = TahunPelajaran::orderBy('nama', 'desc')->get();
        $lembaga        = Lembaga::orderBy('urutan')->orderBy('nama')->get();
        return view('admin.ppdb.pembukaan-ppdb.index', compact('tahunPelajaran', 'lembaga'));
    }

    public function list(Request $request)
    {
        $lembagaId = app('active_lembaga_id');
        $filters   = $lembagaId ? ['lembaga_id' => $lembagaId] : [];
        $result    = $this->pembukaanService->index($filters);

        return DataTables::of($result['data']->with('lembaga'))
            ->addIndexColumn()
            ->addColumn('lembaga', function ($row) {
                return $row->lembaga ? $row->lembaga->nama : '<span class="text-muted">—</span>';
            })
            ->addColumn('tahun_pelajaran', function ($row) {
                return $row->tahunPelajaran ? $row->tahunPelajaran->nama : '-';
            })
            ->addColumn('periode', function ($row) {
                $mulai = Carbon::parse($row->mulai)->translatedFormat('d M Y');
                $selesai = Carbon::parse($row->selesai)->translatedFormat('d M Y');
                return $mulai . ' s/d ' . $selesai;
            })
            ->addColumn('status', function ($row) {
                if ($row->status === 'buka') {
                    return '<span class="badge bg-success">Buka</span>';
                } elseif ($row->status === 'tutup') {
                    return '<span class="badge bg-danger">Tutup</span>';
                } else {
                    return '<span class="badge bg-secondary">' . ucfirst($row->status) . '</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';

                if (auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.toggle')) {
                    $icon = $row->status === 'buka' ? 'bi-x-circle' : 'bi-check-circle';
                    $btnClass = $row->status === 'buka' ? 'btn-warning' : 'btn-success';
                    $btnText = $row->status === 'buka' ? 'Tutup' : 'Buka';
                    $btn .= '<button type="button" class="btn btn-sm ' . $btnClass . ' btn-toggle-status" data-id="' . $row->id . '" title="' . $btnText . '"><i class="bi ' . $icon . '"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.show')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-info btn-show text-white" data-id="' . $row->id . '" title="Detail"><i class="bi bi-info-circle"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.update')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.store')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-secondary btn-duplikasi"'
                        . ' data-id="' . $row->id . '"'
                        . ' data-nama="' . e($row->nama) . '"'
                        . ' data-mulai="' . $row->mulai . '"'
                        . ' data-selesai="' . $row->selesai . '"'
                        . ' data-ta="' . $row->tahun_pelajaran_id . '"'
                        . ' title="Duplikasi ke lembaga lain"><i class="bi bi-copy"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.pembukaan.destroy')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['lembaga', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'lembaga_id'         => 'required|exists:lembaga,id',
            'nama'               => 'required|string|max:255',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'mulai'              => 'required|date',
            'selesai'            => 'required|date|after_or_equal:mulai',
            'deskripsi'          => 'nullable|string',
            'status'             => 'nullable|in:buka,tutup,draft',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->pembukaanService->store($validatedData, $userId);

        if ($result['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} store PembukaanPpdb",
                "Admin {$nama} store PembukaanPpdb: {$validatedData['nama']} ({$validatedData['tahun_pelajaran_id']})"
            );
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function show($id)
    {
        $result = $this->pembukaanService->show($id);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'deskripsi' => 'nullable|string',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->pembukaanService->update($id, $validatedData, $userId);

        if ($result['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} update PembukaanPpdb",
                "Admin {$nama} update PembukaanPpdb: ID #{$id} — {$validatedData['nama']}"
            );
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function destroy($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->pembukaanService->destroy($id, $userId);

        if ($result['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} destroy PembukaanPpdb",
                "Admin {$nama} destroy PembukaanPpdb: ID #{$id}"
            );
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function toggleStatus($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->pembukaanService->toggleStatus($id, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function duplikasi(Request $request, $id)
    {
        $validatedData = $request->validate([
            'lembaga_id'         => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama'               => 'required|string|max:255',
            'mulai'              => 'required|date',
            'selesai'            => 'required|date|after_or_equal:mulai',
            'status'             => 'nullable|in:buka,tutup,draft',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->pembukaanService->duplikasi((int) $id, $validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
