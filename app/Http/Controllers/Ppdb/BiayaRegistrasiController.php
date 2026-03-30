<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\Ppdb\BiayaRegistrasiService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BiayaRegistrasiController extends Controller
{
    protected $biayaService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        BiayaRegistrasiService $biayaService,
        LogActivityService $logActivity,
        ResponseService $responseService
    ) {
        $this->biayaService = $biayaService;
        $this->logActivity = $logActivity;
        $this->responseService = $responseService;
    }

    public function index()
    {
        $this->logActivity->log('View Biaya Registrasi', 'Membuka halaman Biaya Registrasi PPDB');
        $jalurPendaftaran = JalurPendaftaran::with('pembukaanPpdb')->get();
        return view('admin.ppdb.biaya.index', compact('jalurPendaftaran'));
    }

    public function list(Request $request)
    {
        $jalurId = $request->get('jalur_pendaftaran_id');

        if (!$jalurId) {
            return DataTables::of(collect([]))->make(true);
        }

        $result = $this->biayaService->index((int) $jalurId);

        return DataTables::of($result['data'])
            ->addIndexColumn()
            ->addColumn('nominal_format', function ($row) {
                return 'Rp ' . number_format($row->nominal, 0, ',', '.');
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->is_aktif) {
                    return '<span class="badge bg-success">Aktif</span>';
                } else {
                    return '<span class="badge bg-danger">Non-Aktif</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';
                if (auth()->user()->hasPermissionTo('admin.ppdb.biaya.update')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermissionTo('admin.ppdb.biaya.destroy')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'nama' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'deskripsi' => 'nullable|string',
            'is_aktif' => 'nullable|boolean',
        ]);

        $validatedData['is_aktif'] = $request->has('is_aktif') ? 1 : 0;

        $userId = auth()->id() ?? 0;
        $result = $this->biayaService->store($validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function show($id)
    {
        $result = $this->biayaService->show($id);

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
            'nominal' => 'required|numeric|min:1',
            'deskripsi' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $validatedData['is_aktif'] = $request->has('is_aktif') ? 1 : 0;

        $userId = auth()->id() ?? 0;
        $result = $this->biayaService->update($id, $validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function destroy($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->biayaService->destroy($id, $userId);

        if ($result['success']) {
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
