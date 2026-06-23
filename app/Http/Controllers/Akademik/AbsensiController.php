<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Akademik\AbsensiRepositoryInterface;
use App\Services\Akademik\AbsensiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AbsensiController extends Controller
{
    public function __construct(
        protected AbsensiRepositoryInterface $repo,
        protected AbsensiService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Absensi', 'Membuka halaman input absensi siswa.');

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $rombelList  = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $guruList = Guru::when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'lembaga_id']);
        $mapelList = MataPelajaran::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'lembaga_id']);
        $tahunList = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        return view('admin.akademik.absensi.index', compact(
            'lembagaList', 'rombelList', 'guruList', 'mapelList', 'tahunList'
        ));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');
        $rombelId        = $request->integer('rombel_id') ?: null;

        $query = $this->service->datatable($activeLembagaId ?? 0, $rombelId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('rombel_nama', fn($r) => $r->rombel
                ? "Kelas {$r->rombel->tingkat} - {$r->rombel->nama}"
                : '—')
            ->addColumn('guru_nama', fn($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('tanggal_fmt', fn($r) => $r->tanggal?->format('d/m/Y') ?? '—')
            ->addColumn('action', function ($r) {
                $detail = "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='lihatAbsensi({$r->id})' title='Detail'><i class='bi bi-eye'></i></button>";
                $edit = auth()->user()->hasPermissionTo('admin.akademik.absensi.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editAbsensi({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.akademik.absensi.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusAbsensi({$r->id})' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $detail . $edit . $del;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getSiswa(int $rombelId)
    {
        $siswa = $this->service->getSiswaForAbsensi($rombelId);
        return $this->response->success($siswa, 'OK');
    }

    public function detail(int $id)
    {
        $absensi = $this->repo->findById($id);
        if (!$absensi) {
            return $this->response->error('Data tidak ditemukan.', 404);
        }
        return $this->response->success($absensi, 'OK');
    }

    public function show(int $id)
    {
        return $this->detail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'        => 'required|exists:lembaga,id',
            'rombel_id'         => 'required|exists:rombel,id',
            'guru_id'           => 'nullable|exists:guru,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'tanggal'           => 'required|date',
            'jam_ke'            => 'nullable|integer|min:1|max:12',
            'keterangan'        => 'nullable|string|max:255',
        ]);

        $details = $request->validate([
            'detail'              => 'required|array|min:1',
            'detail.*.peserta_id' => 'required|integer|exists:peserta,id',
            'detail.*.status'     => 'required|in:hadir,sakit,izin,alpa',
            'detail.*.keterangan' => 'nullable|string|max:255',
        ])['detail'];

        $absensi = $this->service->store($data, $details);
        return $this->response->success($absensi, 'Absensi berhasil disimpan.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id'        => 'required|exists:lembaga,id',
            'rombel_id'         => 'required|exists:rombel,id',
            'guru_id'           => 'nullable|exists:guru,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'tanggal'           => 'required|date',
            'jam_ke'            => 'nullable|integer|min:1|max:12',
            'keterangan'        => 'nullable|string|max:255',
        ]);

        $details = $request->validate([
            'detail'              => 'required|array|min:1',
            'detail.*.peserta_id' => 'required|integer|exists:peserta,id',
            'detail.*.status'     => 'required|in:hadir,sakit,izin,alpa',
            'detail.*.keterangan' => 'nullable|string|max:255',
        ])['detail'];

        $absensi = $this->service->update($id, $data, $details);
        return $this->response->success($absensi, 'Absensi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success(null, 'Absensi berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
