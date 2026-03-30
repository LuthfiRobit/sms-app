<?php

namespace App\Models\Ppdb;

use App\Models\Transaksi\PendaftaranFieldValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormulirField extends Model
{
    protected $table = 'formulir_field';

    protected $fillable = [
        'formulir_pendaftaran_id',
        'kode_field',
        'label',
        'tipe_field',
        'is_required',
        'is_statis',
        'dapodik_key',
        'urutan',
        'opsi',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_statis' => 'boolean',
        'opsi' => 'array',
    ];

    public function formulirPendaftaran(): BelongsTo
    {
        return $this->belongsTo(FormulirPendaftaran::class, 'formulir_pendaftaran_id');
    }

    public function pendaftaranFieldValue(): HasMany
    {
        return $this->hasMany(PendaftaranFieldValue::class, 'formulir_field_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('urutan', 'asc');
        });
    }
}
