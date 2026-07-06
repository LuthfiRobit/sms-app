<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RppPoinValue extends Model
{
    protected $table = 'rpp_poin_value';

    protected $fillable = ['rpp_id', 'rpp_poin_id', 'value_teks', 'value_json'];

    protected $casts = [
        'value_json' => 'array',
    ];

    public function rpp(): BelongsTo
    {
        return $this->belongsTo(Rpp::class);
    }

    public function poin(): BelongsTo
    {
        return $this->belongsTo(RppPoin::class, 'rpp_poin_id');
    }
}
