<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\Master\KurikulumRepositoryInterface;
use App\Services\Master\KurikulumService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KurikulumController extends Controller
{
    protected $kurikulumRepository;
    protected $kurikulumService;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        KurikulumRepositoryInterface $kurikulumRepository,
        KurikulumService $kurikulumService,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->kurikulumRepository = $kurikulumRepository;
        $this->kurikulumService = $kurikulumService;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function index()
    {
        $this->logActivityService->log('Akses Menu Kurikulum', 'Membuka halaman pengaturan kurikulum');
        return view('admin.master.kurikulum.index');
    }

    public function list()
    {
        $data = $this->kurikulumRepository->getDatatablesData();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                $badge = $row->status == 'aktif' 
                    ? '<span class="badge bg-light-success">Aktif</span>' 
                    : '<span class="badge bg-light-danger">Non-Aktif</span>';
                return $badge;
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                $btn .= '  <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                $btn .= '    <i class="bi bi-gear"></i> Aksi';
                $btn .= '  </button>';
                $btn .= '  <div class="dropdown-menu shadow-sm border-0">';
                
                if (auth()->user()->hasPermissionTo('admin.master.kurikulum.show')) {
                    $btn .= '    <a class="dropdown-item btn-show" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-eye"></i> Lihat Detail</a>';
                }
                if (auth()->user()->hasPermissionTo('admin.master.kurikulum.update')) {
                    $btn .= '    <a class="dropdown-item btn-edit" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-pencil"></i> Edit Data</a>';
                }
                if (auth()->user()->hasPermissionTo('admin.master.kurikulum.toggle')) { 
                    $btn .= '    <div class="dropdown-divider"></div>';
                    $btn .= '    <a class="dropdown-item btn-toggle-status" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-power"></i> Toggle Status</a>';
                }
                if (auth()->user()->hasPermissionTo('admin.master.kurikulum.destroy')) {
                    $btn .= '    <a class="dropdown-item text-danger btn-delete" href="javascript:void(0);" data-id="' . $row->id . '"><i class="bi bi-trash"></i> Hapus</a>';
                }
                $btn .= '  </div>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kurikulum' => 'required|string|max:50',
            'versi' => 'required|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string'
        ]);

        try {
            $this->kurikulumService->createKurikulum($request->all());
            $this->logActivityService->log('Tambah Kurikulum', 'Menambah kurikulum: ' . $request->nama_kurikulum);
            return $this->responseService->success(null, 'Kurikulum berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan kurikulum: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $kurikulum = $this->kurikulumRepository->getById($id);
            return $this->responseService->success($kurikulum);
        } catch (\Exception $e) {
            return $this->responseService->error('Kurikulum tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kurikulum' => 'required|string|max:50',
            'versi' => 'required|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string'
        ]);

        try {
            $this->kurikulumService->updateKurikulum($request->all(), $id);
            $this->logActivityService->log('Update Kurikulum', 'Memperbarui kurikulum ID: ' . $id);
            return $this->responseService->success(null, 'Kurikulum berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui kurikulum: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->kurikulumService->deleteKurikulum($id);
            $this->logActivityService->log('Hapus Kurikulum', 'Menghapus kurikulum ID: ' . $id);
            return $this->responseService->success(null, 'Kurikulum berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus kurikulum: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $this->kurikulumService->toggleStatus($id);
            $this->logActivityService->log('Toggle Status Kurikulum', 'Mengubah status kurikulum ID: ' . $id);
            return $this->responseService->success(null, 'Status kurikulum berhasil diubah');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal mengubah status kurikulum: ' . $e->getMessage());
        }
    }
}
