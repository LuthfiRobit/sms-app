<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Master\JadwalKbmRepositoryInterface;
use App\Services\LogActivityService;
use App\Services\Master\JadwalKbmService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JadwalKbmController extends Controller
{
    public function __construct(
        protected JadwalKbmRepositoryInterface $repo,
        protected JadwalKbmService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Jadwal KBM', 'Membuka halaman manajemen jadwal kegiatan belajar mengajar.');

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $tahunList = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);
        $rombelList = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $guruList = Guru::when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'lembaga_id'])
            ->map(fn ($g) => [
                'id' => $g->id,
                'nama' => $g->nama_lengkap,
                'lembaga_id' => $g->lembaga_id,
            ]);
        $mapelList = MataPelajaran::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'lembaga_id']);

        return view('admin.master.jadwal-kbm.index', compact(
            'lembagaList', 'tahunList', 'rombelList', 'guruList', 'mapelList'
        ));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');
        $rombelId = $request->integer('rombel_id') ?: null;
        $tahunId = $request->integer('tahun_id') ?: null;
        $guruId = $request->integer('guru_id') ?: null;

        $query = $this->service->datatable($activeLembagaId, $rombelId, $tahunId, $guruId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('lembaga_nama', fn ($r) => $r->lembaga?->nama ?? '—')
            ->addColumn('rombel_nama', fn ($r) => $r->rombel
                ? "Kelas {$r->rombel->tingkat} - {$r->rombel->nama}"
                : '—')
            ->addColumn('guru_nama', fn ($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn ($r) => $r->mataPelajaran
                ? "[{$r->mataPelajaran->kode}] {$r->mataPelajaran->nama}"
                : '—')
            ->addColumn('jam', fn ($r) => $r->jam_mulai.' – '.$r->jam_selesai)
            ->addColumn('action', function ($r) {
                $edit = auth()->user()->hasPermissionTo('admin.master.jadwal-kbm.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editJadwal({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.master.jadwal-kbm.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusJadwal({$r->id})' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $edit.$del;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'rombel_id' => 'required|exists:rombel,id',
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jam_ke' => 'nullable|integer|min:1|max:12',
            'ruangan' => 'nullable|string|max:50',
        ]);

        $jadwal = $this->service->store($data);

        return $this->response->success('Jadwal KBM berhasil ditambahkan.', $jadwal);
    }

    public function show(int $id)
    {
        $jadwal = $this->repo->findById($id);
        if (! $jadwal) {
            return $this->response->error('Jadwal tidak ditemukan.', 404);
        }

        return $this->response->success($jadwal, 'OK');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'rombel_id' => 'required|exists:rombel,id',
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jam_ke' => 'nullable|integer|min:1|max:12',
            'ruangan' => 'nullable|string|max:50',
        ]);

        $jadwal = $this->service->update($id, $data);

        return $this->response->success('Jadwal KBM berhasil diperbarui.', $jadwal);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);

            return $this->response->success('Jadwal KBM berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
