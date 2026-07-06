<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RppSubmateri extends Model
{
    protected $table = 'rpp_submateri';

    protected $fillable = ['rpp_id', 'teks', 'urutan'];

    public function rpp(): BelongsTo
    {
        return $this->belongsTo(Rpp::class);
    }
}
