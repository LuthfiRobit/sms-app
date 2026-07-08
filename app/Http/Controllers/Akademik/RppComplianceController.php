<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Rpp;
use App\Models\Master\JadwalKbm;
use App\Services\LogActivityService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/**
 * Laporan read-only: kombinasi guru+mapel+tahun yang punya jadwal KBM tapi
 * belum punya RPP berstatus disetujui. Sumber data hanya JadwalKbm+Rpp,
 * jadi datatable dibangun dari Collection PHP (bukan query builder) karena
 * grouping "distinct kombinasi vs approved-set" tidak natural sebagai SQL join.
 */
class RppComplianceController extends Controller
{
    public function __construct(protected LogActivityService $logActivity) {}

    public function index()
    {
        $this->logActivity->log('Akses Laporan Kepatuhan RPP', 'Membuka laporan kepatuhan RPP.');

        return view('admin.akademik.rpp-compliance.index');
    }

    public function list(Request $request)
    {
        $lid = app('active_lembaga_id');

        $jadwal = JadwalKbm::when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->with(['guru:id,nama,gelar_depan,gelar_belakang', 'mataPelajaran:id,nama', 'tahunPelajaran:id,nama', 'rombel:id,nama'])
            ->get();

        $rppDisetujuiSet = Rpp::where('status', 'disetujui')
            ->when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->get(['guru_id', 'mata_pelajaran_id', 'tahun_pelajaran_id'])
            ->map(fn ($r) => "{$r->guru_id}.{$r->mata_pelajaran_id}.{$r->tahun_pelajaran_id}")
            ->flip();

        $belum = $jadwal
            ->groupBy(fn ($j) => "{$j->guru_id}.{$j->mata_pelajaran_id}.{$j->tahun_pelajaran_id}")
            ->reject(fn ($grup, $kunci) => $rppDisetujuiSet->has($kunci))
            ->map(function ($grup) {
                $satu = $grup->first();

                return [
                    'guru_nama' => $satu->guru?->nama_lengkap ?? '—',
                    'mapel_nama' => $satu->mataPelajaran?->nama ?? '—',
                    'tahun_nama' => $satu->tahunPelajaran?->nama ?? '—',
                    'rombel_terdampak' => $grup->pluck('rombel.nama')->filter()->unique()->values()->implode(', '),
                ];
            })
            ->values();

        return DataTables::of($belum)
            ->addIndexColumn()
            ->make(true);
    }
}
