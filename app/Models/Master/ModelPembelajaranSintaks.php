<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelPembelajaranSintaks extends Model
{
    protected $table = 'model_pembelajaran_sintaks';

    protected $fillable = ['model_pembelajaran_id', 'meta_fase', 'nama_sintaks', 'urutan'];

    public function modelPembelajaran(): BelongsTo
    {
        return $this->belongsTo(ModelPembelajaran::class);
    }
}
