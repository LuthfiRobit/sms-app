<?php

namespace App\Models\Akademik;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiGuru extends Model
{
    protected $table = 'absensi_guru';

    protected $fillable = [
        'guru_id', 'lembaga_id', 'tanggal',
        'jam_masuk', 'lat_masuk', 'lng_masuk', 'jarak_masuk_m', 'akurasi_masuk_m', 'selfie_masuk',
        'jam_pulang', 'lat_pulang', 'lng_pulang', 'jarak_pulang_m', 'akurasi_pulang_m', 'selfie_pulang',
        'status', 'flag_mock_location', 'keterangan',
        'is_koreksi_manual', 'dikoreksi_oleh',
        'face_verified_masuk', 'face_confidence_masuk', 'face_liveness_ok_masuk',
        'face_verified_pulang', 'face_confidence_pulang', 'face_liveness_ok_pulang',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'lat_masuk' => 'float',
        'lng_masuk' => 'float',
        'lat_pulang' => 'float',
        'lng_pulang' => 'float',
        'flag_mock_location' => 'boolean',
        'is_koreksi_manual' => 'boolean',
        'face_confidence_masuk' => 'float',
        'face_liveness_ok_masuk' => 'boolean',
        'face_confidence_pulang' => 'float',
        'face_liveness_ok_pulang' => 'boolean',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function dikoreksiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikoreksi_oleh', 'id_user');
    }
}
