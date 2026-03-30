<?php

namespace App\Models\Master;

use App\Models\Ppdb\KuotaJurusan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jurusan';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'status',
        'urutan',
    ];

    public function kuotaJurusan(): HasMany
    {
        return $this->hasMany(KuotaJurusan::class, 'jurusan_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
