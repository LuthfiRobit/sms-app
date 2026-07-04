<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanIzinGuru extends Model
{
    protected $table = 'pengajuan_izin_guru';

    protected $fillable = [
        'guru_id', 'lembaga_id', 'jenis', 'tanggal_mulai', 'tanggal_selesai',
        'alasan', 'lampiran', 'status', 'catatan_admin', 'diproses_at', 'diproses_oleh',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diproses_at' => 'datetime',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh', 'id_user');
    }

    public static function statusBadge(string $status): array
    {
        return match ($status) {
            'menunggu' => ['class' => 'warning text-dark', 'label' => 'Menunggu'],
            'disetujui' => ['class' => 'success', 'label' => 'Disetujui'],
            'ditolak' => ['class' => 'danger', 'label' => 'Ditolak'],
            default => ['class' => 'secondary', 'label' => ucfirst($status)],
        };
    }
}
