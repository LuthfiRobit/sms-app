<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Models\Master\Rombel;
use App\Models\Master\Semester;
use App\Models\Master\TahunPelajaran;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\PengajuanRaportRepositoryInterface;
use App\Services\Akademik\PengajuanRaportService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PengajuanRaportController extends Controller
{
    public function __construct(
        protected PengajuanRaportRepositoryInterface $repo,
        protected PengajuanRaportService $service,
        protected AkademikSettingRepositoryInterface $settingRepo,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $activeLembagaId = app('active_lembaga_id');

        $lembagaList  = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $rombelList   = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id', 'wali_kelas']);
        $semesterList = Semester::orderBy('nama')->get(['id', 'nama']);
        $tahunList    = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        $this->logActivity->log('Akses Menu Pengajuan Raport', 'Membuka halaman daftar pengajuan raport.');

        return view('admin.akademik.raport.pengajuan.index', compact(
            'lembagaList', 'rombelList', 'semesterList', 'tahunList'
        ));
    }

    public function list(Request $request)
    {
        $activeLembagaId = app('active_lembaga_id');

        $filters = array_filter([
            'lembaga_id'          => $activeLembagaId,
            'rombel_id'           => $request->input('rombel_id'),
            'semester_id'         => $request->input('semester_id'),
            'tahun_pelajaran_id'  => $request->input('tahun_pelajaran_id'),
            'status'              => $request->input('status'),
        ], fn($v) => $v !== null && $v !== '');

        $query = $this->repo->datatable($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('rombel_nama', fn($row) => 'Kelas ' . ($row->rombel->tingkat ?? '') . ' - ' . ($row->rombel->nama ?? '-'))
            ->addColumn('wali_kelas', fn($row) => $row->rombel->wali_kelas ?? '-')
            ->addColumn('semester_nama', fn($row) => $row->semester->nama ?? '-')
            ->addColumn('tahun_nama', fn($row) => $row->tahunPelajaran->nama ?? '-')
            ->addColumn('status_badge', function ($row) {
                $map = [
                    'draft'       => ['secondary', 'Draft'],
                    'diajukan'    => ['primary',   'Diajukan'],
                    'diverifikasi'=> ['info',       'Diverifikasi'],
                    'ditolak'     => ['danger',     'Ditolak'],
                    'disetujui'   => ['success',    'Disetujui'],
                ];
                [$color, $label] = $map[$row->status] ?? ['secondary', ucfirst($row->status)];
                return "<span class=\"badge bg-{$color}\">{$label}</span>";
            })
            ->addColumn('action', function ($row) {
                $id    = $row->id;
                $btns  = "<a href=\"" . route('admin.akademik.raport.pengajuan.show', $id) . "\" class=\"btn btn-sm btn-outline-primary me-1\" title=\"Lihat Detail\"><i class=\"bi bi-eye\"></i> Lihat</a>";

                if (in_array($row->status, ['draft', 'ditolak'])) {
                    if (auth()->user()->hasPermissionTo('admin.akademik.raport.pengajuan.index')) {
                        $btns .= "<button class=\"btn btn-sm btn-success me-1\" onclick=\"submitPengajuan({$id})\" title=\"Ajukan\"><i class=\"bi bi-send\"></i> Submit</button>";
                    }
                }

                if ($row->status === 'diajukan') {
                    if (auth()->user()->hasPermissionTo('admin.akademik.raport.pengajuan.index')) {
                        $btns .= "<button class=\"btn btn-sm btn-warning me-1\" onclick=\"tarikPengajuan({$id})\" title=\"Tarik Kembali\"><i class=\"bi bi-arrow-counterclockwise\"></i> Tarik</button>";
                    }
                }

                if ($row->status === 'draft') {
                    if (auth()->user()->hasPermissionTo('admin.akademik.raport.pengajuan.index')) {
                        $btns .= "<button class=\"btn btn-sm btn-outline-danger\" onclick=\"hapusPengajuan({$id})\" title=\"Hapus\"><i class=\"bi bi-trash\"></i></button>";
                    }
                }

                return $btns;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lembaga_id'         => 'required|integer|exists:lembaga,id',
            'rombel_id'          => 'required|integer|exists:rombel,id',
            'semester_id'        => 'required|integer|exists:semester,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
        ]);

        $data['dibuat_oleh'] = auth()->user()->id_user;

        try {
            $pengajuan = $this->service->create($data);
            return $this->response->success($pengajuan, 'Pengajuan raport berhasil dibuat.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function show(int $id)
    {
        $pengajuan = $this->repo->findById($id);

        if (!$pengajuan) {
            return $this->response->error('Pengajuan tidak ditemukan.', 404);
        }

        $setting = $this->settingRepo->findByLembaga($pengajuan->lembaga_id)
            ?? \App\Models\Akademik\AkademikSetting::default();

        $siswaList    = $this->service->getSiswaList($id);
        $nilaiPerSiswa = $this->service->getNilaiSiswa($id);

        $this->logActivity->log(
            'Akses Detail Pengajuan Raport',
            "Membuka detail pengajuan raport id={$id}."
        );

        return view('admin.akademik.raport.pengajuan.detail', compact(
            'pengajuan', 'setting', 'siswaList', 'nilaiPerSiswa'
        ));
    }

    public function submit(Request $request, int $id)
    {
        $catatan = $request->input('catatan_pengajuan');

        if ($catatan) {
            $this->repo->update($id, ['catatan_pengajuan' => $catatan]);
        }

        try {
            $this->service->submit($id);
            return $this->response->success(null, 'Pengajuan berhasil diajukan ke Kurikulum.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function withdraw(int $id)
    {
        try {
            $this->service->withdraw($id);
            return $this->response->success(null, 'Pengajuan berhasil ditarik kembali.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function refreshNilai(int $id)
    {
        try {
            $this->service->refreshNilai($id);
            return $this->response->success(null, 'Data nilai berhasil diperbarui dari sumber.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function updateNilai(Request $request, int $id)
    {
        $request->validate([
            'peserta_id'                    => 'required|integer|exists:peserta,id',
            'rows'                          => 'required|array|min:1',
            'rows.*.mata_pelajaran_id'      => 'required|integer|exists:mata_pelajaran,id',
            'rows.*.nilai_harian'           => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uts'              => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_uas'              => 'nullable|numeric|min:0|max:100',
            'rows.*.nilai_akhir'            => 'nullable|numeric|min:0|max:100',
            'rows.*.catatan_guru'           => 'nullable|string|max:500',
        ]);

        try {
            $this->service->updateNilaiSiswa($id, $request->integer('peserta_id'), $request->input('rows'));
            return $this->response->success(null, 'Nilai berhasil disimpan.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        $pengajuan = $this->repo->findById($id, []);

        if (!$pengajuan || $pengajuan->status !== 'draft') {
            return $this->response->error('Hanya pengajuan berstatus draft yang dapat dihapus.');
        }

        $this->repo->delete($id);

        $this->logActivity->log('Hapus Pengajuan Raport', "Pengajuan raport id={$id} dihapus.");

        return $this->response->success(null, 'Pengajuan berhasil dihapus.');
    }
}
