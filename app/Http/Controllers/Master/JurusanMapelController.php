<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Jurusan;
use App\Models\Master\Lembaga;
use App\Models\Master\MataPelajaran;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class JurusanMapelController extends Controller
{
    public function __construct(
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $this->logActivity->log('Akses Menu Mapping Jurusan-Mapel', 'Membuka halaman mapping jurusan dan mata pelajaran.');
        $lembagaId   = app('active_lembaga_id');
        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode']);
        $jurusanList = Jurusan::query()
            ->when($lembagaId, fn ($q) => $q->where('lembaga_id', $lembagaId))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'lembaga_id']);

        return view('admin.master.jurusan-mapel.index', compact('lembagaList', 'jurusanList'));
    }

    public function show(int $jurusanId)
    {
        $jurusan = Jurusan::findOrFail($jurusanId);
        $lembagaId = $jurusan->lembaga_id;

        $allMapel = MataPelajaran::query()
            ->where(fn ($q) => $q->where('lembaga_id', $lembagaId)->orWhereNull('lembaga_id'))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'kelompok']);

        $mappedMapel = $jurusan->mataPelajaran()
            ->get(['mata_pelajaran.id', 'jurusan_mata_pelajaran.urutan']);

        $mappedIds   = $mappedMapel->pluck('id')->toArray();
        $urutanMap   = $mappedMapel->mapWithKeys(fn ($m) => [$m->id => $m->pivot->urutan])->toArray();

        return $this->response->success('', [
            'jurusan'     => $jurusan->only(['id', 'nama', 'kode']),
            'all_mapel'   => $allMapel,
            'mapped_ids'  => $mappedIds,
            'urutan_map'  => $urutanMap,
        ]);
    }

    public function sync(Request $request, int $jurusanId)
    {
        $data = $request->validate([
            'mapel_ids'   => 'nullable|array',
            'mapel_ids.*' => 'integer|exists:mata_pelajaran,id',
            'urutan'      => 'nullable|array',
            'urutan.*'    => 'nullable|integer|min:0',
        ]);

        $jurusan   = Jurusan::findOrFail($jurusanId);
        $mapelIds  = $data['mapel_ids'] ?? [];
        $urutanRaw = $data['urutan'] ?? [];

        $syncData = [];
        foreach ($mapelIds as $mapelId) {
            $syncData[$mapelId] = ['urutan' => (int) ($urutanRaw[$mapelId] ?? 0)];
        }

        $jurusan->mataPelajaran()->sync($syncData);

        $this->logActivity->log(
            'Update Mapping Jurusan-Mapel',
            "Mapping mata pelajaran untuk jurusan '{$jurusan->nama}' diperbarui. Total: " . count($mapelIds) . " mapel."
        );

        return $this->response->success('Mapping berhasil diperbarui.');
    }
}
