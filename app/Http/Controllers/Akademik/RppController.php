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
    /** Cache per-request agar guruContext() aman dipanggil berkali-kali tanpa query berulang. */
    private ?Guru $guruContextCache = null;

    private bool $guruContextResolved = false;

    public function __construct(
        protected RppService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu RPP', 'Membuka halaman manajemen RPP.');

        return view('admin.akademik.rpp.index', $this->dropdownData() + ['guruAktif' => $this->guruContext()]);
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');
        $guruAktif = $this->guruContext();

        $query = $this->service->datatable($activeLembagaId)
            // Guru HANYA boleh melihat RPP miliknya sendiri — dipaksa dari
            // identitas login, mengabaikan filter guru_id apapun yang dikirim
            // klien (mencegah guru mengintip/mengubah RPP guru lain lewat
            // manipulasi parameter).
            ->when($guruAktif, fn ($q) => $q->where('guru_id', $guruAktif->id))
            ->when(! $guruAktif && $request->guru_id, fn ($q) => $q->where('guru_id', $request->guru_id))
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
                $duplikat = auth()->user()->hasPermissionTo('admin.akademik.rpp.store')
                    ? "<button class='btn btn-xs btn-icon btn-light-warning me-1' onclick='duplikatRpp({$r->id},\"{$judul}\")' title='Duplikat'><i class='bi bi-files'></i></button>"
                    : '';
                $hapus = auth()->user()->hasPermissionTo('admin.akademik.rpp.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusRpp({$r->id},\"{$judul}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $lihat.$edit.$duplikat.$hapus;
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
        $data = $this->validateRpp($request, $this->guruContext());

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

        $this->authorizeOwnership($rpp);

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

        $this->authorizeOwnership($rpp);

        return view('admin.akademik.rpp.form', $this->formData($rpp));
    }

    public function update(Request $request, int $id)
    {
        try {
            $existing = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        $this->authorizeOwnership($existing);

        $data = $this->validateRpp($request, $this->guruContext());

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
            $rpp = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        $this->authorizeOwnership($rpp);

        try {
            $this->service->destroy($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'RPP berhasil dihapus.');
    }

    public function duplicate(Request $request, int $id)
    {
        try {
            $sumber = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        $this->authorizeOwnership($sumber);

        $data = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        try {
            $salinan = $this->service->duplicate($id, $data['tahun_pelajaran_id'], $data['semester_id']);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(['id' => $salinan->id], 'RPP berhasil diduplikat.');
    }

    public function getMapelByGuru(int $guruId)
    {
        $activeLembagaId = app('active_lembaga_id');

        // Guru tidak boleh mengintip mapel guru lain lewat manipulasi URL —
        // paksa ke id miliknya sendiri kalau yang login berperan sebagai guru.
        $guruAktif = $this->guruContext();
        $guruId = $guruAktif?->id ?? $guruId;

        $mapelIds = JadwalKbm::where('guru_id', $guruId)
            ->when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))
            ->pluck('mata_pelajaran_id')
            ->unique()
            ->all();

        $mapel = MataPelajaran::whereIn('id', $mapelIds)->aktif()->orderBy('urutan')->get(['id', 'nama']);

        return response()->json(['status' => 200, 'data' => $mapel]);
    }

    // ── Helper akses guru (auto-scoping) ────────────────────────────────────

    /**
     * Kalau user login berperan sebagai guru (role 'guru'), kembalikan record
     * Guru yang tertaut ke akunnya — dipakai untuk mengunci Lembaga/Guru/
     * Tahun Ajaran/Semester secara otomatis di form RPP (guru tidak perlu,
     * dan tidak boleh, memilih identitas guru atau lembaga lain). Non-guru
     * (admin/kepala sekolah/dst) mengembalikan null — dropdown tetap manual.
     * Di-cache per-request supaya aman dipanggil berkali-kali (index/list/
     * store/update/destroy/duplicate semua memanggilnya) tanpa query berulang.
     */
    private function guruContext(): ?Guru
    {
        if (! $this->guruContextResolved) {
            $this->guruContextResolved = true;
            $user = auth()->user();

            if ($user && $user->hasRole('guru')) {
                $guru = $user->guru()->with('lembaga')->first();

                abort_if(! $guru, 403, 'Akun ini belum tertaut ke data guru manapun. Hubungi admin untuk menautkan akun Anda.');

                $this->guruContextCache = $guru;
            }
        }

        return $this->guruContextCache;
    }

    /** Tahun ajaran & semester yang sedang berjalan — dipakai sebagai default otomatis untuk guru. */
    private function periodeAktif(): array
    {
        $tahun = TahunPelajaran::where('status', 'aktif')->first();
        $semester = $tahun ? Semester::where('tahun_pelajaran_id', $tahun->id)->where('status', 'aktif')->first() : null;

        return [$tahun, $semester];
    }

    /** Guru cuma boleh mengakses (lihat/edit/hapus/duplikat) RPP miliknya sendiri. */
    private function authorizeOwnership(Rpp $rpp): void
    {
        $guruAktif = $this->guruContext();

        abort_if($guruAktif && $rpp->guru_id !== $guruAktif->id, 403, 'Anda tidak memiliki akses ke RPP milik guru lain.');
    }

    // ── Helper data & validasi ───────────────────────────────────────────────

    private function dropdownData(): array
    {
        $activeLembagaId = app('active_lembaga_id');
        $guruAktif = $this->guruContext();

        return [
            'lembagaList' => Lembaga::when($guruAktif, fn ($q) => $q->where('id', $guruAktif->lembaga_id))
                ->orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']),
            'guruList' => Guru::when($guruAktif, fn ($q) => $q->where('id', $guruAktif->id))
                ->when(! $guruAktif && $activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))
                ->orderBy('nama')->get(['id', 'lembaga_id', 'nama', 'gelar_depan', 'gelar_belakang']),
            'mapelList' => MataPelajaran::byLembaga($guruAktif ? $guruAktif->lembaga_id : $activeLembagaId)->aktif()->orderBy('urutan')->get(['id', 'lembaga_id', 'nama']),
            'tahunList' => TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama']),
            'semesterList' => Semester::orderBy('nama')->get(['id', 'nama']),
        ];
    }

    private function formData(?Rpp $rpp = null): array
    {
        $bagianList = RppBagian::aktif()->with(['poin' => fn ($q) => $q->aktif()->orderBy('urutan')])->orderBy('urutan')->get();
        $modelList = ModelPembelajaran::aktif()->with('sintaks')->orderBy('urutan')->get();
        $guruAktif = $this->guruContext();

        [$tahunAktif, $semesterAktif] = $guruAktif ? $this->periodeAktif() : [null, null];
        abort_if($guruAktif && (! $tahunAktif || ! $semesterAktif), 422, 'Tahun ajaran/semester aktif belum diatur. Hubungi admin.');

        return $this->dropdownData() + compact('bagianList', 'modelList', 'rpp', 'guruAktif', 'tahunAktif', 'semesterAktif');
    }

    /** Bangun rules statis (header) + dinamis (per poin aktif) sekaligus. */
    private function validateRpp(Request $request, ?Guru $guruAktif = null): array
    {
        $rules = [
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
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

        // Non-guru (admin dkk) memilih sendiri lembaga/guru/tahun/semester
        // lewat dropdown — untuk guru, keempatnya diisi paksa dari identitas
        // login (lihat bawah), JANGAN dipercaya dari input klien sama sekali.
        if (! $guruAktif) {
            $rules['lembaga_id'] = 'required|exists:lembaga,id';
            $rules['guru_id'] = 'required|exists:guru,id';
            $rules['tahun_pelajaran_id'] = 'required|exists:tahun_pelajaran,id';
            $rules['semester_id'] = 'required|exists:semester,id';
        }

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

        if ($guruAktif) {
            [$tahun, $semester] = $this->periodeAktif();
            abort_if(! $tahun || ! $semester, 422, 'Tahun ajaran/semester aktif belum diatur. Hubungi admin.');

            $data['lembaga_id'] = $guruAktif->lembaga_id;
            $data['guru_id'] = $guruAktif->id;
            $data['tahun_pelajaran_id'] = $tahun->id;
            $data['semester_id'] = $semester->id;
        }

        if (! empty($data['inti'])) {
            $validSintaksIds = ModelPembelajaranSintaks::where('model_pembelajaran_id', $data['model_pembelajaran_id'])
                ->pluck('id')->map(fn ($id) => (string) $id)->all();
            $invalid = array_diff(array_map('strval', array_keys($data['inti'])), $validSintaksIds);

            abort_unless(empty($invalid), 422, 'Data Inti tidak sesuai dengan Model Pembelajaran yang dipilih.');
        }

        return $data;
    }
}
