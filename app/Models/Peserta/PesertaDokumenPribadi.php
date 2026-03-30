<?php

namespace App\Models\Peserta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaDokumenPribadi extends Model
{
    protected $table = 'peserta_dokumen_pribadi';

    protected $fillable = [
        'peserta_id',
        'no_kip',
        'no_pkh',
        'no_kitas',
        'no_paspor',
    ];

    /**
     * Get the peserta that owns the sensitive doc IDs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }
}
