<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Services\Akademik\KalenderLiburService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class KalenderLiburController extends Controller
{
    public function __construct(
        protected KalenderLiburService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Kalender Libur', 'Membuka halaman kalender libur akademik.');

        $user = auth()->user();
        $lembagaList = Lembaga::orderBy('urutan')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('id', $user->getLembagaIds()))
            ->get(['id', 'nama', 'kode']);

        return view('admin.master.kalender-libur.index', [
            'lembagaList' => $lembagaList,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'activeLembagaId' => app('active_lembaga_id'),
        ]);
    }

    public function list(Request $request)
    {
        $filters = [
            'lembaga_id' => $this->resolveLembagaId($request),
            'tahun' => $request->integer('tahun') ?: null,
        ];

        $query = $this->service->datatable(null, $filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tanggal_fmt', fn ($r) => $r->tanggal?->format('d/m/Y') ?? '—')
            ->addColumn('lembaga_nama', fn ($r) => $r->lembaga?->nama ?? 'Semua Lembaga')
            ->addColumn('action', function ($r) {
                $edit = auth()->user()->hasPermissionTo('admin.master.kalender-libur.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editLibur({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.master.kalender-libur.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusLibur({$r->id})' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $edit.$del;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(int $id)
    {
        try {
            $row = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        return $this->response->success($row, 'OK');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:150',
            'lembaga_id' => 'nullable|exists:lembaga,id',
        ]);

        $data['lembaga_id'] = $this->resolveLembagaIdUntukTulis($request);
        $data['dibuat_oleh'] = auth()->id();

        try {
            $row = $this->service->store($data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($row, 'Hari libur berhasil ditambahkan.');
    }

    public function storeRentang(Request $request)
    {
        $data = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:150',
            'lembaga_id' => 'nullable|exists:lembaga,id',
        ]);

        $data['lembaga_id'] = $this->resolveLembagaIdUntukTulis($request);
        $data['dibuat_oleh'] = auth()->id();

        try {
            $jumlah = $this->service->storeRentang($data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success(['jumlah' => $jumlah], "{$jumlah} tanggal libur berhasil ditambahkan.");
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:150',
            'lembaga_id' => 'nullable|exists:lembaga,id',
        ]);

        $data['lembaga_id'] = $this->resolveLembagaIdUntukTulis($request);

        try {
            $row = $this->service->update($id, $data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->success($row, 'Hari libur berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        return $this->response->success(null, 'Hari libur berhasil dihapus.');
    }

    private function resolveLembagaId(Request $request): ?int
    {
        $requested = $request->integer('lembaga_id') ?: null;

        if (! $requested) {
            return app('active_lembaga_id');
        }

        return $this->bolehAksesLembaga($requested) ? $requested : app('active_lembaga_id');
    }

    /**
     * Non-super-admin wajib dipaksa ke lembaganya sendiri — tidak boleh
     * membuat libur "Semua Lembaga" (global) atau untuk lembaga lain.
     */
    private function resolveLembagaIdUntukTulis(Request $request): ?int
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $request->integer('lembaga_id') ?: null;
        }

        return app('active_lembaga_id');
    }

    private function bolehAksesLembaga(?int $lembagaId): bool
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $lembagaId && in_array($lembagaId, $user->getLembagaIds(), true);
    }
}
