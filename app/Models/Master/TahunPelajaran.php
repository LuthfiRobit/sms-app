<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_pelajaran';

    protected $fillable = [
        'kode_tahun',
        'nama',
        'mulai',
        'selesai',
        'status',
    ];

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'tahun_pelajaran_id');
    }
}
