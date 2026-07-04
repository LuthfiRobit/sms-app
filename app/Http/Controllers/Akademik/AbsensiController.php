<?php

namespace App\Http\Controllers\Akademik;

use App\Exports\RekapAbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Models\Master\Rombel;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Akademik\AbsensiRepositoryInterface;
use App\Services\Akademik\AbsensiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use App\Support\QrToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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
        $rombelList = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $guruList = Guru::when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))
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
        $rombelId = $request->integer('rombel_id') ?: null;

        $query = $this->service->datatable($activeLembagaId ?? 0, $rombelId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('rombel_nama', fn ($r) => $r->rombel
                ? "Kelas {$r->rombel->tingkat} - {$r->rombel->nama}"
                : '—')
            ->addColumn('guru_nama', fn ($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn ($r) => $r->mataPelajaran?->nama ?? '—')
            ->addColumn('tanggal_fmt', fn ($r) => $r->tanggal?->format('d/m/Y') ?? '—')
            ->addColumn('action', function ($r) {
                $detail = "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='lihatAbsensi({$r->id})' title='Detail'><i class='bi bi-eye'></i></button>";
                $edit = auth()->user()->hasPermissionTo('admin.akademik.absensi.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editAbsensi({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.akademik.absensi.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusAbsensi({$r->id})' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $detail.$edit.$del;
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
        if (! $absensi) {
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
            'lembaga_id' => 'required|exists:lembaga,id',
            'rombel_id' => 'required|exists:rombel,id',
            'guru_id' => 'nullable|exists:guru,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'tanggal' => 'required|date',
            'jam_ke' => 'nullable|integer|min:1|max:12',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $details = $request->validate([
            'detail' => 'required|array|min:1',
            'detail.*.peserta_id' => 'required|integer|exists:peserta,id',
            'detail.*.status' => 'required|in:hadir,sakit,izin,alpa',
            'detail.*.keterangan' => 'nullable|string|max:255',
        ])['detail'];

        $absensi = $this->service->store($data, $details);

        return $this->response->success($absensi, 'Absensi berhasil disimpan.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'lembaga_id' => 'required|exists:lembaga,id',
            'rombel_id' => 'required|exists:rombel,id',
            'guru_id' => 'nullable|exists:guru,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'tanggal' => 'required|date',
            'jam_ke' => 'nullable|integer|min:1|max:12',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $details = $request->validate([
            'detail' => 'required|array|min:1',
            'detail.*.peserta_id' => 'required|integer|exists:peserta,id',
            'detail.*.status' => 'required|in:hadir,sakit,izin,alpa',
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

    // ─── REKAP ABSENSI ───────────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $this->logActivity->log('Akses Rekap Absensi', 'Membuka halaman rekap absensi siswa.');
        $activeLembagaId = app('active_lembaga_id');

        $rombelList = Rombel::byLembaga($activeLembagaId)->aktif()->orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);
        $tahunList = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        $rekap = collect();
        $rombel = null;
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $rombelId = $request->integer('rombel_id') ?: null;

        if ($rombelId && $tanggalMulai && $tanggalAkhir) {
            $rekap = $this->service->getRekapAbsensi($rombelId, $tanggalMulai, $tanggalAkhir);
            $rombel = Rombel::find($rombelId);
        }

        return view('admin.akademik.absensi.rekap', compact(
            'rombelList', 'semesterList', 'tahunList', 'rekap', 'rombel', 'tanggalMulai', 'tanggalAkhir', 'rombelId'
        ));
    }

    public function rekapPdf(Request $request)
    {
        $rombelId = $request->integer('rombel_id');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $rekap = $this->service->getRekapAbsensi($rombelId, $tanggalMulai, $tanggalAkhir);
        $rombel = Rombel::with('lembaga')->find($rombelId);

        $pdf = Pdf::loadView('pdf.akademik.rekap-absensi', compact('rekap', 'rombel', 'tanggalMulai', 'tanggalAkhir'))
            ->setPaper('a4', 'landscape');

        $filename = 'rekap-absensi-'.($rombel?->nama ?? 'kelas').'-'.$tanggalMulai.'.pdf';

        return $pdf->download($filename);
    }

    public function rekapExcel(Request $request)
    {
        $rombelId = $request->integer('rombel_id');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $rekap = $this->service->getRekapAbsensi($rombelId, $tanggalMulai, $tanggalAkhir);
        $rombel = Rombel::with('lembaga')->find($rombelId);

        $filename = 'rekap-absensi-'.($rombel?->nama ?? 'kelas').'-'.$tanggalMulai.'.xlsx';

        return Excel::download(new RekapAbsensiExport($rekap, $rombel, $tanggalMulai, $tanggalAkhir), $filename);
    }

    // ── TAP Kehadiran QR ─────────────────────────────────────────────────────

    /**
     * Halaman TAP scan — menampilkan daftar siswa dengan QR tiap siswa,
     * serta area scan kamera untuk merekam kehadiran.
     */
    public function tapScan(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');
        $rombelList = Rombel::byLembaga($activeLembagaId)->aktif()->orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat']);

        $rombelId = $request->integer('rombel_id');
        $tanggal = $request->input('tanggal', today()->toDateString());

        $siswaList = collect();
        $rombel = null;

        if ($rombelId) {
            $rombel = Rombel::with('lembaga')->find($rombelId);
            $siswaList = DB::table('rombel_siswa')
                ->join('peserta', 'peserta.id', '=', 'rombel_siswa.peserta_id')
                ->where('rombel_siswa.rombel_id', $rombelId)
                ->whereNull('peserta.deleted_at')
                ->select('peserta.id', 'peserta.nama_lengkap', 'rombel_siswa.no_absen')
                ->orderBy('rombel_siswa.no_absen')
                ->orderBy('peserta.nama_lengkap')
                ->get()
                ->map(function ($siswa) use ($tanggal) {
                    $siswa->qr_token = QrToken::generate((int) $siswa->id, $tanggal);

                    return $siswa;
                });
        }

        $this->logActivity->log('TAP Kehadiran QR', 'Membuka halaman TAP scan kehadiran.');

        return view('admin.akademik.absensi.tap', compact('rombelList', 'rombelId', 'tanggal', 'rombel', 'siswaList'));
    }

    /**
     * Mobile-friendly endpoint: terima QR token, cari siswa, catat hadir.
     */
    public function tapRecord(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'absensi_id' => 'required|integer|exists:absensi,id',
        ]);

        $payload = QrToken::verify($request->input('token'));

        if (! $payload) {
            return $this->response->error('Token QR tidak valid atau sudah kedaluwarsa.');
        }

        ['peserta_id' => $pesertaId, 'tanggal' => $tanggal] = $payload;

        $absensiId = $request->integer('absensi_id');

        // Pastikan peserta ada di absensi ini
        $existing = DB::table('absensi_detail')
            ->where('absensi_id', $absensiId)
            ->where('peserta_id', $pesertaId)
            ->first();

        if (! $existing) {
            return $this->response->error('Siswa tidak terdaftar pada sesi absensi ini.');
        }

        if ($existing->status === 'hadir') {
            $peserta = DB::table('peserta')->where('id', $pesertaId)->value('nama_lengkap');

            return $this->response->success(['nama' => $peserta, 'status' => 'hadir'], 'Sudah tercatat hadir.');
        }

        // Catatan: absensi_detail tidak punya kolom timestamps.
        DB::table('absensi_detail')
            ->where('absensi_id', $absensiId)
            ->where('peserta_id', $pesertaId)
            ->update(['status' => 'hadir']);

        $peserta = DB::table('peserta')->where('id', $pesertaId)->value('nama_lengkap');

        $this->logActivity->log('TAP Kehadiran', "Siswa {$peserta} (ID #{$pesertaId}) hadir via TAP QR pada absensi #{$absensiId}.");

        return $this->response->success(['nama' => $peserta, 'status' => 'hadir'], "{$peserta} berhasil dicatat hadir.");
    }
}
