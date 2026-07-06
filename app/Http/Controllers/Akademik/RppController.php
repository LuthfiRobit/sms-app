<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Rpp;
use App\Models\Akademik\RppBagian;
use App\Models\Akademik\RppPoin;
use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\ModelPembelajaran;
use App\Models\Master\ModelPembelajaranSintaks;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Services\Akademik\RppService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class RppController extends Controller
{
    public function __construct(
        protected RppService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu RPP', 'Membuka halaman manajemen RPP.');

        return view('admin.akademik.rpp.index', $this->dropdownData());
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');

        $query = $this->service->datatable($activeLembagaId)
            ->when($request->guru_id, fn ($q) => $q->where('guru_id', $request->guru_id))
            ->when($request->mata_pelajaran_id, fn ($q) => $q->where('mata_pelajaran_id', $request->mata_pelajaran_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn ($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn ($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('status_badge', function ($r) {
                $badge = Rpp::statusBadge($r->status);

                return "<span class='badge bg-{$badge['class']}'>{$badge['label']}</span>";
            })
            ->addColumn('file_link', function ($r) {
                if ($r->file_path) {
                    $url = asset('storage/'.$r->file_path);

                    return "<a href='{$url}' target='_blank' class='btn btn-xs btn-light-info' title='Unduh PDF'><i class='bi bi-download me-1'></i>Unduh</a>";
                }

                return '<span class="text-muted small">—</span>';
            })
            ->addColumn('action', function ($r) {
                $lihat = "<a href='".route('admin.akademik.rpp.show', $r->id)."' class='btn btn-xs btn-icon btn-light-info me-1' title='Detail'><i class='bi bi-eye'></i></a>";
                $edit = auth()->user()->hasPermissionTo('admin.akademik.rpp.update')
                    ? "<a href='".route('admin.akademik.rpp.edit', $r->id)."' class='btn btn-xs btn-icon btn-light-primary me-1' title='Edit'><i class='bi bi-pencil'></i></a>"
                    : '';
                $judul = addslashes($r->materi);
                $hapus = auth()->user()->hasPermissionTo('admin.akademik.rpp.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusRpp({$r->id},\"{$judul}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $lihat.$edit.$hapus;
            })
            ->rawColumns(['status_badge', 'file_link', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.akademik.rpp.form', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateRpp($request);

        try {
            $rpp = $this->service->store($data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success($rpp, 'RPP berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        try {
            $rpp = $this->service->find($id);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }

        $bagianList = RppBagian::aktif()->with(['poin' => fn ($q) => $q->aktif()->orderBy('urutan')])->orderBy('urutan')->get();

        return view('admin.akademik.rpp.show', compact('rpp', 'bagianList'));
    }

    public function edit(int $id)
    {
        try {
            $rpp = $this->service->find($id);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }

        return view('admin.akademik.rpp.form', $this->formData($rpp));
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validateRpp($request);

        try {
            $rpp = $this->service->update($id, $data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success($rpp, 'RPP berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'RPP berhasil dihapus.');
    }

    public function getMapelByGuru(int $guruId)
    {
        $activeLembagaId = app('active_lembaga_id');

        $mapelIds = JadwalKbm::where('guru_id', $guruId)
            ->when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))
            ->pluck('mata_pelajaran_id')
            ->unique()
            ->all();

        $mapel = MataPelajaran::whereIn('id', $mapelIds)->aktif()->orderBy('urutan')->get(['id', 'nama']);

        return response()->json(['status' => 200, 'data' => $mapel]);
    }

    // ── Helper data & validasi ───────────────────────────────────────────────

    private function dropdownData(): array
    {
        $activeLembagaId = app('active_lembaga_id');

        return [
            'lembagaList' => Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']),
            'guruList' => Guru::when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))->orderBy('nama')->get(['id', 'lembaga_id', 'nama', 'gelar_depan', 'gelar_belakang']),
            'mapelList' => MataPelajaran::byLembaga($activeLembagaId)->aktif()->orderBy('urutan')->get(['id', 'lembaga_id', 'nama']),
            'tahunList' => TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama']),
            'semesterList' => Semester::orderBy('nama')->get(['id', 'nama']),
        ];
    }

    private function formData(?Rpp $rpp = null): array
    {
        $bagianList = RppBagian::aktif()->with(['poin' => fn ($q) => $q->aktif()->orderBy('urutan')])->orderBy('urutan')->get();
        $modelList = ModelPembelajaran::aktif()->with('sintaks')->orderBy('urutan')->get();

        return $this->dropdownData() + compact('bagianList', 'modelList', 'rpp');
    }

    /** Bangun rules statis (header) + dinamis (per poin aktif) sekaligus. */
    private function validateRpp(Request $request): array
    {
        $rules = [
            'lembaga_id' => 'required|exists:lembaga,id',
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester_id' => 'required|exists:semester,id',
            'model_pembelajaran_id' => 'required|exists:model_pembelajaran,id',
            'fase_kelas' => 'required|string|max:100',
            'materi' => 'required|string|max:255',
            'alokasi_waktu' => 'required|string|max:100',
            // Submateri: baris berulang (satu input per submateri), jumlah tidak
            // dibatasi — inilah yang ditampilkan sebagai daftar materi per-sesi
            // di app mobile guru.
            'submateri' => 'nullable|array',
            'submateri.*' => 'nullable|string|max:255',
            'inti' => 'nullable|array',
            'inti.*' => 'nullable|string',
        ];

        foreach (RppPoin::aktif()->get() as $poin) {
            if ($poin->tipe === 'model_pembelajaran') {
                continue;
            }

            $required = $poin->is_required ? 'required' : 'nullable';
            $rules["poin.{$poin->id}"] = match ($poin->tipe) {
                'pasangan_kolom', 'pilih_master' => "{$required}|array",
                default => "{$required}|string",
            };
        }

        $data = $request->validate($rules);

        if (! empty($data['inti'])) {
            $validSintaksIds = ModelPembelajaranSintaks::where('model_pembelajaran_id', $data['model_pembelajaran_id'])
                ->pluck('id')->map(fn ($id) => (string) $id)->all();
            $invalid = array_diff(array_map('strval', array_keys($data['inti'])), $validSintaksIds);

            abort_unless(empty($invalid), 422, 'Data Inti tidak sesuai dengan Model Pembelajaran yang dipilih.');
        }

        return $data;
    }
}
