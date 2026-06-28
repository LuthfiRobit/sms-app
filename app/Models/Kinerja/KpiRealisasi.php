<?php

namespace App\Models\Kinerja;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiRealisasi extends Model
{
    protected $table = 'kpi_realisasi';

    protected $fillable = [
        'kpi_indikator_id',
        'periode',
        'nilai_realisasi',
        'catatan',
        'dicatat_oleh',
        'dicatat_at',
    ];

    protected function casts(): array
    {
        return [
            'nilai_realisasi' => 'float',
            'dicatat_at'      => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function kpiIndikator(): BelongsTo
    {
        return $this->belongsTo(KpiIndikator::class, 'kpi_indikator_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh', 'id_user');
    }
}
