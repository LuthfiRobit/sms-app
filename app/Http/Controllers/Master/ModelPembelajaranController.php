<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\Master\ModelPembelajaranService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class ModelPembelajaranController extends Controller
{
    public function __construct(
        protected ModelPembelajaranService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Model Pembelajaran', 'Membuka halaman master Model Pembelajaran.');

        return view('admin.master.model-pembelajaran.index');
    }

    public function list()
    {
        return DataTables::of($this->service->datatable())
            ->addIndexColumn()
            ->addColumn('status_badge', fn ($r) => $r->status === 'aktif'
                ? '<span class="badge bg-light-success">Aktif</span>'
                : '<span class="badge bg-light-danger">Non-Aktif</span>')
            ->addColumn('action', function ($r) {
                $sintaks = "<button class='btn btn-xs btn-icon btn-light-secondary me-1' onclick='kelolaSintaks({$r->id})' title='Kelola Sintaks'><i class='bi bi-list-ol'></i></button>";
                $edit = "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editModel({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>";
                $hapus = "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusModel({$r->id})' title='Hapus'><i class='bi bi-trash'></i></button>";

                return $sintaks.$edit.$hapus;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150|unique:model_pembelajaran,nama',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $model = $this->service->store($data);

        return $this->response->success($model, 'Model Pembelajaran berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        try {
            $model = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        return $this->response->success($model, 'OK');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150|unique:model_pembelajaran,nama,'.$id,
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $model = $this->service->update($id, $data);

        return $this->response->success($model, 'Model Pembelajaran berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'Model Pembelajaran berhasil dihapus.');
    }

    public function sintaks(int $id)
    {
        try {
            $model = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        return $this->response->success($model->sintaks, 'OK');
    }

    public function syncSintaks(Request $request, int $id)
    {
        $data = $request->validate([
            'sintaks' => 'required|array|min:1',
            'sintaks.*.id' => 'nullable|integer',
            'sintaks.*.nama_sintaks' => 'required|string|max:150',
            'sintaks.*.meta_fase' => 'required|in:Memahami,Mengaplikasi,Merefleksi',
            'sintaks.*.urutan' => 'required|integer',
        ]);

        try {
            $this->service->syncSintaks($id, $data['sintaks']);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'Sintaks berhasil disimpan.');
    }
}
