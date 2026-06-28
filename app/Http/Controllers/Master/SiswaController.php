<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Master\Lembaga;
use App\Models\Master\Rombel;
use App\Models\Master\TahunPelajaran;

class SiswaController extends Controller
{
    public function __construct(
        private readonly ResponseService $response,
        private readonly LogActivityService $log,
    ) {}

    public function index()
    {
        $lembagaList  = Lembaga::orderBy('urutan')->get(['id', 'kode', 'nama']);
        $tahunList    = TahunPelajaran::orderBy('nama', 'desc')->get(['id', 'nama', 'status']);
        $activeLembagaId = app('active_lembaga_id');
        $activeTahun  = TahunPelajaran::where('status', 'aktif')->first();

        $rombelList = Rombel::when($activeLembagaId, fn($q) => $q->where('lembaga_id', $activeLembagaId))
            ->when($activeTahun, fn($q) => $q->where('tahun_pelajaran_id', $activeTahun->id))
            ->orderBy('tingkat')->orderBy('nama')
            ->get(['id', 'lembaga_id', 'tahun_pelajaran_id', 'nama', 'tingkat']);

        $this->log->log('Akses Master Siswa', 'Melihat daftar siswa aktif');

        return view('admin.master.siswa.index', compact(
            'lembagaList', 'tahunList', 'rombelList', 'activeLembagaId', 'activeTahun'
        ));
    }

    public function list(Request $request)
    {
        $lembagaId = $request->input('lembaga_id', app('active_lembaga_id'));
        $tahunId   = $request->input('tahun_pelajaran_id');
        $rombelId  = $request->input('rombel_id');
        $tingkat   = $request->input('tingkat');

        $query = DB::table('peserta')
            ->join('pendaftaran', function ($join) use ($lembagaId) {
                $join->on('pendaftaran.peserta_id', '=', 'peserta.id')
                    ->where('pendaftaran.status', 'siswa_tetap')
                    ->whereNull('pendaftaran.deleted_at');
                if ($lembagaId) {
                    $join->where('pendaftaran.lembaga_id', $lembagaId);
                }
            })
            ->leftJoin('rombel_siswa', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->leftJoin('rombel', function ($join) use ($tahunId) {
                $join->on('rombel.id', '=', 'rombel_siswa.rombel_id');
                if ($tahunId) {
                    $join->where('rombel.tahun_pelajaran_id', $tahunId);
                }
            })
            ->leftJoin('tahun_pelajaran', 'tahun_pelajaran.id', '=', 'pendaftaran.tahun_pelajaran_id')
            ->whereNull('peserta.deleted_at')
            ->when($tahunId, fn($q) => $q->where('pendaftaran.tahun_pelajaran_id', $tahunId))
            ->when($rombelId, fn($q) => $q->where('rombel_siswa.rombel_id', $rombelId))
            ->when($tingkat, fn($q) => $q->where('rombel.tingkat', $tingkat))
            ->select([
                'peserta.id',
                'peserta.nama_lengkap',
                'peserta.nisn',
                'peserta.jenis_kelamin',
                'peserta.tempat_lahir',
                'peserta.tanggal_lahir',
                'rombel_siswa.no_absen',
                'rombel.id as rombel_id',
                'rombel.nama as rombel_nama',
                'rombel.tingkat',
                'rombel.wali_kelas',
                'tahun_pelajaran.nama as tahun_nama',
                'pendaftaran.no_pendaftaran',
                'pendaftaran.tanggal_daftar',
            ])
            ->distinct();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $detail = route('admin.peserta.show', $row->id);
                $btn  = '<a href="' . $detail . '" class="btn btn-xs btn-icon btn-light-primary" title="Lihat Profil Lengkap" target="_blank"><i class="bi bi-person-lines-fill"></i></a>';
                return $btn;
            })
            ->addColumn('nama_display', function ($row) {
                $badge = $row->jenis_kelamin === 'L'
                    ? '<span class="badge bg-light-primary text-primary">L</span>'
                    : '<span class="badge bg-light-danger text-danger">P</span>';
                return $badge . ' ' . e($row->nama_lengkap);
            })
            ->addColumn('kelas_display', function ($row) {
                if (!$row->rombel_nama) {
                    return '<span class="badge bg-secondary">Belum di Kelas</span>';
                }
                $noAbsen = $row->no_absen ? '<small class="text-muted ms-1">#' . $row->no_absen . '</small>' : '';
                return '<span class="badge bg-light-success text-success">Kelas ' . $row->tingkat . ' - ' . e($row->rombel_nama) . '</span>' . $noAbsen;
            })
            ->addColumn('wali_kelas_display', fn($row) => $row->wali_kelas ? e($row->wali_kelas) : '<span class="text-muted">—</span>')
            ->rawColumns(['action', 'nama_display', 'kelas_display', 'wali_kelas_display'])
            ->make(true);
    }

    public function stats(Request $request)
    {
        $lembagaId = $request->input('lembaga_id', app('active_lembaga_id'));
        $tahunId   = $request->input('tahun_pelajaran_id');

        $base = DB::table('peserta')
            ->join('pendaftaran', function ($join) use ($lembagaId) {
                $join->on('pendaftaran.peserta_id', '=', 'peserta.id')
                    ->where('pendaftaran.status', 'siswa_tetap')
                    ->whereNull('pendaftaran.deleted_at');
                if ($lembagaId) $join->where('pendaftaran.lembaga_id', $lembagaId);
            })
            ->whereNull('peserta.deleted_at')
            ->when($tahunId, fn($q) => $q->where('pendaftaran.tahun_pelajaran_id', $tahunId));

        $total   = (clone $base)->distinct('peserta.id')->count('peserta.id');
        $lakiLaki  = (clone $base)->where('peserta.jenis_kelamin', 'L')->distinct('peserta.id')->count('peserta.id');
        $perempuan = (clone $base)->where('peserta.jenis_kelamin', 'P')->distinct('peserta.id')->count('peserta.id');

        // Per tingkat
        $perTingkat = DB::table('peserta')
            ->join('pendaftaran', function ($join) use ($lembagaId) {
                $join->on('pendaftaran.peserta_id', '=', 'peserta.id')
                    ->where('pendaftaran.status', 'siswa_tetap')
                    ->whereNull('pendaftaran.deleted_at');
                if ($lembagaId) $join->where('pendaftaran.lembaga_id', $lembagaId);
            })
            ->leftJoin('rombel_siswa', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->leftJoin('rombel', function ($join) use ($tahunId) {
                $join->on('rombel.id', '=', 'rombel_siswa.rombel_id');
                if ($tahunId) $join->where('rombel.tahun_pelajaran_id', $tahunId);
            })
            ->whereNull('peserta.deleted_at')
            ->when($tahunId, fn($q) => $q->where('pendaftaran.tahun_pelajaran_id', $tahunId))
            ->selectRaw('rombel.tingkat, COUNT(DISTINCT peserta.id) as jumlah')
            ->groupBy('rombel.tingkat')
            ->orderBy('rombel.tingkat')
            ->get();

        // Belum di kelas
        $belumKelas = DB::table('peserta')
            ->join('pendaftaran', function ($join) use ($lembagaId) {
                $join->on('pendaftaran.peserta_id', '=', 'peserta.id')
                    ->where('pendaftaran.status', 'siswa_tetap')
                    ->whereNull('pendaftaran.deleted_at');
                if ($lembagaId) $join->where('pendaftaran.lembaga_id', $lembagaId);
            })
            ->leftJoin('rombel_siswa', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->whereNull('peserta.deleted_at')
            ->when($tahunId, fn($q) => $q->where('pendaftaran.tahun_pelajaran_id', $tahunId))
            ->whereNull('rombel_siswa.rombel_id')
            ->distinct('peserta.id')->count('peserta.id');

        return $this->response->success(compact('total', 'lakiLaki', 'perempuan', 'perTingkat', 'belumKelas'), 'OK');
    }

    public function exportCsv(Request $request)
    {
        $lembagaId = $request->input('lembaga_id', app('active_lembaga_id'));
        $tahunId   = $request->input('tahun_pelajaran_id');
        $rombelId  = $request->input('rombel_id');

        $data = DB::table('peserta')
            ->join('pendaftaran', function ($join) use ($lembagaId) {
                $join->on('pendaftaran.peserta_id', '=', 'peserta.id')
                    ->where('pendaftaran.status', 'siswa_tetap')
                    ->whereNull('pendaftaran.deleted_at');
                if ($lembagaId) $join->where('pendaftaran.lembaga_id', $lembagaId);
            })
            ->leftJoin('rombel_siswa', 'rombel_siswa.peserta_id', '=', 'peserta.id')
            ->leftJoin('rombel', function ($join) use ($tahunId) {
                $join->on('rombel.id', '=', 'rombel_siswa.rombel_id');
                if ($tahunId) $join->where('rombel.tahun_pelajaran_id', $tahunId);
            })
            ->leftJoin('tahun_pelajaran', 'tahun_pelajaran.id', '=', 'pendaftaran.tahun_pelajaran_id')
            ->whereNull('peserta.deleted_at')
            ->when($tahunId, fn($q) => $q->where('pendaftaran.tahun_pelajaran_id', $tahunId))
            ->when($rombelId, fn($q) => $q->where('rombel_siswa.rombel_id', $rombelId))
            ->select([
                'peserta.nisn',
                'peserta.nik',
                'peserta.nama_lengkap',
                'peserta.jenis_kelamin',
                'peserta.tempat_lahir',
                'peserta.tanggal_lahir',
                'rombel_siswa.no_absen',
                'rombel.tingkat',
                'rombel.nama as kelas',
                'rombel.wali_kelas',
                'tahun_pelajaran.nama as tahun_pelajaran',
                'pendaftaran.no_pendaftaran',
            ])
            ->distinct()
            ->orderBy('rombel.tingkat')
            ->orderBy('rombel.nama')
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->get();

        $this->log->log('Export Siswa CSV', 'Export data siswa aktif');

        $filename = 'Data-Siswa-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // BOM untuk Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['No Absen', 'NISN', 'NIK', 'Nama Lengkap', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Kelas', 'Wali Kelas', 'Tahun Pelajaran', 'No Pendaftaran']);
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->no_absen ?? '',
                    $row->nisn ?? '',
                    $row->nik ?? '',
                    $row->nama_lengkap,
                    $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                    $row->tempat_lahir ?? '',
                    $row->tanggal_lahir ?? '',
                    $row->tingkat && $row->kelas ? 'Kelas ' . $row->tingkat . ' - ' . $row->kelas : 'Belum di Kelas',
                    $row->wali_kelas ?? '',
                    $row->tahun_pelajaran ?? '',
                    $row->no_pendaftaran ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
