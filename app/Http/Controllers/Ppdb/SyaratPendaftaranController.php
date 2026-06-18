<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\Ppdb\SyaratPendaftaranService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SyaratPendaftaranController extends Controller
{
    protected $syaratService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        SyaratPendaftaranService $syaratService,
        LogActivityService $logActivity,
        ResponseService $responseService
    ) {
        $this->syaratService = $syaratService;
        $this->logActivity = $logActivity;
        $this->responseService = $responseService;
    }

    public function index()
    {
        $this->logActivity->log('View Syarat Pendaftaran', 'Membuka halaman Syarat Pendaftaran PPDB');
        $activeLembagaId  = app('active_lembaga_id');
        $jalurPendaftaran = JalurPendaftaran::with('pembukaanPpdb')
            ->when($activeLembagaId, fn ($q) => $q->whereHas('pembukaanPpdb', fn ($q2) => $q2->where('lembaga_id', $activeLembagaId)))
            ->get();
        return view('admin.ppdb.syarat.index', compact('jalurPendaftaran'));
    }

    public function list(Request $request)
    {
        $jalurId = $request->get('jalur_pendaftaran_id');

        if (!$jalurId) {
            return DataTables::of(collect([]))->make(true);
        }

        $result = $this->syaratService->index((int) $jalurId);

        return DataTables::of($result['data'])
            ->addColumn('tipe_badge', function ($row) {
                if ($row->tipe === 'dokumen') {
                    return '<span class="badge bg-info"><i class="bi bi-file-earmark-text"></i> Dokumen</span>';
                } else {
                    return '<span class="badge bg-secondary"><i class="bi bi-input-cursor-text"></i> Isian</span>';
                }
            })
            ->addColumn('wajib_icon', function ($row) {
                if ($row->wajib) {
                    return '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
                } else {
                    return '<i class="bi bi-x-circle-fill text-danger fs-5"></i>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';
                if (auth()->user()->hasPermissionTo('admin.ppdb.syarat.update')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermissionTo('admin.ppdb.syarat.destroy')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }
                $btn .= '</div>';
                
                // Add drag handle
                return '<i class="bi bi-grip-vertical text-muted me-2" style="cursor: grab;"></i>' . $btn;
            })
            ->rawColumns(['tipe_badge', 'wajib_icon', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string',
            'wajib' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        $validatedData['wajib'] = $request->has('wajib') ? true : false;

        $userId = auth()->id() ?? 0;
        $result = $this->syaratService->store($validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function show($id)
    {
        $result = $this->syaratService->show($id);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string',
            'wajib' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        // Karena form tidak mengirimkan checkbox yg tidak dicentang
        $validatedData['wajib'] = $request->has('wajib') ? true : false;

        $userId = auth()->id() ?? 0;
        $result = $this->syaratService->update($id, $validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function destroy($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->syaratService->destroy($id, $userId);

        if ($result['success']) {
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function reorder(Request $request)
    {
        $validatedData = $request->validate([
            'urutan_data' => 'required|array',
            'urutan_data.*.id' => 'required|integer',
            'urutan_data.*.urutan' => 'required|integer',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->syaratService->reorder($validatedData['urutan_data'], $userId);

        if ($result['success']) {
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
