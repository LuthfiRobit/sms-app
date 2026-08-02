<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Rpp;
use App\Models\Master\Guru;
use App\Models\Master\MataPelajaran;
use App\Models\Master\ProfilSekolah;
use App\Services\Akademik\RppSupervisiService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class RppSupervisiController extends Controller
{
    public function __construct(
        protected RppSupervisiService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Supervisi RPP', 'Membuka halaman supervisi RPP.');

        $activeLembagaId = app('active_lembaga_id');

        $guruList = Guru::when($activeLembagaId, fn ($q) => $q->where('lembaga_id', $activeLembagaId))->orderBy('nama')->get(['id', 'nama']);
        $mapelList = MataPelajaran::byLembaga($activeLembagaId)->aktif()->orderBy('urutan')->get(['id', 'nama']);

        return view('admin.akademik.supervisi-rpp.index', compact('guruList', 'mapelList'));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');

        $query = $this->service->datatable($activeLembagaId)
            ->when($request->guru_id, fn ($q) => $q->whereHas('rpp', fn ($r) => $r->where('guru_id', $request->guru_id)))
            ->when($request->mata_pelajaran_id, fn ($q) => $q->whereHas('rpp', fn ($r) => $r->where('mata_pelajaran_id', $request->mata_pelajaran_id)))
            ->latest('tanggal_supervisi');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn ($s) => $s->rpp?->guru?->nama_lengkap ?? '—')
            ->addColumn('mapel_nama', fn ($s) => $s->rpp?->mataPelajaran?->nama ?? '—')
            ->addColumn('materi_rpp', fn ($s) => $s->rpp?->materi ?? '—')
            ->addColumn('tanggal', fn ($s) => $s->tanggal_supervisi?->format('d/m/Y'))
            ->addColumn('predikat_badges', function ($s) {
                $badge = fn ($label) => match ($label) {
                    'Sangat Baik' => 'success',
                    'Baik' => 'primary',
                    'Cukup' => 'warning text-dark',
                    default => 'danger',
                };
                $html = '';
                foreach (['perencanaan' => 'P', 'pelaksanaan' => 'L', 'asesmen' => 'A'] as $instrumen => $singkatan) {
                    $hasil = $s->hasil($instrumen);
                    $html .= "<span class='badge bg-{$badge($hasil['predikat'])} me-1' title='{$hasil['label']}'>{$singkatan}: {$hasil['predikat']} ({$hasil['persen_capaian']}%)</span>";
                }

                return $html;
            })
            ->addColumn('action', function ($s) {
                $lihat = "<a href='".route('admin.akademik.supervisi-rpp.show', $s->id)."' class='btn btn-xs btn-icon btn-light-info me-1' title='Detail'><i class='bi bi-eye'></i></a>";
                $edit = auth()->user()->hasPermissionTo('admin.akademik.supervisi-rpp.update')
                    ? "<a href='".route('admin.akademik.supervisi-rpp.edit', $s->id)."' class='btn btn-xs btn-icon btn-light-primary me-1' title='Edit'><i class='bi bi-pencil'></i></a>"
                    : '';
                $judul = addslashes($s->rpp?->materi ?? '');
                $hapus = auth()->user()->hasPermissionTo('admin.akademik.supervisi-rpp.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusSupervisi({$s->id},\"{$judul}\")' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $lihat.$edit.$hapus;
            })
            ->rawColumns(['predikat_badges', 'action'])
            ->make(true);
    }

    public function create(int $rppId)
    {
        $rpp = Rpp::with(['guru', 'mataPelajaran', 'tahunPelajaran', 'semester', 'lembaga'])->find($rppId);

        if (! $rpp) {
            abort(404, 'RPP yang akan disupervisi tidak ditemukan.');
        }

        $profilSekolah = ProfilSekolah::first();
        $instrumenList = config('rpp_supervisi.instrumen');

        return view('admin.akademik.supervisi-rpp.form', [
            'rpp' => $rpp,
            'supervisi' => null,
            'profilSekolah' => $profilSekolah,
            'instrumenList' => $instrumenList,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateSupervisi($request, isCreate: true);

        try {
            $supervisi = $this->service->store($data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success($supervisi, 'Supervisi RPP berhasil disimpan.');
    }

    public function show(int $id)
    {
        try {
            $supervisi = $this->service->find($id);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }

        $profilSekolah = ProfilSekolah::first();
        $hasil = [
            'perencanaan' => $supervisi->hasil('perencanaan'),
            'pelaksanaan' => $supervisi->hasil('pelaksanaan'),
            'asesmen' => $supervisi->hasil('asesmen'),
        ];

        return view('admin.akademik.supervisi-rpp.show', compact('supervisi', 'profilSekolah', 'hasil'));
    }

    public function edit(int $id)
    {
        try {
            $supervisi = $this->service->find($id);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }

        $profilSekolah = ProfilSekolah::first();
        $instrumenList = config('rpp_supervisi.instrumen');

        return view('admin.akademik.supervisi-rpp.form', [
            'rpp' => $supervisi->rpp,
            'supervisi' => $supervisi,
            'profilSekolah' => $profilSekolah,
            'instrumenList' => $instrumenList,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validateSupervisi($request, isCreate: false);

        try {
            $supervisi = $this->service->update($id, $data);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success($supervisi, 'Supervisi RPP berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage());
        }

        return $this->response->success(null, 'Supervisi RPP berhasil dihapus.');
    }

    /** Bangun rules statis (header) + dinamis (satu skor per kriteria aktif di config). */
    private function validateSupervisi(Request $request, bool $isCreate): array
    {
        $rules = [
            'tanggal_supervisi' => 'required|date',
            'nama_supervisor' => 'nullable|string|max:150',
            'nip_supervisor' => 'nullable|string|max:30',
            'jabatan_supervisor' => 'nullable|string|max:100',
            'catatan_perencanaan' => 'nullable|string',
            'rtl_perencanaan' => 'nullable|string',
            'catatan_pelaksanaan' => 'nullable|string',
            'rtl_pelaksanaan' => 'nullable|string',
            'catatan_asesmen' => 'nullable|string',
            'rtl_asesmen' => 'nullable|string',
        ];

        if ($isCreate) {
            $rules['rpp_id'] = 'required|exists:rpp,id';
        }

        foreach (config('rpp_supervisi.instrumen') as $instrumen => $config) {
            foreach ($config['kriteria'] as $kriteria) {
                $rules["skor.{$instrumen}.{$kriteria['kode']}.skor"] = 'required|integer|between:0,3';
                $rules["skor.{$instrumen}.{$kriteria['kode']}.catatan"] = 'nullable|string|max:1000';
            }
        }

        return $request->validate($rules);
    }
}
