<?php

namespace App\Http\Controllers\Akademik;

use App\Exports\RekapAbsensiGuruExport;
use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Services\Akademik\AbsensiGuruService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class AbsensiGuruController extends Controller
{
    public function __construct(
        protected AbsensiGuruService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Absensi Guru', 'Membuka halaman rekap absensi guru.');

        $activeLembagaId = app('active_lembaga_id');
        $user = auth()->user();

        // Non-super-admin hanya boleh melihat opsi lembaga miliknya sendiri —
        // dropdown filter tidak boleh menawarkan lembaga di luar aksesnya.
        $lembagaList = Lembaga::orderBy('urutan')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->whereIn('id', $user->getLembagaIds()))
            ->get(['id', 'nama', 'kode']);

        // Super admin: muat semua guru — filter lembaga di sisi klien (data-lembaga)
        // yang menyaring tampilan saat dropdown Lembaga diganti.
        // Non-super-admin: dikunci ke lembaganya sendiri saja, sesuai dropdown yang di-disable.
        $guruList = Guru::when(! $user->isSuperAdmin(), fn ($q) => $q->where('lembaga_id', $activeLembagaId))
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'lembaga_id']);

        return view('admin.akademik.absensi-guru.index', [
            'lembagaList' => $lembagaList,
            'guruList' => $guruList,
            'activeLembagaId' => $activeLembagaId,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function list(Request $request)
    {
        $filters = [
            'lembaga_id' => $this->resolveLembagaId($request),
            'guru_id' => $request->integer('guru_id') ?: null,
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_akhir' => $request->input('tanggal_akhir'),
            'status' => $request->input('status'),
        ];

        $query = $this->service->datatable(null, $filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('guru_nama', fn ($r) => $r->guru?->nama_lengkap ?? '—')
            ->addColumn('lembaga_nama', fn ($r) => $r->lembaga?->nama ?? '—')
            ->addColumn('tanggal_fmt', fn ($r) => $r->tanggal?->format('d/m/Y') ?? '—')
            ->addColumn('jam_masuk_fmt', fn ($r) => $r->jam_masuk ? substr($r->jam_masuk, 0, 5) : '—')
            ->addColumn('jam_pulang_fmt', fn ($r) => $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '—')
            ->addColumn('status_badge', function ($r) {
                $map = [
                    'hadir' => 'success',
                    'terlambat' => 'warning text-dark',
                    'izin' => 'info',
                    'sakit' => 'info',
                    'alpa' => 'danger',
                ];
                $color = $map[$r->status] ?? 'secondary';

                return "<span class='badge bg-{$color}'>".ucfirst($r->status).'</span>';
            })
            ->addColumn('mock_flag', fn ($r) => $r->flag_mock_location
                ? "<span class='badge bg-danger' title='Lokasi palsu terdeteksi'><i class='bi bi-exclamation-triangle'></i></span>"
                : '')
            ->addColumn('koreksi_badge', fn ($r) => $r->is_koreksi_manual
                ? "<span class='badge bg-light-warning text-warning' title='Dikoreksi oleh {$r->dikoreksiOleh?->name}'>Dikoreksi</span>"
                : '')
            ->addColumn('action', function ($r) {
                $detail = "<button class='btn btn-xs btn-icon btn-light-info me-1' onclick='lihatDetailAbsensiGuru({$r->id})' title='Detail'><i class='bi bi-eye'></i></button>";
                $edit = auth()->user()->hasPermissionTo('admin.akademik.absensi-guru.update')
                    ? "<button class='btn btn-xs btn-icon btn-light-primary me-1' onclick='editAbsensiGuru({$r->id})' title='Edit'><i class='bi bi-pencil'></i></button>"
                    : '';
                $del = auth()->user()->hasPermissionTo('admin.akademik.absensi-guru.destroy')
                    ? "<button class='btn btn-xs btn-icon btn-light-danger' onclick='hapusAbsensiGuru({$r->id})' title='Hapus'><i class='bi bi-trash'></i></button>"
                    : '';

                return $detail.$edit.$del;
            })
            ->rawColumns(['status_badge', 'mock_flag', 'koreksi_badge', 'action'])
            ->make(true);
    }

    public function detail(int $id)
    {
        try {
            $absensi = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        if (! $this->bolehAksesLembaga($absensi->lembaga_id)) {
            return $this->response->error('Anda tidak memiliki akses ke data lembaga ini.', 403);
        }

        return $this->response->success($absensi, 'OK');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'lembaga_id' => 'required|exists:lembaga,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if (! $this->bolehAksesLembaga((int) $data['lembaga_id'])) {
            return $this->response->error('Anda tidak memiliki akses ke lembaga ini.', 403);
        }

        try {
            $absensi = $this->service->koreksiManual($data, auth()->id());
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        $this->logActivity->log('Koreksi Manual Absensi Guru', "Tambah absensi guru #{$absensi->id} secara manual oleh admin.");

        return $this->response->success($absensi, 'Absensi guru berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        try {
            $absensi = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        if (! $this->bolehAksesLembaga($absensi->lembaga_id)) {
            return $this->response->error('Anda tidak memiliki akses ke data lembaga ini.', 403);
        }

        // guru_id/tanggal (kunci unique) sengaja tidak boleh diubah lewat form edit —
        // kalau perlu pindah tanggal/guru, hapus lalu buat baru.
        $data = $request->validate([
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $absensi = $this->service->koreksiManual($data, auth()->id(), $id);
        $this->logActivity->log('Koreksi Manual Absensi Guru', "Ubah absensi guru #{$id} secara manual oleh admin.");

        return $this->response->success($absensi, 'Absensi guru berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        try {
            $absensi = $this->service->find($id);
        } catch (RuntimeException $e) {
            return $this->response->error($e->getMessage(), 404);
        }

        if (! $this->bolehAksesLembaga($absensi->lembaga_id)) {
            return $this->response->error('Anda tidak memiliki akses ke data lembaga ini.', 403);
        }

        $this->service->destroy($id);
        $this->logActivity->log('Hapus Absensi Guru', "Hapus absensi guru #{$id} oleh admin.");

        return $this->response->success(null, 'Absensi guru berhasil dihapus.');
    }

    public function rekapPdf(Request $request)
    {
        $filters = [
            'lembaga_id' => $this->resolveLembagaId($request),
            'guru_id' => $request->integer('guru_id') ?: null,
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_akhir' => $request->input('tanggal_akhir'),
            'status' => $request->input('status'),
        ];

        $rows = $this->service->datatable(null, $filters)->get();
        $lembaga = $filters['lembaga_id'] ? Lembaga::find($filters['lembaga_id']) : null;

        $this->logActivity->log('Cetak PDF Absensi Guru', 'Cetak rekap absensi guru ke PDF.');

        $pdf = Pdf::loadView('pdf.akademik.rekap-absensi-guru', compact('rows', 'lembaga', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi-guru-'.now()->format('Ymd-His').'.pdf');
    }

    public function export(Request $request)
    {
        $filters = [
            'lembaga_id' => $this->resolveLembagaId($request),
            'guru_id' => $request->integer('guru_id') ?: null,
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_akhir' => $request->input('tanggal_akhir'),
            'status' => $request->input('status'),
        ];

        $rows = $this->service->datatable(null, $filters)->get();

        $this->logActivity->log('Export Absensi Guru', 'Export rekap absensi guru ke Excel.');

        $filename = 'rekap-absensi-guru-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new RekapAbsensiGuruExport($rows), $filename);
    }

    /**
     * Tentukan lembaga_id yang benar-benar dipakai untuk query — TIDAK
     * mempercayai begitu saja lembaga_id dari request. Non-super-admin hanya
     * boleh memfilter ke lembaga miliknya sendiri; permintaan di luar itu
     * diabaikan dan jatuh kembali ke lembaga aktif di sesi.
     */
    private function resolveLembagaId(Request $request): ?int
    {
        $requested = $request->integer('lembaga_id') ?: null;

        if (! $requested) {
            return app('active_lembaga_id');
        }

        return $this->bolehAksesLembaga($requested) ? $requested : app('active_lembaga_id');
    }

    private function bolehAksesLembaga(?int $lembagaId): bool
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $lembagaId && in_array($lembagaId, $user->getLembagaIds(), true);
    }
}
