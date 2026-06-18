<?php

namespace App\Models\Transaksi;

use App\Models\Master\Lembaga;
use App\Models\Master\TahunPelajaran;
use App\Models\Peserta\Peserta;
use App\Models\Ppdb\JalurPendaftaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pendaftaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pendaftaran';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMIT = 'submit';
    public const STATUS_VERIFIKASI = 'verifikasi';
    public const STATUS_LULUS = 'lulus';
    public const STATUS_TIDAK_LULUS = 'tidak_lulus';
    public const STATUS_DAFTAR_ULANG = 'daftar_ulang';
    public const STATUS_SISWA_TETAP = 'siswa_tetap';

    protected $fillable = [
        'no_pendaftaran',
        'lembaga_id',
        'peserta_id',
        'jalur_pendaftaran_id',
        'tahun_pelajaran_id',
        'status',
        'tanggal_daftar',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_daftar' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    public function jalurPendaftaran(): BelongsTo
    {
        return $this->belongsTo(JalurPendaftaran::class, 'jalur_pendaftaran_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id_user');
    }

    public function pendaftaranFieldValue(): HasMany
    {
        return $this->hasMany(PendaftaranFieldValue::class, 'pendaftaran_id');
    }

    public function dokumenPeserta(): HasMany
    {
        return $this->hasMany(DokumenPeserta::class, 'pendaftaran_id');
    }

    public function pembayaranPpdb(): HasMany
    {
        return $this->hasMany(PembayaranPpdb::class, 'pendaftaran_id');
    }

    public function seleksi(): HasMany
    {
        return $this->hasMany(Seleksi::class, 'pendaftaran_id');
    }

    public function hasilSeleksi(): HasOne
    {
        return $this->hasOne(HasilSeleksi::class, 'pendaftaran_id');
    }
}
