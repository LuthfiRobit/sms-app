<?php

namespace App\Services\Akademik;

use App\Models\Akademik\AkademikSetting;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Services\LogActivityService;
use InvalidArgumentException;

class AkademikSettingService
{
    public function __construct(
        protected AkademikSettingRepositoryInterface $repo,
        protected LogActivityService $logActivity,
    ) {}

    /**
     * Get the setting for a lembaga. Returns a default in-memory object if none exists yet.
     */
    public function get(int $lembagaId): object
    {
        $setting = $this->repo->findByLembaga($lembagaId);

        if ($setting) {
            return $setting;
        }

        // Return a default transient object (not persisted)
        $default = new AkademikSetting();
        $default->lembaga_id          = $lembagaId;
        $default->allow_manual_nilai   = false;
        $default->bobot_harian        = 40.00;
        $default->bobot_uts           = 30.00;
        $default->bobot_uas           = 30.00;
        $default->kkm_default         = 70;

        return $default;
    }

    /**
     * Save (create or update) the setting for a lembaga.
     * The three bobot values must sum to exactly 100.
     */
    public function save(int $lembagaId, array $data): object
    {
        $bobotHarian = (float) ($data['bobot_harian'] ?? 0);
        $bobotUts    = (float) ($data['bobot_uts']    ?? 0);
        $bobotUas    = (float) ($data['bobot_uas']    ?? 0);

        $total = round($bobotHarian + $bobotUts + $bobotUas, 2);

        if ($total !== 100.00) {
            throw new InvalidArgumentException(
                "Total bobot penilaian harus 100. Saat ini: {$total}."
            );
        }

        $setting = $this->repo->upsert($lembagaId, [
            'allow_manual_nilai' => (bool) ($data['allow_manual_nilai'] ?? false),
            'bobot_harian'       => $bobotHarian,
            'bobot_uts'          => $bobotUts,
            'bobot_uas'          => $bobotUas,
            'kkm_default'        => (int) ($data['kkm_default'] ?? 70),
        ]);

        $this->logActivity->log(
            'Simpan Akademik Setting',
            "Setting akademik lembaga_id={$lembagaId} diperbarui. Bobot: {$bobotHarian}/{$bobotUts}/{$bobotUas}, KKM: {$setting->kkm_default}."
        );

        return $setting;
    }

    /**
     * Calculate nilai akhir from three component scores using the lembaga's bobot configuration.
     */
    public function calculateNilaiAkhir(float $h, float $u, float $a, object $setting): float
    {
        return round(
            ($h * ($setting->bobot_harian / 100))
            + ($u * ($setting->bobot_uts / 100))
            + ($a * ($setting->bobot_uas / 100)),
            2
        );
    }
}
