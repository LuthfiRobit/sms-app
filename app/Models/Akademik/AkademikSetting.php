<?php

namespace App\Models\Akademik;

use App\Models\Master\Lembaga;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikSetting extends Model
{
    protected $table = 'akademik_setting';

    protected $fillable = [
        'lembaga_id',
        'allow_manual_nilai',
        'bobot_harian',
        'bobot_uts',
        'bobot_uas',
        'kkm_default',
        'alpa_beruntun_threshold',
    ];

    protected $casts = [
        'allow_manual_nilai' => 'boolean',
        'bobot_harian'       => 'float',
        'bobot_uts'          => 'float',
        'bobot_uas'          => 'float',
        'kkm_default'        => 'integer',
        'alpa_beruntun_threshold' => 'integer',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public static function default(): self
    {
        $instance = new self();
        $instance->allow_manual_nilai = false;
        $instance->bobot_harian       = 40.00;
        $instance->bobot_uts          = 30.00;
        $instance->bobot_uas          = 30.00;
        $instance->kkm_default        = 70;
        $instance->alpa_beruntun_threshold = 3;

        return $instance;
    }
}
