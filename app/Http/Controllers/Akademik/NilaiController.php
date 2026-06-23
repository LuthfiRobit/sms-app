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
use Illuminate\Http\Request;

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
}
