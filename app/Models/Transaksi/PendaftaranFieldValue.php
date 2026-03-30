<?php

namespace App\Models\Transaksi;

use App\Models\Ppdb\FormulirField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranFieldValue extends Model
{
    protected $table = 'pendaftaran_field_value';

    protected $fillable = [
        'pendaftaran_id',
        'formulir_field_id',
        'value',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function formulirField(): BelongsTo
    {
        return $this->belongsTo(FormulirField::class, 'formulir_field_id');
    }
}
