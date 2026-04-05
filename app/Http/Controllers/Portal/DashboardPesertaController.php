<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Ppdb\PembukaanPpdb;
use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use Illuminate\View\View;

class DashboardPesertaController extends Controller
{
    public function __construct(
        protected PesertaRepositoryInterface    $pesertaRepository,
        protected PendaftaranRepositoryInterface $pendaftaranRepository,
    ) {}

    /**
     * Dashboard utama peserta.
     * Menampilkan progress profil, pembukaan PPDB aktif, dan daftar pendaftaran.
     */
    public function index(): View
    {
        $user    = auth()->user();
        $peserta = $this->pesertaRepository->findByUserId($user->id_user);

        // ── Hitung progress kelengkapan profil ──────────────────────────────
        $progress_profil = $this->hitungProgressProfil($peserta);

        // ── Data pendaftaran peserta ─────────────────────────────────────────
        $pendaftaran_list = collect();
        if ($peserta) {
            $pendaftaran_list = $this->pendaftaranRepository->all(
                filters: ['peserta_id' => $peserta->id],
                with:    ['jalurPendaftaran', 'jalurPendaftaran.pembukaanPpdb'],
            );
        }

        // ── Pembukaan PPDB aktif untuk info ─────────────────────────────────
        $pembukaan = PembukaanPpdb::aktif()
            ->with([
                'tahunPelajaran',
                'jalurPendaftaran' => fn($q) => $q->aktif()->orderBy('urutan'),
            ])
            ->first();

        return view('portal.dashboard', compact(
            'user',
            'peserta',
            'progress_profil',
            'pendaftaran_list',
            'pembukaan',
        ));
    }

    /**
     * Hitung persentase kelengkapan data profil peserta.
     *
     * Mengecek 6 aspek data sesuai struktur tabel Dapodik:
     * 1. Data pribadi inti (kolom di tabel peserta)
     * 2. Alamat (tabel peserta_alamat)
     * 3. Orang tua (tabel peserta_orang_tua — minimal 1 data)
     * 4. Data periodik (tabel peserta_periodik)
     * 5. Kontak (tabel peserta_kontak)
     * 6. Dokumen pribadi (tabel peserta_dokumen_pribadi)
     *
     * @param mixed $peserta
     * @return array{persen: int, aspek: array, lengkap: int, total: int}
     */
    private function hitungProgressProfil(mixed $peserta): array
    {
        if (!$peserta) {
            return [
                'persen' => 0,
                'lengkap' => 0,
                'total'   => 6,
                'aspek'   => array_fill_keys(
                    ['pribadi', 'alamat', 'orang_tua', 'periodik', 'kontak', 'dokumen'],
                    false
                ),
            ];
        }

        // Load semua relasi sekaligus untuk performa
        $peserta->loadMissing(['alamat', 'orangTua', 'periodik', 'kontak', 'dokumenPribadi']);

        $aspek = [
            // Data pribadi: kolom wajib minimal harus terisi
            'pribadi'   => filled($peserta->nama_lengkap)
                        && filled($peserta->nik)
                        && filled($peserta->tempat_lahir)
                        && filled($peserta->tanggal_lahir)
                        && filled($peserta->jenis_kelamin)
                        && filled($peserta->agama),

            // Relasi HasOne — null berarti belum ada record
            'alamat'    => filled($peserta->alamat?->alamat)
                        && filled($peserta->alamat?->kabupaten_kota),

            // Relasi HasMany — minimal 1 data orang tua/wali
            'orang_tua' => $peserta->orangTua->isNotEmpty()
                        && filled($peserta->orangTua->first()?->nama),

            'periodik'  => filled($peserta->periodik?->tinggi_badan)
                        && filled($peserta->periodik?->berat_badan),

            'kontak'    => filled($peserta->kontak?->no_hp),

            'dokumen'   => filled($peserta->dokumenPribadi),
        ];

        $lengkap = count(array_filter($aspek));
        $total   = count($aspek);
        $persen  = $total > 0 ? (int) round($lengkap / $total * 100) : 0;

        return compact('persen', 'lengkap', 'total', 'aspek');
    }
}
