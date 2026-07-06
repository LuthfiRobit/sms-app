<?php

namespace App\Models\Akademik;

use App\Models\Master\ModelPembelajaranSintaks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RppInti extends Model
{
    protected $table = 'rpp_inti';

    protected $fillable = ['rpp_id', 'model_pembelajaran_sintaks_id', 'konten', 'urutan'];

    protected $casts = [
        'konten' => 'array',
    ];

    public function rpp(): BelongsTo
    {
        return $this->belongsTo(Rpp::class);
    }

    public function sintaks(): BelongsTo
    {
        return $this->belongsTo(ModelPembelajaranSintaks::class, 'model_pembelajaran_sintaks_id');
    }
}
