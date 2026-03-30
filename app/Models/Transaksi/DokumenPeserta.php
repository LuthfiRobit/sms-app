<?php

namespace App\Models\Transaksi;

use App\Models\Ppdb\SyaratPendaftaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DokumenPeserta extends Model
{
    protected $table = 'dokumen_peserta';

    protected $fillable = [
        'pendaftaran_id',
        'syarat_pendaftaran_id',
        'nama_file',
        'path_file',
        'mime_type',
        'ukuran_file',
        'status_verifikasi',
        'keterangan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function syaratPendaftaran(): BelongsTo
    {
        return $this->belongsTo(SyaratPendaftaran::class, 'syarat_pendaftaran_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'pending');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'valid');
    }

    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'invalid');
    }

    public function getUrlFileAttribute()
    {
        if ($this->path_file) {
            return Storage::url($this->path_file);
        }
        
        return null;
    }
}
