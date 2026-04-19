<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Portal\PortalPendaftaranService;
use App\Models\Transaksi\Pendaftaran;
use Illuminate\Http\Request;

class PengumumanPesertaController extends Controller
{
    public function __construct(
        protected PortalPendaftaranService $portalPendaftaranService
    ) {
    }

    public function index()
    {
        $userId = auth()->user()->id_user;
        
        $result = $this->portalPendaftaranService->getPendaftaranSaya($userId);
        $pendaftaranList = $result['data'] ?? collect();

        $pendaftaranList->each(function ($pendaftaran) {
            $pendaftaran->load('hasilSeleksi');
        });

        return view('portal.pengumuman.index', compact('pendaftaranList'));
    }

    public function downloadKartu(int $pendaftaranId)
    {
        $userId = auth()->user()->id_user;

        // Ownership check via PortalPendaftaranService
        // Ini akan throw AuthorizationException jika pendaftaran bukan milik user
        $this->portalPendaftaranService->getDetailPendaftaran($pendaftaranId, $userId);

        // Ambil data pendaftaran beserta hasil seleksinya (eager load relation yang konsisten)
        $pendaftaran = Pendaftaran::with(['hasilSeleksi', 'peserta.user'])->findOrFail($pendaftaranId);

        // Cek: hasilSeleksi->waktu_pengumuman tidak null
        $hasilSeleksi = $pendaftaran->hasilSeleksi;
        if (!$hasilSeleksi || is_null($hasilSeleksi->waktu_pengumuman)) {
            abort(404, 'Pengumuman belum tersedia.');
        }

        // Jika M8 ada: panggil SeleksiService->generateKartuPeserta($pendaftaranId)
        if (class_exists(\App\Services\PPDB\SeleksiService::class)) {
            $seleksiService = app(\App\Services\PPDB\SeleksiService::class);
            return $seleksiService->generateKartuPeserta($pendaftaranId);
        }

        // Jika M8 belum ada: return response()->streamDownload untuk PDF placeholder sederhana
        return response()->streamDownload(function () use ($pendaftaran) {
            echo "KARTU PESERTA\n===================\n";
            echo "ID Pendaftaran: " . $pendaftaran->id . "\n";
            echo "Nama: " . ($pendaftaran->peserta->user->name ?? 'Peserta') . "\n";
            echo "Status: LULUS\n";
            echo "\nIni adalah placeholder kartu peserta sederhana.";
        }, 'kartu-peserta-' . $pendaftaran->id . '.txt'); // Use txt as placeholder instead of full PDF generation library
    }
}
