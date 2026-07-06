<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Akademik\PerangkatMengajarRepositoryInterface;
use App\Services\Akademik\PerangkatMengajarService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PerangkatMengajarController extends Controller
{
    public function __construct(
        protected PerangkatMengajarRepositoryInterface $repo,
        protected PerangkatMengajarService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Perangkat Mengajar', 'Membuka halaman manajemen perangkat mengajar.');

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);

        $guruList = Guru::when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->orderBy('nama')
            ->get(['id', 'lembaga_id', 'nama', 'gelar_depan', 'gelar_belakang']);

        $mapelList = MataPelajaran::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('urutan')
            ->get(['id', 'lembaga_id', 'nama']);

        $tahunList = TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);

        return view('admin.akademik.perangkat-mengajar.index', compact(
            'lembagaList',
            'guruList',
            'mapelList',
            'tahunList',
            'semesterList',
        ));
    }

    public function list()
    {
        $activeLembagaId = app('active_lembaga_id');
        $query = $this->service->datatable($activeLembagaId);

        $jenisBadgeMap = [
            'RPP'        => 'primary',
            'Silabus'    => 'success',
            'Prota'      => 'warning',
            'Prosem'     => 'info',
            'Modul Ajar' => 'secondary',
        ];

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('tahun_nama', fn($r) => $r->tahunPelajaran?->nama ?? '—')
            ->addColumn('semester_nama', fn($r) => $r->semester?->nama ?? '—')
            ->addColumn('jenis_badge', function ($r) use ($jenisBadgeMap) {
                $color = $jenisBadgeMap[$r->jenis] ?? 'dark';
                return "<span class='badge bg-{$color}'>{$r->jenis}</span>";
            })
            ->addColumn('status_badge', fn($r) => $r->status === 'final'
                ? "<span class='badge bg-success'>Final</span>"
                : "<span class='badge bg-secondary'>Draft</span>")
            ->addColumn('file_link', function ($r) {
                if ($r->file_path) {
                    $url = asset('storage/' . $r->file_path);
                    $name = e($r->file_name ?? 'Download');
                    return "<a href='{$url}' target='_blank' class='btn btn-xs btn-light-info' title='{$name}'><i class='bi bi-download me-1'></i>Unduh</a>";
                }
                return '<span class="text-muted small">—</span>';
            })
            ->addColumn('action', function ($r) {
                $edit = auth()->user()->hasPermissionTo('admin.akademik.perangkat-mengajar.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editPerangkat({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $judul = addslashes($r->judul);
                $del = auth()->user()->hasPermissionTo('admin.akademik.perangkat-mengajar.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusPerangkat({$r->id},\"{$judul}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';
                return $edit . $del;
            })
            ->rawColumns(['jenis_badge', 'status_badge', 'file_link', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'        => 'required|exists:lembaga,id',
            'guru_id'           => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tahun_pelajaran_id'=> 'required|exists:tahun_pelajaran,id',
            'semester_id'       => 'required|exists:semester,id',
            // 'RPP' sengaja tidak ditawarkan untuk perangkat BARU — sudah pindah
            // ke modul RPP terstruktur (lihat RppController). Baris jenis=RPP
            // lama tetap bisa diedit (lihat update() di bawah, yang masih
            // mengizinkannya) supaya tidak ada data lama yang jadi tidak bisa disimpan.
            'jenis'             => 'required|in:Silabus,Prota,Prosem,Modul Ajar',
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'file'              => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'status'            => 'required|in:draft,final',
        ]);

        $file = $request->file('file');
        unset($data['file']);

        $record = $this->service->store($data, $file);
        return $this->response->success($record, 'Perangkat mengajar berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        $record = $this->repo->findById($id);
        if (!$record) {
            return $this->response->error('Data tidak ditemukan.', 404);
        }
        return $this->response->success($record, '');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id'        => 'required|exists:lembaga,id',
            'guru_id'           => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tahun_pelajaran_id'=> 'required|exists:tahun_pelajaran,id',
            'semester_id'       => 'required|exists:semester,id',
            // Beda dari store(): 'RPP' TETAP diizinkan di sini supaya baris
            // legacy jenis=RPP (dari sebelum modul RPP terstruktur ada) masih
            // bisa disunting/disimpan tanpa dipaksa ganti jenis.
            'jenis'             => 'required|in:Silabus,RPP,Prota,Prosem,Modul Ajar',
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'file'              => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'status'            => 'required|in:draft,final',
        ]);

        $file = $request->file('file');
        unset($data['file']);

        $record = $this->service->update($id, $data, $file);
        return $this->response->success($record, 'Perangkat mengajar berhasil diperbarui.');
    }

    public function getMapelByGuru(int $guruId)
    {
        $activeLembagaId = app('active_lembaga_id');

        $mapelIds = JadwalKbm::where('guru_id', $guruId)
            ->when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->pluck('mata_pelajaran_id')
            ->unique()
            ->all();

        $mapel = MataPelajaran::whereIn('id', $mapelIds)
            ->aktif()
            ->orderBy('urutan')
            ->get(['id', 'nama']);

        return response()->json([
            'status' => 200,
            'data' => $mapel
        ]);
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
            return $this->response->success(null, 'Perangkat mengajar berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
