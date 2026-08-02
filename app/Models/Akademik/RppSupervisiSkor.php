<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RppSupervisiSkor extends Model
{
    protected $table = 'rpp_supervisi_skor';

    protected $fillable = ['rpp_supervisi_id', 'instrumen', 'kode', 'skor', 'catatan'];

    protected $casts = [
        'skor' => 'integer',
    ];

    public function rppSupervisi(): BelongsTo
    {
        return $this->belongsTo(RppSupervisi::class);
    }
}
