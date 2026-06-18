<?php

namespace App\Models\Master;

use App\Models\Ppdb\KuotaJurusan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jurusan';

    protected $fillable = [
        'lembaga_id',
        'kode',
        'nama',
        'deskripsi',
        'status',
        'urutan',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function scopeByLembaga(Builder $query, ?int $lembagaId): Builder
    {
        return $lembagaId ? $query->where('lembaga_id', $lembagaId) : $query;
    }

    public function kuotaJurusan(): HasMany
    {
        return $this->hasMany(KuotaJurusan::class, 'jurusan_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
