<?php

namespace App\Models\Ppdb;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TemplateDokumen extends Model
{
    protected $table = 'template_dokumen';

    protected $fillable = [
        'nama',
        'tipe',
        'file_template',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }
}
