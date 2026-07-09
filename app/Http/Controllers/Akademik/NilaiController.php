<?php

namespace App\Http\Controllers\Akademik;

use App\Exports\RekapNilaiExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\JadwalKbm;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Services\Akademik\NilaiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NilaiController extends Controller
{
    /** Cache per-request agar guruContext() aman dipanggil berkali-kali tanpa query berulang. */
    private ?Guru $guruContextCache = null;

    private bool $guruContextResolved = false;

    public function __construct(
        protected NilaiService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Input Nilai', 'Membuka halaman input nilai siswa.');

        $guruAktif = $this->guruContext();
        $activeLembagaId = $guruAktif ? $guruAktif->lembaga_id : app('active_lembaga_id');

        $lembagaList = Lembaga::when($guruAktif, fn ($q) => $q->where('id', $guruAktif->lembaga_id))
            ->orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $rombelList = $this->rombelUntukGuru($guruAktif, $activeLembagaId);
        $mapelList = $this->mapelUntukGuru($guruAktif, $activeLembagaId);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);
        $tahunList = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        return view('admin.akademik.nilai.index', compact(
            'lembagaList', 'rombelList', 'mapelList', 'semesterList', 'tahunList'
        ) + ['guruAktif' => $guruAktif]);
    }

    public function sheet(Request $request)
    {
        $request->validate([
            'rombel_id' => 'required|integer|exists:rombel,id',
            'mata_pelajaran_id' => 'required|integer|exists:mata_pelajaran,id',
            'semester_id' => 'required|integer|exists:semester,id',
        ]);

        $this->authorizeGuruMengajar($request->integer('rombel_id'), $request->integer('mata_pelajaran_id'));

        $sheet = $this->service->getNilaiSheet(
            $request->integer('rombel_id'),
            $request->integer('mata_pelajaran_id'),
            $request->integer('semester_id'),
        );

        return $this->response->success($sheet, 'OK');
    }

    public function save(Request $request)
    {
        $request->validate([
            'lembaga_id' => 'required|integer|exists:lembaga,id',
            'rombel_id' => 'required|integer|exists:rombel,id',
            'mata_pelajaran_id' => 'required|integer|exists:mata_pelajaran,id',
            'semester_id' => 'required|integer|exists:semester,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'rows' => 'required|array|min:1',
            'rows.*.peserta_id' => 'required|integer|exists:peserta,id',
            'rows.*.nilai_harian' => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uts' => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uas' => 'nullable|numeric|min:0|max:100',
            'rows.*.catatan' => 'nullable|string|max:500',
        ]);

        $this->authorizeGuruMengajar($request->integer('rombel_id'), $request->integer('mata_pelajaran_id'));

        // Guru tidak boleh menitipkan lembaga_id sendiri — paksa dari identitas
        // login-nya, JANGAN dipercaya dari input klien (lihat guruContext()).
        $guruAktif = $this->guruContext();
        $lembagaId = $guruAktif ? $guruAktif->lembaga_id : $request->integer('lembaga_id');

        $this->service->save(
            $request->input('rows'),
            $lembagaId,
            $request->integer('rombel_id'),
            $request->integer('mata_pelajaran_id'),
            $request->integer('semester_id'),
            $request->integer('tahun_pelajaran_id'),
        );

        return $this->response->success(null, 'Nilai berhasil disimpan.');
    }

    // ── Helper akses guru (auto-scoping) ────────────────────────────────────

    /** Sama persis polanya dengan RppController::guruContext() — lihat dokblok di sana. */
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

    /** Rombel untuk dropdown — guru cuma lihat kelas yang benar-benar ia ajar (via JadwalKbm). */
    private function rombelUntukGuru(?Guru $guruAktif, ?int $activeLembagaId)
    {
        if ($guruAktif) {
            $rombelIds = JadwalKbm::where('guru_id', $guruAktif->id)
                ->where('lembaga_id', $guruAktif->lembaga_id)
                ->pluck('rombel_id')->unique();

            return Rombel::whereIn('id', $rombelIds)->aktif()->orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        }

        return Rombel::byLembaga($activeLembagaId)->aktif()->orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat', 'lembaga_id']);
    }

    /** Mapel untuk dropdown — guru cuma lihat mapel yang benar-benar ia ajar (via JadwalKbm). */
    private function mapelUntukGuru(?Guru $guruAktif, ?int $activeLembagaId)
    {
        if ($guruAktif) {
            $mapelIds = JadwalKbm::where('guru_id', $guruAktif->id)
                ->where('lembaga_id', $guruAktif->lembaga_id)
                ->pluck('mata_pelajaran_id')->unique();

            return MataPelajaran::whereIn('id', $mapelIds)->aktif()->orderBy('nama')->get(['id', 'nama', 'lembaga_id']);
        }

        return MataPelajaran::byLembaga($activeLembagaId)->aktif()->orderBy('nama')->get(['id', 'nama', 'lembaga_id']);
    }

    /** Guru cuma boleh buka/simpan sheet nilai utk rombel+mapel yang benar-benar ia ajar. */
    private function authorizeGuruMengajar(int $rombelId, int $mapelId): void
    {
        $guruAktif = $this->guruContext();

        if (! $guruAktif) {
            return;
        }

        $mengajar = JadwalKbm::where('guru_id', $guruAktif->id)
            ->where('rombel_id', $rombelId)
            ->where('mata_pelajaran_id', $mapelId)
            ->exists();

        abort_unless($mengajar, 403, 'Anda tidak mengajar mata pelajaran ini di kelas tersebut.');
    }

    // ─── REKAP NILAI ─────────────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $this->logActivity->log('Akses Rekap Nilai', 'Membuka halaman rekap nilai siswa.');
        $activeLembagaId = app('active_lembaga_id');

        $rombelList = Rombel::byLembaga($activeLembagaId)->aktif()->orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);
        $tahunList = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        $rekap = collect();
        $mapelList = collect();
        $rombel = null;
        $semester = null;
        $tahun = null;

        $rombelId = $request->integer('rombel_id') ?: null;
        $semesterId = $request->integer('semester_id') ?: null;
        $tahunId = $request->integer('tahun_pelajaran_id') ?: null;

        if ($rombelId && $semesterId && $tahunId) {
            ['siswa' => $rekap, 'mapel' => $mapelList] = $this->service->getRekapNilai($rombelId, $semesterId, $tahunId);
            $rombel = Rombel::find($rombelId);
            $semester = Semester::find($semesterId);
            $tahun = TahunPelajaran::find($tahunId);
        }

        return view('admin.akademik.nilai.rekap', compact(
            'rombelList', 'semesterList', 'tahunList', 'rekap', 'mapelList',
            'rombel', 'semester', 'tahun', 'rombelId', 'semesterId', 'tahunId'
        ));
    }

    public function rekapPdf(Request $request)
    {
        $rombelId = $request->integer('rombel_id');
        $semesterId = $request->integer('semester_id');
        $tahunId = $request->integer('tahun_pelajaran_id');

        ['siswa' => $rekap, 'mapel' => $mapelList] = $this->service->getRekapNilai($rombelId, $semesterId, $tahunId);
        $rombel = Rombel::with('lembaga')->find($rombelId);
        $semester = Semester::find($semesterId);
        $tahun = TahunPelajaran::find($tahunId);

        $pdf = Pdf::loadView('pdf.akademik.rekap-nilai', compact('rekap', 'mapelList', 'rombel', 'semester', 'tahun'))
            ->setPaper('a4', 'landscape');

        $filename = 'rekap-nilai-'.($rombel?->nama ?? 'kelas').'-'.($semester?->nama ?? '').'.pdf';

        return $pdf->download($filename);
    }

    public function rekapExcel(Request $request)
    {
        $rombelId = $request->integer('rombel_id');
        $semesterId = $request->integer('semester_id');
        $tahunId = $request->integer('tahun_pelajaran_id');

        ['siswa' => $rekap, 'mapel' => $mapelList] = $this->service->getRekapNilai($rombelId, $semesterId, $tahunId);
        $rombel = Rombel::with('lembaga')->find($rombelId);
        $semester = Semester::find($semesterId);
        $tahun = TahunPelajaran::find($tahunId);

        $filename = 'rekap-nilai-'.($rombel?->nama ?? 'kelas').'-'.($semester?->nama ?? '').'.xlsx';

        return Excel::download(new RekapNilaiExport($rekap, $mapelList, $rombel, $semester, $tahun), $filename);
    }
}
