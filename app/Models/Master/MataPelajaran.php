<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'lembaga_id',
        'kode',
        'nama',
        'kelompok',
        'status',
        'urutan',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function jurusan(): BelongsToMany
    {
        return $this->belongsToMany(Jurusan::class, 'jurusan_mata_pelajaran')
            ->withPivot('urutan')
            ->orderByPivot('urutan');
    }

    public function scopeByLembaga(Builder $query, ?int $lembagaId): Builder
    {
        return $lembagaId
            ? $query->where(fn ($q) => $q->where('lembaga_id', $lembagaId)->orWhereNull('lembaga_id'))
            : $query;
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
