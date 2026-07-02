<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
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
    public function __construct(
        protected NilaiService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Input Nilai', 'Membuka halaman input nilai siswa.');

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $rombelList  = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id']);
        $mapelList = MataPelajaran::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'lembaga_id']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);
        $tahunList    = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        return view('admin.akademik.nilai.index', compact(
            'lembagaList', 'rombelList', 'mapelList', 'semesterList', 'tahunList'
        ));
    }

    public function sheet(Request $request)
    {
        $request->validate([
            'rombel_id'         => 'required|integer|exists:rombel,id',
            'mata_pelajaran_id' => 'required|integer|exists:mata_pelajaran,id',
            'semester_id'       => 'required|integer|exists:semester,id',
        ]);

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
            'lembaga_id'        => 'required|integer|exists:lembaga,id',
            'rombel_id'         => 'required|integer|exists:rombel,id',
            'mata_pelajaran_id' => 'required|integer|exists:mata_pelajaran,id',
            'semester_id'       => 'required|integer|exists:semester,id',
            'tahun_pelajaran_id'=> 'required|integer|exists:tahun_pelajaran,id',
            'rows'              => 'required|array|min:1',
            'rows.*.peserta_id' => 'required|integer|exists:peserta,id',
            'rows.*.nilai_harian' => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uts'    => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uas'    => 'nullable|numeric|min:0|max:100',
            'rows.*.catatan'      => 'nullable|string|max:500',
        ]);

        $this->service->save(
            $request->input('rows'),
            $request->integer('lembaga_id'),
            $request->integer('rombel_id'),
            $request->integer('mata_pelajaran_id'),
            $request->integer('semester_id'),
            $request->integer('tahun_pelajaran_id'),
        );

        return $this->response->success(null, 'Nilai berhasil disimpan.');
    }

    // ─── REKAP NILAI ─────────────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $this->logActivity->log('Akses Rekap Nilai', 'Membuka halaman rekap nilai siswa.');
        $activeLembagaId = app('active_lembaga_id');

        $rombelList   = Rombel::byLembaga($activeLembagaId)->aktif()->orderBy('tingkat')->orderBy('nama')->get(['id','nama','tingkat','lembaga_id']);
        $semesterList = Semester::orderBy('nama')->get(['id','nama']);
        $tahunList    = TahunPelajaran::orderByDesc('nama')->get(['id','nama','status']);

        $rekap    = collect();
        $mapelList = collect();
        $rombel   = null;
        $semester = null;
        $tahun    = null;

        $rombelId   = $request->integer('rombel_id') ?: null;
        $semesterId = $request->integer('semester_id') ?: null;
        $tahunId    = $request->integer('tahun_pelajaran_id') ?: null;

        if ($rombelId && $semesterId && $tahunId) {
            ['siswa' => $rekap, 'mapel' => $mapelList] = $this->service->getRekapNilai($rombelId, $semesterId, $tahunId);
            $rombel   = Rombel::find($rombelId);
            $semester = Semester::find($semesterId);
            $tahun    = TahunPelajaran::find($tahunId);
        }

        return view('admin.akademik.nilai.rekap', compact(
            'rombelList','semesterList','tahunList','rekap','mapelList',
            'rombel','semester','tahun','rombelId','semesterId','tahunId'
        ));
    }

    public function rekapPdf(Request $request)
    {
        $rombelId   = $request->integer('rombel_id');
        $semesterId = $request->integer('semester_id');
        $tahunId    = $request->integer('tahun_pelajaran_id');

        ['siswa' => $rekap, 'mapel' => $mapelList] = $this->service->getRekapNilai($rombelId, $semesterId, $tahunId);
        $rombel   = Rombel::with('lembaga')->find($rombelId);
        $semester = Semester::find($semesterId);
        $tahun    = TahunPelajaran::find($tahunId);

        $pdf = Pdf::loadView('pdf.akademik.rekap-nilai', compact('rekap','mapelList','rombel','semester','tahun'))
            ->setPaper('a4', 'landscape');

        $filename = 'rekap-nilai-' . ($rombel?->nama ?? 'kelas') . '-' . ($semester?->nama ?? '') . '.pdf';
        return $pdf->download($filename);
    }

    public function rekapExcel(Request $request)
    {
        $rombelId   = $request->integer('rombel_id');
        $semesterId = $request->integer('semester_id');
        $tahunId    = $request->integer('tahun_pelajaran_id');

        ['siswa' => $rekap, 'mapel' => $mapelList] = $this->service->getRekapNilai($rombelId, $semesterId, $tahunId);
        $rombel   = Rombel::with('lembaga')->find($rombelId);
        $semester = Semester::find($semesterId);
        $tahun    = TahunPelajaran::find($tahunId);

        $filename = 'rekap-nilai-' . ($rombel?->nama ?? 'kelas') . '-' . ($semester?->nama ?? '') . '.xlsx';
        return Excel::download(new \App\Exports\RekapNilaiExport($rekap, $mapelList, $rombel, $semester, $tahun), $filename);
    }
}
