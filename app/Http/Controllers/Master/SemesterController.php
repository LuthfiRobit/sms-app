<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\Master\SemesterRepositoryInterface;
use App\Services\Master\SemesterService;
use App\Services\ResponseService;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class SemesterController extends Controller
{
    protected $semesterService;
    protected $semesterRepository;
    protected $responseService;
    protected $logActivityService;

    public function __construct(
        SemesterService $semesterService,
        SemesterRepositoryInterface $semesterRepository,
        ResponseService $responseService,
        LogActivityService $logActivityService
    ) {
        $this->semesterService = $semesterService;
        $this->semesterRepository = $semesterRepository;
        $this->responseService = $responseService;
        $this->logActivityService = $logActivityService;
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $this->logActivityService->log('List Semester', 'Mengambil data datatable Semester');
            $data = $this->semesterRepository->getDatatablesData();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tahun_pelajaran', function($row){
                    return $row->tahunPelajaran ? $row->tahunPelajaran->nama : '-';
                })
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
                    if (auth()->user()->hasPermissionTo('admin.master.semester.show')) {
                        $btn .= '    <a class="dropdown-item btn-show-semester" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-eye"></i> Lihat</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.master.semester.update')) {
                        $btn .= '    <a class="dropdown-item btn-edit-semester" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-pencil"></i> Edit</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.master.semester.toggle')) {
                        $btn .= '    <div class="dropdown-divider"></div>';
                        $btn .= '    <a class="dropdown-item btn-toggle-status-semester" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-lock"></i> Toggle Aktif</a>';
                    }
                    if (auth()->user()->hasPermissionTo('admin.master.semester.destroy')) {
                        $btn .= '    <a class="dropdown-item text-danger btn-delete-semester" href="javascript:void(0);" data-id="'.$row->id.'"><i class="bi bi-trash"></i> Hapus</a>';
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
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => [
                'required', 'string', 'max:20',
                Rule::unique('semester')->where(function ($query) use ($request) {
                    return $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
                })
            ],
            'semester_ke' => [
                'required', 'integer', 'in:1,2',
                Rule::unique('semester')->where(function ($query) use ($request) {
                    return $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
                })
            ],
            'mulai' => 'required|date',
            'selesai' => 'required|date|after:mulai',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $semester = $this->semesterService->createSemester($request->all());
            return $this->responseService->success($semester, 'Semester berhasil ditambahkan');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menambahkan Semester: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $semester = $this->semesterRepository->getById($id);
            return $this->responseService->success($semester);
        } catch (\Exception $e) {
            return $this->responseService->error('Semester tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => [
                'required', 'string', 'max:20',
                Rule::unique('semester')->where(function ($query) use ($request) {
                    return $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
                })->ignore($id)
            ],
            'semester_ke' => [
                'required', 'integer', 'in:1,2',
                Rule::unique('semester')->where(function ($query) use ($request) {
                    return $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
                })->ignore($id)
            ],
            'mulai' => 'required|date',
            'selesai' => 'required|date|after:mulai',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $semester = $this->semesterService->updateSemester($id, $request->all());
            return $this->responseService->success($semester, 'Semester berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal memperbarui Semester: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $semester = $this->semesterService->toggleStatus($id);
            return $this->responseService->success($semester, 'Status semester berhasil diubah');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal merubah status Semester: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->semesterService->deleteSemester($id);
            return $this->responseService->success(null, 'Semester berhasil dihapus');
        } catch (\Exception $e) {
            return $this->responseService->error('Gagal menghapus Semester: ' . $e->getMessage());
        }
    }
}
