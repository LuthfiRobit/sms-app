<?php

namespace App\Http\Controllers\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\Ppdb\JalurPendaftaran;
use App\Services\LogActivityService;
use App\Services\Ppdb\JadwalPendaftaranService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class JadwalPendaftaranController extends Controller
{
    protected $jadwalService;
    protected $logActivity;
    protected $responseService;

    public function __construct(
        JadwalPendaftaranService $jadwalService,
        LogActivityService $logActivity,
        ResponseService $responseService
    ) {
        $this->jadwalService = $jadwalService;
        $this->logActivity = $logActivity;
        $this->responseService = $responseService;
    }

    public function index()
    {
        $this->logActivity->log('View Jadwal Pendaftaran', 'Membuka halaman Jadwal Pendaftaran PPDB');
        $jalurPendaftaran = JalurPendaftaran::with('pembukaanPpdb')->get();
        return view('admin.ppdb.jadwal.index', compact('jalurPendaftaran'));
    }

    public function list(Request $request)
    {
        $jalurId = $request->get('jalur_pendaftaran_id');

        if (!$jalurId) {
            return DataTables::of(collect([]))->make(true);
        }

        $result = $this->jadwalService->index((int) $jalurId);

        return DataTables::of($result['data'])
            ->addIndexColumn()
            ->addColumn('waktu_mulai', function ($row) {
                return Carbon::parse($row->mulai)->translatedFormat('d M Y H:i');
            })
            ->addColumn('waktu_selesai', function ($row) {
                return Carbon::parse($row->selesai)->translatedFormat('d M Y H:i');
            })
            ->addColumn('tipe_badge', function ($row) {
                $badges = [
                    'pendaftaran' => 'bg-primary',
                    'seleksi' => 'bg-warning text-dark',
                    'pengumuman' => 'bg-info text-dark',
                    'daftar_ulang' => 'bg-success',
                ];
                $class = $badges[$row->tipe] ?? 'bg-secondary';
                $label = ucwords(str_replace('_', ' ', $row->tipe));
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'aktif') {
                    return '<span class="badge bg-success">Aktif</span>';
                } else {
                    return '<span class="badge bg-danger">Non-Aktif</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group" role="group">';
                if (auth()->user()->hasPermissionTo('admin.ppdb.jadwal.update')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }
                if (auth()->user()->hasPermissionTo('admin.ppdb.jadwal.destroy')) {
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['tipe_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'jalur_pendaftaran_id' => 'required|exists:jalur_pendaftaran,id',
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->jadwalService->store($validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function show($id)
    {
        $result = $this->jadwalService->show($id);

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
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $userId = auth()->id() ?? 0;
        $result = $this->jadwalService->update($id, $validatedData, $userId);

        if ($result['success']) {
            return $this->responseService->success($result['data'], $result['message']);
        }

        return $this->responseService->error($result['message']);
    }

    public function destroy($id)
    {
        $userId = auth()->id() ?? 0;
        $result = $this->jadwalService->destroy($id, $userId);

        if ($result['success']) {
            return $this->responseService->success(null, $result['message']);
        }

        return $this->responseService->error($result['message']);
    }
}
