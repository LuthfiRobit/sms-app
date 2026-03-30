<?php

namespace App\Models\Peserta;

use App\Models\User;
use App\Models\Transaksi\Pendaftaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peserta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peserta';

    protected $fillable = [
        'user_id',
        'nisn',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'kebutuhan_khusus',
        'no_kk',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Get the user that owns the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        // Sesuaikan primary key parent agar foreign keys sinkron 
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Get the alamat record associated with the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function alamat(): HasOne
    {
        return $this->hasOne(PesertaAlamat::class, 'peserta_id');
    }

    /**
     * Get the orang tua records for the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orangTua(): HasMany
    {
        return $this->hasMany(PesertaOrangTua::class, 'peserta_id');
    }

    /**
     * Get the periodik record associated with the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function periodik(): HasOne
    {
        return $this->hasOne(PesertaPeriodik::class, 'peserta_id');
    }

    /**
     * Get the kontak record associated with the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function kontak(): HasOne
    {
        return $this->hasOne(PesertaKontak::class, 'peserta_id');
    }

    /**
     * Get the dokumen pribadi record associated with the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dokumenPribadi(): HasOne
    {
        return $this->hasOne(PesertaDokumenPribadi::class, 'peserta_id');
    }

    /**
     * Get the pendaftaran records for the peserta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'peserta_id');
    }

    /**
     * Accessor untuk nama lengkap yang di-capitalize di setiap awal kata.
     *
     * @param  string  $value
     * @return string
     */
    public function getNamaLengkapAttribute($value)
    {
        return ucwords(strtolower($value));
    }
}
