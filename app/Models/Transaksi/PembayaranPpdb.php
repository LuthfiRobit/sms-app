<?php

namespace App\Models\Transaksi;

use App\Models\Ppdb\BiayaRegistrasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranPpdb extends Model
{
    protected $table = 'pembayaran_ppdb';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUND = 'refund';

    protected $fillable = [
        'pendaftaran_id',
        'biaya_registrasi_id',
        'metode',
        'status',
        'snap_token',
        'order_id',
        'amount',
        'waktu_bayar',
        'bukti_bayar',
        'keterangan',
        'midtrans_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'waktu_bayar' => 'datetime',
        'midtrans_response' => 'array',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function biayaRegistrasi(): BelongsTo
    {
        return $this->belongsTo(BiayaRegistrasi::class, 'biaya_registrasi_id');
    }
}
