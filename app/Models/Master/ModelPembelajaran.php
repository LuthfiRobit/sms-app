<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelPembelajaran extends Model
{
    protected $table = 'model_pembelajaran';

    protected $fillable = ['nama', 'deskripsi', 'urutan', 'status'];

    public function sintaks(): HasMany
    {
        return $this->hasMany(ModelPembelajaranSintaks::class)->orderBy('urutan');
    }

    /** Sintaks dikelompokkan per fase (Memahami/Mengaplikasi/Merefleksi) untuk form & PDF. */
    public function sintaksByFase(): array
    {
        return $this->sintaks->groupBy('meta_fase')->all();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
