<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Models\Master\Rombel;
use App\Models\Master\TahunPelajaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelSiswaController extends Controller
{
    public function __construct(
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Rombel Siswa', 'Membuka halaman manajemen siswa per rombel.');

        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode', 'jenis']);
        $rombelList  = Rombel::byLembaga($activeLembagaId)
            ->aktif()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'lembaga_id', 'tahun_pelajaran_id']);
        $tahunList   = TahunPelajaran::orderByDesc('nama')->get(['id', 'nama', 'status']);

        return view('admin.master.rombel-siswa.index', compact(
            'lembagaList', 'rombelList', 'tahunList'
        ));
    }

    public function getByRombel(int $rombelId)
    {
        $rombel = Rombel::findOrFail($rombelId);

        // Siswa yang sudah terdaftar di rombel ini
        $assigned = DB::table('rombel_siswa')
            ->join('peserta', 'peserta.id', '=', 'rombel_siswa.peserta_id')
            ->where('rombel_siswa.rombel_id', $rombelId)
            ->whereNull('peserta.deleted_at')
            ->select(
                'peserta.id',
                'peserta.nama_lengkap as nama',
                'peserta.nik',
                'peserta.nisn',
                'rombel_siswa.no_absen',
            )
            ->orderBy('rombel_siswa.no_absen')
            ->orderBy('peserta.nama_lengkap')
            ->get();

        // Peserta yang lulus seleksi di lembaga + tahun pelajaran yang sama
        // dan belum masuk ke rombel manapun pada tahun pelajaran tersebut
        $tahunPelajaranId = $rombel->tahun_pelajaran_id;
        $lembagaId        = $rombel->lembaga_id;

        // ID peserta yang sudah punya rombel pada tahun pelajaran ini
        $sudahDiRombel = DB::table('rombel_siswa')
            ->join('rombel', 'rombel.id', '=', 'rombel_siswa.rombel_id')
            ->where('rombel.tahun_pelajaran_id', $tahunPelajaranId)
            ->where('rombel.lembaga_id', $lembagaId)
            ->pluck('rombel_siswa.peserta_id');

        $available = DB::table('peserta')
            ->join('pendaftaran', 'pendaftaran.peserta_id', '=', 'peserta.id')
            ->join('hasil_seleksi', 'hasil_seleksi.pendaftaran_id', '=', 'pendaftaran.id')
            ->where('pendaftaran.lembaga_id', $lembagaId)
            ->where('pendaftaran.tahun_pelajaran_id', $tahunPelajaranId)
            ->where('hasil_seleksi.status_kelulusan', 'lulus')
            ->whereNull('peserta.deleted_at')
            ->whereNull('pendaftaran.deleted_at')
            ->whereNotIn('peserta.id', $sudahDiRombel)
            ->select(
                'peserta.id',
                'peserta.nama_lengkap as nama',
                'peserta.nik',
                'peserta.nisn',
            )
            ->distinct()
            ->orderBy('peserta.nama_lengkap')
            ->get();

        return $this->response->success([
            'assigned'  => $assigned,
            'available' => $available,
            'rombel'    => [
                'id'    => $rombel->id,
                'nama'  => $rombel->nama,
                'tingkat' => $rombel->tingkat,
            ],
        ], 'OK');
    }

    public function assign(Request $request, int $rombelId)
    {
        $request->validate([
            'peserta_ids'   => 'required|array|min:1',
            'peserta_ids.*' => 'required|integer|exists:peserta,id',
        ]);

        $rombel = Rombel::findOrFail($rombelId);

        $rows = collect($request->peserta_ids)->map(fn($pid) => [
            'rombel_id'  => $rombelId,
            'peserta_id' => $pid,
            'no_absen'   => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('rombel_siswa')->upsert(
            $rows,
            ['rombel_id', 'peserta_id'],
            ['updated_at']
        );

        $count = count($request->peserta_ids);
        $this->logActivity->log(
            'Tambah Siswa ke Rombel',
            "{$count} siswa ditambahkan ke rombel '{$rombel->nama}'."
        );

        return $this->response->success("Berhasil menambahkan {$count} siswa ke rombel.");
    }

    public function unassign(Request $request, int $rombelId)
    {
        $request->validate([
            'peserta_id' => 'required|integer|exists:peserta,id',
        ]);

        $rombel = Rombel::findOrFail($rombelId);

        DB::table('rombel_siswa')
            ->where('rombel_id', $rombelId)
            ->where('peserta_id', $request->peserta_id)
            ->delete();

        $this->logActivity->log(
            'Keluarkan Siswa dari Rombel',
            "Peserta ID {$request->peserta_id} dikeluarkan dari rombel '{$rombel->nama}'."
        );

        return $this->response->success('Siswa berhasil dikeluarkan dari rombel.');
    }

    public function updateNoAbsen(Request $request, int $rombelId)
    {
        $request->validate([
            'data'              => 'required|array|min:1',
            'data.*.peserta_id' => 'required|integer|exists:peserta,id',
            'data.*.no_absen'   => 'nullable|string|max:5',
        ]);

        $rombel = Rombel::findOrFail($rombelId);

        foreach ($request->data as $item) {
            DB::table('rombel_siswa')
                ->where('rombel_id', $rombelId)
                ->where('peserta_id', $item['peserta_id'])
                ->update([
                    'no_absen'   => $item['no_absen'] ?? null,
                    'updated_at' => now(),
                ]);
        }

        $this->logActivity->log(
            'Update No Absen Rombel',
            "Nomor absen siswa di rombel '{$rombel->nama}' diperbarui."
        );

        return $this->response->success('Nomor absen berhasil diperbarui.');
    }
}
