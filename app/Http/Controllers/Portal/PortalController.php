<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Ppdb\JadwalPendaftaran;
use App\Models\Ppdb\PembukaanPpdb;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Beranda publik portal PPDB.
     * Menampilkan info pembukaan aktif, jalur pendaftaran, dan jadwal terdekat.
     * Dapat diakses tanpa login.
     */
    public function beranda(): View
    {
        // Pembukaan PPDB aktif beserta jalur yang aktif
        $pembukaan = PembukaanPpdb::aktif()
            ->with([
                'tahunPelajaran',
                'jalurPendaftaran' => fn($q) => $q->aktif()->orderBy('urutan'),
                'jalurPendaftaran.biayaRegistrasi',
                'jalurPendaftaran.jadwalPendaftaran' => fn($q) => $q->aktif(),
            ])
            ->first();

        // Jadwal pendaftaran terdekat (yang belum selesai, tipe 'pendaftaran')
        $jadwal_terdekat = null;
        if ($pembukaan) {
            $jadwal_terdekat = JadwalPendaftaran::aktif()
                ->byTipe('pendaftaran')
                ->whereHas('jalurPendaftaran', fn($q) => $q->where('pembukaan_ppdb_id', $pembukaan->id))
                ->whereDate('selesai', '>=', now())
                ->orderBy('mulai')
                ->first();
        }

        // Semua jadwal untuk section timeline (dari pembukaan aktif)
        $semua_jadwal = collect();
        if ($pembukaan) {
            $semua_jadwal = JadwalPendaftaran::whereHas(
                'jalurPendaftaran',
                fn($q) => $q->where('pembukaan_ppdb_id', $pembukaan->id)
            )
                ->orderBy('mulai')
                ->get();
        }

        return view('portal.beranda', compact('pembukaan', 'jadwal_terdekat', 'semua_jadwal'));
    }

    /**
     * Halaman informasi statis tata cara PPDB.
     */
    public function info(): View
    {
        return view('portal.info');
    }
}
