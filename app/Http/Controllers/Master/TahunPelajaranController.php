<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\Master\TahunPelajaranRepositoryInterface;
use App\Services\Master\TahunPelajaranService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class TahunPelajaranController extends Controller
{
    protected $tahunPelajaranService;
    protected $tahunPelajaranRepository;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        TahunPelajaranService $tahunPelajaranService,
        TahunPelajaranRepositoryInterface $tahunPelajaranRepository,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->tahunPelajaranService = $tahunPelajaranService;
        $this->tahunPelajaranRepository = $tahunPelajaranRepository;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $this->logActivityService->log('Access Tahun Pelajaran Index', 'Membuka halaman master Tahun Pelajaran');
        $tahunPelajarans = $this->tahunPelajaranRepository->getAll();
        return view('admin.master.tahun-pelajaran.index', compact('tahunPelajarans'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $this->logActivityService->log('List Tahun Pelajaran', 'Mengambil data datatable Tahun Pelajaran');
            $data = $this->tahunPelajaranRepository->getDatatablesData();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function($row){
                    $badge = $row->status == 'aktif' 
                        ? '<span class="badge bg-light-success">Aktif</span>' 
                        : '<span class="badge bg-light-danger">Non-Aktif</span>';
                    return $badge;
                })
                ->addColumn('action', function($row){
                    $btn = '<div class="btn-group">';
                    $btn .= '  <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $btn .= '    <i class="bi bi-gear"></i> Aksi';
                    $btn .= '  </button>';
                    $btn .= '  <div class="dropdown-menu">';
                    if (auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.show')) {
                        $btn .= '    <a class="dropdown-item btn-show" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-eye"></i> Lihat</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.update')) {
                        $btn .= '    <a class="dropdown-item btn-edit" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-pencil"></i> Edit</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.toggle')) {
                        $btn .= '    <div class="dropdown-divider"></div>';
                        $btn .= '    <a class="dropdown-item btn-toggle-status" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-lock"></i> Toggle Aktif</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.master.tahun-pelajaran.destroy')) {
                        $btn .= '    <a class="dropdown-item text-danger btn-delete" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-trash"></i> Hapus</a>';
                    }
                    $btn .= '  </div>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_tahun' => 'required|string|max:9|unique:tahun_pelajaran,kode_tahun',
            'nama' => 'required|string|max:50|unique:tahun_pelajaran,nama',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after:mulai',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $tahun = $this->tahunPelajaranService->createTahunPelajaran($request->all());
            return $this->responseService->success($tahun, 'Tahun Pelajaran berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan Tahun Pelajaran: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $tahun = $this->tahunPelajaranRepository->getById($id);
            return $this->responseService->success($tahun);
        } catch (\Exception $e) {
            return $this->responseService->error('Tahun Pelajaran tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_tahun' => 'required|string|max:9|unique:tahun_pelajaran,kode_tahun,' . $id,
            'nama' => 'required|string|max:50|unique:tahun_pelajaran,nama,' . $id,
            'mulai' => 'required|date',
            'selesai' => 'required|date|after:mulai',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $tahun = $this->tahunPelajaranService->updateTahunPelajaran($id, $request->all());
            return $this->responseService->success($tahun, 'Tahun Pelajaran berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui Tahun Pelajaran: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $tahun = $this->tahunPelajaranService->toggleStatus($id);
            return $this->responseService->success($tahun, 'Status Tahun Pelajaran berhasil diubah');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal merubah status Tahun Pelajaran: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->tahunPelajaranService->deleteTahunPelajaran($id);
            return $this->responseService->success(null, 'Tahun Pelajaran berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus Tahun Pelajaran: ' . $e->getMessage());
        }
    }
}
