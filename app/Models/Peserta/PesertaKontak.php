<?php

namespace App\Models\Peserta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaKontak extends Model
{
    protected $table = 'peserta_kontak';

    protected $fillable = [
        'peserta_id',
        'no_hp',
        'email',
    ];

    /**
     * Get the peserta that owns the contact info.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }
}
