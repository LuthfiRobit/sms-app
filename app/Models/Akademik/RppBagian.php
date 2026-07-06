<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RppBagian extends Model
{
    protected $table = 'rpp_bagian';

    protected $fillable = ['nama', 'urutan', 'status'];

    public function poin(): HasMany
    {
        return $this->hasMany(RppPoin::class)->orderBy('urutan');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
