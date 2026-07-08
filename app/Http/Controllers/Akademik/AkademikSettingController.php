<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Services\Akademik\AkademikSettingService;
use App\Services\LogActivityService;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Request;

class AkademikSettingController extends Controller
{
    public function __construct(
        protected AkademikSettingRepositoryInterface $repo,
        protected AkademikSettingService $service,
        protected ResponseService $response,
        protected LogActivityService $logActivity,
    ) {}

    public function index()
    {
        $activeLembagaId = app('active_lembaga_id');

        $lembagaList = Lembaga::orderBy('urutan')->get(['id', 'nama', 'kode']);

        $settings = [];
        foreach ($lembagaList as $l) {
            $settings[$l->id] = $this->service->get($l->id);
        }

        $activeSetting = $activeLembagaId
            ? $this->service->get($activeLembagaId)
            : ($settings[array_key_first($settings)] ?? null);

        $this->logActivity->log(
            'Akses Setting Akademik',
            'Membuka halaman konfigurasi setting akademik.'
        );

        return view('admin.akademik.setting.index', compact(
            'lembagaList',
            'settings',
            'activeSetting'
        ));
    }

    public function update(Request $request, int $lembagaId)
    {
        $data = $request->validate([
            'bobot_harian'       => 'required|numeric|min:1|max:98',
            'bobot_uts'          => 'required|numeric|min:1|max:98',
            'bobot_uas'          => 'required|numeric|min:1|max:98',
            'kkm_default'        => 'required|integer|min:0|max:100',
            'allow_manual_nilai' => 'boolean',
            'alpa_beruntun_threshold' => 'required|integer|min:2|max:10',
        ]);

        $data['allow_manual_nilai'] = $request->boolean('allow_manual_nilai');

        try {
            $setting = $this->service->save($lembagaId, $data);

            return $this->response->success($setting, 'Setting berhasil disimpan.');
        } catch (Exception $e) {
            return $this->response->error($e->getMessage());
        }
    }
}
