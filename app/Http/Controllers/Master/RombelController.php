<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\Jurusan;
use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Master\RombelRepositoryInterface;
use App\Services\Master\RombelService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RombelController extends Controller
{
    public function __construct(
        protected RombelRepositoryInterface $repo,
        protected RombelService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {
    }

    public function index()
    {
        $this->logActivity->log('Akses Menu Rombel', 'Membuka halaman manajemen rombel / kelas.');
        $lembagaList  = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $tahunList    = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);
        $tahunAktifId = $tahunList->firstWhere('status', 'aktif')?->id;
        // Semua jurusan tanpa scope — JS yang filter per lembaga terpilih
        $allJurusan   = Jurusan::aktif()->orderBy('nama')->get(['id', 'nama', 'kode', 'lembaga_id']);

        return view('admin.master.rombel.index', compact('lembagaList', 'tahunList', 'tahunAktifId', 'allJurusan'));
    }

    public function list()
    {
        $lembagaId = app('active_lembaga_id');
        $query = $this->service->datatable($lembagaId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('lembaga_nama', fn($r) => $r->lembaga?->nama ?? '—')
            ->addColumn('tahun_pelajaran_nama', fn($r) => $r->tahunPelajaran?->nama ?? '—')
            ->addColumn('jurusan_nama', fn($r) => $r->jurusan?->nama ?? '<span class="text-muted">—</span>')
            ->addColumn('status_badge', fn($r) => $r->status === 'aktif'
                ? '<span class="badge bg-light-success text-success">Aktif</span>'
                : '<span class="badge bg-light-secondary text-secondary">Non-Aktif</span>')
            ->addColumn('action', function ($r) {
                $namaJs = addslashes($r->nama);
                $edit = auth()->user()->hasPermissionTo('admin.master.rombel.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editRombel({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.master.rombel.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusRombel({$r->id},\"{$namaJs}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $edit . $del;
            })
            ->rawColumns(['jurusan_nama', 'status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'tingkat' => 'required|integer|min:1|max:12',
            'nama' => 'required|string|max:50',
            'wali_kelas' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $rombel = $this->service->store($data);
        return $this->response->success('Rombel berhasil ditambahkan.', $rombel);
    }

    public function show(int $id)
    {
        $rombel = $this->repo->findById($id, ['lembaga', 'tahunPelajaran', 'jurusan']);
        if (!$rombel) {
            return $this->response->error('Rombel tidak ditemukan.', 404);
        }
        return $this->response->success('', $rombel);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'tingkat' => 'required|integer|min:1|max:12',
            'nama' => 'required|string|max:50',
            'wali_kelas' => 'nullable|string|max:255',
            'kapasitas' => 'required|integer|min:1|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $rombel = $this->service->update($id, $data);
        return $this->response->success('Rombel berhasil diperbarui.', $rombel);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success('Rombel berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    /** AJAX: daftar guru aktif per lembaga untuk autocomplete wali kelas. */
    public function guruByLembaga(int $lembagaId)
    {
        $guru = Guru::where('lembaga_id', $lembagaId)
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip'])
            ->map(fn($g) => [
                'id'    => $g->id,
                'nama'  => $g->nama_lengkap,
                'nip'   => $g->nip,
            ]);

        return response()->json($guru);
    }
}
