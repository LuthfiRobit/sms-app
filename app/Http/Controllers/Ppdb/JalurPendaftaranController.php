<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb\PembukaanPpdb;
use App\Services\LogActivityService;
use App\Services\Ppdb\JalurPendaftaranService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JalurPendaftaranController extends Controller
{
    protected $jalurService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        JalurPendaftaranService $jalurService,
        LogActivityService $logActivity,
        ResponseService $responseService
    ) {
        $this->jalurService = $jalurService;
        $this->logActivity = $logActivity;
        $this->responseService = $responseService;

        // Middleware handled in routes/web.php via 'permission' middleware
    }

    public function index()
    {
        $this->logActivity->log('View Jalur Pendaftaran', 'Membuka halaman Jalur Pendaftaran PPDB');
        $pembukaanPpdb = PembukaanPpdb::orderBy('mulai', 'desc')->get();
        return view('admin.ppdb.jalur-pendaftaran.index', compact('pembukaanPpdb'));
    }

    public function list(Request $request)
    {
        $pembukaanId = $request->get('pembukaan_ppdb_id');

        if (!$pembukaanId) {
            return DataTables::of(collect([]))->make(true);
        }

        $result = $this->jalurService->index((int) $pembukaanId);

        return DataTables::of($result['data'])
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                if ($row->status === 'aktif') {
                    return '<span class="badge bg-success">Aktif</span>';
                } else {
                    return '<span class="badge bg-danger">Non-Aktif</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';

                if (auth()->user()->hasPermissionTo('admin.seleksi.index')) {
                    $url = route('admin.seleksi.index', $row->id);
                    $btn .= '<a href="' . $url . '" class="btn btn-sm btn-success text-white" title="Verifikasi & Seleksi"><i class="bi bi-person-lines-fill"></i></a>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.jalur.show')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-info btn-show text-white" data-id="' . $row->id . '" title="Detail"><i class="bi bi-info-circle"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.jalur.update')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                    // // Button for sync kuota - could be directed to another view or open modal
                    // $btn .= '<button type="button" class="btn btn-sm btn-secondary btn-kuota" data-id="' . $row->id . '" title="Kelola Kuota"><i class="bi bi-pie-chart"></i></button>';
                }

                if (auth()->user()->hasPermissionTo('admin.ppdb.jalur.destroy')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pembukaan_ppdb_id' => 'required|exists:pembukaan_ppdb,id',
            'kode_jalur' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kuota' => 'nullable|integer|min:0',
            'urutan' => 'nullable|integer|min:0',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->jalurService->store($validatedData, $userId);

        if ($result['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} store JalurPendaftaran",
                "Admin {$nama} store JalurPendaftaran: {$validatedData['nama']} (kode: {$validatedData['kode_jalur']})"
            );
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function show($id)
    {
        $result = $this->jalurService->show($id);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'kode_jalur' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kuota' => 'nullable|integer|min:0',
            'urutan' => 'nullable|integer|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->jalurService->update($id, $validatedData, $userId);

        if ($result['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} update JalurPendaftaran",
                "Admin {$nama} update JalurPendaftaran: ID #{$id} — {$validatedData['nama']}"
            );
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function destroy($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->jalurService->destroy($id, $userId);

        if ($result['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} destroy JalurPendaftaran",
                "Admin {$nama} destroy JalurPendaftaran: ID #{$id}"
            );
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function syncKuotaJurusan(Request $request, int $jalurId)
    {
        $validatedData = $request->validate([
            'kuota_data' => 'required|array',
            'kuota_data.*.jurusan_id' => 'required|exists:jurusan,id',
            'kuota_data.*.kuota' => 'required|integer|min:0',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->jalurService->syncKuotaJurusan($jalurId, $validatedData['kuota_data'], $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
