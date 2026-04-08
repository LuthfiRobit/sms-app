<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Transaksi\SeleksiService;
use App\Services\LogActivityService;
use App\Models\Ppdb\JalurPendaftaran;
use App\Models\Transaksi\Pendaftaran;

class SeleksiController extends Controller
{
    public function __construct(
        protected SeleksiService $seleksiService,
        protected LogActivityService $logActivity,
    ) {
    }

    public function index($jalurId)
    {
        $response = $this->seleksiService->index($jalurId);
        
        if (!$response['success']) {
            return redirect()->back()->with('error', $response['message']);
        }

        $jalur = $response['data']['jalur'];
        $kuota = $response['data']['kuota'];
        $pendaftaran = $response['data']['pendaftaran'];
        
        $sudahDinilai = collect($pendaftaran)->filter(function($p) {
            return !empty($p['nilai']);
        })->count();

        $belumDinilai = count($pendaftaran) - $sudahDinilai;

        return view('admin.seleksi.index', compact('jalurId', 'jalur', 'kuota', 'pendaftaran', 'sudahDinilai', 'belumDinilai'));
    }

    public function penilaian($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::with(['peserta', 'seleksi'])->findOrFail($pendaftaranId);
        $jalurId = $pendaftaran->jalur_pendaftaran_id;
        
        // Optional: If you want to prevent input when not in verifikasi
        // if ($pendaftaran->status !== Pendaftaran::STATUS_VERIFIKASI) {
        //     return redirect()->route('admin.seleksi.index', $jalurId)->with('warning', 'Pendaftaran tidak dalam status verifikasi.');
        // }

        return view('admin.seleksi.penilaian', compact('pendaftaran', 'jalurId'));
    }

    public function inputNilai(Request $request, $pendaftaranId)
    {
        $request->validate([
            'nilaiData' => 'required|array',
            'nilaiData.*.model_penilaian' => 'required|string',
            'nilaiData.*.nilai' => 'required|numeric|min:0|max:100',
            'nilaiData.*.bobot' => 'required|numeric|min:0|max:1',
        ]);

        $reviewerId = auth()->id();
        $response = $this->seleksiService->inputNilai($pendaftaranId, $request->input('nilaiData'), $reviewerId);

        if ($response['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} input nilai seleksi",
                "Admin {$nama} store Seleksi: nilai untuk pendaftaran ID #{$pendaftaranId} ("
                    . count($request->input('nilaiData')) . ' komponen penilaian)'
            );
            $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);
            return redirect()->route('admin.seleksi.index', $pendaftaran->jalur_pendaftaran_id)
                             ->with('success', $response['message']);
        }

        return redirect()->back()
                         ->withErrors($response['errors'] ?? [$response['message']])
                         ->withInput();
    }

    public function hitungRanking(Request $request, $jalurId)
    {
        $userId = auth()->id();
        $response = $this->seleksiService->hitungRanking($jalurId, $userId);

        if ($response['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} hitung ranking seleksi",
                "Admin {$nama} hitungRanking JalurPendaftaran: jalur ID #{$jalurId}"
            );
            return redirect()->route('admin.seleksi.hasil', $jalurId)
                             ->with('success', $response['message']);
        }

        // Gagal — kembali ke index dengan pesan error yang jelas
        return redirect()->route('admin.seleksi.index', $jalurId)
                         ->with('error', $response['message']);
    }

    public function hasil($jalurId)
    {
        $response = $this->seleksiService->getHasilSeleksi($jalurId);
        
        if (!$response['success']) {
            return redirect()->back()->with('error', $response['message']);
        }

        $data = $response['data'];
        return view('admin.seleksi.hasil', compact('data', 'jalurId'));
    }

    public function pengumuman(Request $request, $jalurId)
    {
        $userId = auth()->id();
        $response = $this->seleksiService->pengumuman($jalurId, $userId);

        if ($response['success']) {
            $nama = auth()->user()->name ?? 'Admin';
            $this->logActivity->log(
                "Admin {$nama} umumkan hasil seleksi",
                "Admin {$nama} pengumuman HasilSeleksi: jalur ID #{$jalurId}"
            );
            return redirect()->route('admin.seleksi.hasil', $jalurId)->with('success', $response['message']);
        }

        return redirect()->back()->with('error', $response['message']);
    }

    public function downloadPengumuman($jalurId)
    {
        try {
            $path = $this->seleksiService->generatePdfPengumuman($jalurId);
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
            return response()->download($fullPath);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendownload pengumuman: ' . $e->getMessage());
        }
    }

    public function downloadKartu($pendaftaranId)
    {
        try {
            $path = $this->seleksiService->generateKartuPeserta($pendaftaranId);
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
            return response()->download($fullPath);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendownload kartu peserta: ' . $e->getMessage());
        }
    }
}
