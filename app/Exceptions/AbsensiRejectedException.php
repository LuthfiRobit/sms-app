<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Penolakan absen yang butuh ditampilkan berbeda-beda di mobile (ikon/judul
 * per kasus), bukan cuma teks generik — bawa $code machine-readable di
 * samping pesan siap-tampil, supaya mobile tidak perlu string-match pesan
 * Indonesia yang isinya bisa berubah (mis. jarak/radius yang disisipkan).
 */
class AbsensiRejectedException extends RuntimeException
{
    /** Nama properti sengaja "errorCode", bukan "code" — Exception bawaan PHP
     *  sudah punya properti $code sendiri (non-readonly), jadi promosi
     *  readonly di sini akan bentrok kalau memakai nama yang sama. */
    public function __construct(string $message, public readonly string $errorCode)
    {
        parent::__construct($message);
    }
}
