<?php

namespace Database\Seeders;

use App\Models\Akademik\RppBagian;
use App\Models\Akademik\RppMasterOpsi;
use App\Models\Akademik\RppPoin;
use App\Models\Master\ModelPembelajaran;
use Illuminate\Database\Seeder;

/**
 * Templat awal RPP — disusun dari 2 contoh dokumen resmi: "RPP KBC SKI KELAS 5"
 * (format dasar 4 bagian) dan "RPP KBC_KLS 5 B. INDO_ IDE POKOK" (melengkapi
 * daftar Dimensi Profil Lulusan jadi 8 item, Topik Panca Cinta jadi 5 item,
 * menambah poin Pendekatan Pembelajaran, model LOK-R, dan poin Asesmen yang
 * butuh format bebas/tabel). Pakai updateOrCreate (bukan firstOrCreate) untuk
 * poin supaya re-run seeder ini selalu menyamakan urutan/tipe ke versi
 * terbaru — TAPI tidak menimpa data RPP yang sudah diisi guru (isi RPP ada
 * di rpp_poin_value, bukan di sini). Ini cuma titik awal: admin bebas
 * menambah/mengubah/menonaktifkan bagian, poin, dan opsi master dari panel
 * "Kelola Bagian & Poin RPP" tanpa migrasi.
 */
class RppTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $identifikasi = RppBagian::firstOrCreate(['nama' => 'Identifikasi'], ['urutan' => 1]);
        $desain = RppBagian::firstOrCreate(['nama' => 'Desain Pembelajaran'], ['urutan' => 2]);
        $pengalaman = RppBagian::firstOrCreate(['nama' => 'Pengalaman Belajar'], ['urutan' => 3]);
        $asesmen = RppBagian::firstOrCreate(['nama' => 'Asesmen'], ['urutan' => 4]);

        $poin = [
            // Identifikasi
            ['rpp_bagian_id' => $identifikasi->id, 'kode' => 'kondisi_awal_murid', 'label' => 'Kondisi Awal Murid', 'tipe' => 'teks_panjang', 'is_required' => true, 'urutan' => 1],
            ['rpp_bagian_id' => $identifikasi->id, 'kode' => 'dimensi_profil_lulusan', 'label' => 'Dimensi Profil Lulusan', 'tipe' => 'pilih_master', 'master_kategori' => 'dimensi_profil_lulusan', 'is_required' => true, 'urutan' => 2],
            ['rpp_bagian_id' => $identifikasi->id, 'kode' => 'lintas_disiplin_ilmu', 'label' => 'Lintas Disiplin Ilmu', 'tipe' => 'pasangan_kolom', 'kolom1_label' => 'Mata Pelajaran', 'kolom2_label' => 'Keterangan', 'urutan' => 3],
            ['rpp_bagian_id' => $identifikasi->id, 'kode' => 'topik_panca_cinta', 'label' => 'Topik Panca Cinta', 'tipe' => 'pilih_master', 'master_kategori' => 'topik_panca_cinta', 'is_required' => true, 'urutan' => 4],
            ['rpp_bagian_id' => $identifikasi->id, 'kode' => 'materi_integrasi_kbc', 'label' => 'Materi Integrasi Kurikulum Berbasis Cinta', 'tipe' => 'daftar_poin', 'urutan' => 5],

            // Desain Pembelajaran
            ['rpp_bagian_id' => $desain->id, 'kode' => 'capaian_pembelajaran', 'label' => 'Capaian Pembelajaran', 'tipe' => 'teks_panjang', 'is_required' => true, 'urutan' => 1],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'tujuan_pembelajaran', 'label' => 'Tujuan Pembelajaran', 'tipe' => 'teks_panjang', 'is_required' => true, 'urutan' => 2],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'iktp', 'label' => 'Indikator Ketercapaian Tujuan Pembelajaran (IKTP)', 'tipe' => 'daftar_poin', 'is_required' => true, 'urutan' => 3],
            // Pendekatan Pembelajaran (mis. "Deep Learning") beda dari Model Pembelajaran —
            // Model driven-nya bagian Inti, Pendekatan cuma informasi/filosofi mengajar,
            // jadi cukup poin pilih_master biasa (bukan kolom rpp tersendiri seperti Model).
            ['rpp_bagian_id' => $desain->id, 'kode' => 'pendekatan_pembelajaran', 'label' => 'Pendekatan Pembelajaran', 'tipe' => 'pilih_master', 'master_kategori' => 'pendekatan_pembelajaran', 'urutan' => 4],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'metode', 'label' => 'Metode', 'tipe' => 'daftar_poin', 'urutan' => 5],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'sumber_belajar', 'label' => 'Sumber Belajar', 'tipe' => 'pasangan_kolom', 'kolom1_label' => 'Sumber', 'kolom2_label' => 'Tautan/Keterangan', 'urutan' => 6],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'kemitraan_pembelajaran', 'label' => 'Kemitraan Pembelajaran', 'tipe' => 'daftar_poin', 'urutan' => 7],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'lingkungan_pembelajaran', 'label' => 'Lingkungan Pembelajaran', 'tipe' => 'teks_panjang', 'urutan' => 8],
            ['rpp_bagian_id' => $desain->id, 'kode' => 'pemanfaatan_digital', 'label' => 'Pemanfaatan Digital', 'tipe' => 'daftar_poin', 'urutan' => 9],

            // Pengalaman Belajar
            ['rpp_bagian_id' => $pengalaman->id, 'kode' => 'pendahuluan', 'label' => 'Pendahuluan', 'tipe' => 'daftar_poin', 'is_required' => true, 'urutan' => 1],
            // Artefak Khas Model — konten substansi (mis. rumusan masalah, rencana
            // proyek) yang jadi pijakan tahapan Inti, bukan urutan kegiatannya
            // sendiri. Reuse tipe teks_panjang biasa; yang membuatnya "adaptif per
            // model" murni override label/hint di view (lihat _poin-input,
            // _poin-display, dan pdf/akademik/rpp) berdasar kode ini, sumber
            // labelnya dari model_pembelajaran.label_artefak/deskripsi_artefak.
            ['rpp_bagian_id' => $pengalaman->id, 'kode' => 'artefak_model', 'label' => 'Artefak Khas Model Pembelajaran', 'tipe' => 'teks_panjang', 'is_required' => false, 'urutan' => 2],
            ['rpp_bagian_id' => $pengalaman->id, 'kode' => 'inti', 'label' => 'Inti', 'tipe' => 'model_pembelajaran', 'is_required' => true, 'urutan' => 3],
            ['rpp_bagian_id' => $pengalaman->id, 'kode' => 'penutup', 'label' => 'Penutup', 'tipe' => 'daftar_poin', 'is_required' => true, 'urutan' => 4],

            // Asesmen — Formatif Proses & Sumatif diganti ke teks_panjang: contoh
            // dokumen nyata isinya bukan cuma daftar poin pendek, tapi ada tabel
            // rubrik penilaian dan soal pilihan-ganda panjang dengan opsi a/b/c/d
            // — WYSIWYG (dengan tombol sisip tabel) jauh lebih pas untuk ini.
            ['rpp_bagian_id' => $asesmen->id, 'kode' => 'formatif_awal', 'label' => 'Formatif Awal', 'tipe' => 'daftar_poin', 'urutan' => 1],
            ['rpp_bagian_id' => $asesmen->id, 'kode' => 'formatif_proses', 'label' => 'Formatif Proses', 'tipe' => 'teks_panjang', 'urutan' => 2],
            ['rpp_bagian_id' => $asesmen->id, 'kode' => 'sumatif_soal', 'label' => 'Sumatif', 'tipe' => 'teks_panjang', 'urutan' => 3],
            ['rpp_bagian_id' => $asesmen->id, 'kode' => 'penilaian_produk', 'label' => 'Penilaian Produk', 'tipe' => 'teks_panjang', 'urutan' => 4],
        ];

        foreach ($poin as $p) {
            RppPoin::updateOrCreate(['kode' => $p['kode']], $p);
        }

        // Dimensi Profil Lulusan — daftar resmi 8 item (DPL 1-8).
        $dimensiProfilLulusan = [
            'Keimanan dan Ketakwaan kepada Tuhan YME',
            'Kewargaan',
            'Penalaran Kritis',
            'Kreativitas',
            'Kolaborasi',
            'Kemandirian',
            'Kesehatan',
            'Komunikasi',
        ];
        foreach ($dimensiProfilLulusan as $i => $nama) {
            RppMasterOpsi::firstOrCreate(
                ['kategori' => 'dimensi_profil_lulusan', 'nama' => $nama],
                ['urutan' => $i + 1]
            );
        }

        // Topik Panca Cinta — daftar resmi 5 item (TOPIK 1-5, sesuai nama "Panca").
        $topikPancaCinta = [
            'Cinta Allah dan Rasul-Nya',
            'Cinta Ilmu',
            'Cinta Diri dan Sesama Manusia',
            'Cinta Lingkungan',
            'Cinta Tanah Air',
        ];
        foreach ($topikPancaCinta as $i => $nama) {
            RppMasterOpsi::firstOrCreate(
                ['kategori' => 'topik_panca_cinta', 'nama' => $nama],
                ['urutan' => $i + 1]
            );
        }

        // Pendekatan Pembelajaran — daftar baru, baru terverifikasi 1 nilai
        // ("Deep Learning") dari contoh dokumen; admin tinggal tambah lewat
        // "Kelola Opsi" kalau ada pendekatan lain (mis. Kontekstual, Saintifik).
        RppMasterOpsi::firstOrCreate(
            ['kategori' => 'pendekatan_pembelajaran', 'nama' => 'Deep Learning'],
            ['urutan' => 1]
        );

        $discoveryLearning = ModelPembelajaran::firstOrCreate(
            ['nama' => 'Discovery Learning'],
            ['deskripsi' => 'Murid menemukan sendiri konsep/nilai lewat rangsangan, eksplorasi, dan pembuktian.', 'urutan' => 1]
        );

        $sintaksDiscoveryLearning = [
            ['nama_sintaks' => 'Stimulation (Pemberian Rangsangan)', 'meta_fase' => 'Memahami', 'urutan' => 1],
            ['nama_sintaks' => 'Problem Statement (Identifikasi Masalah)', 'meta_fase' => 'Memahami', 'urutan' => 2],
            ['nama_sintaks' => 'Data Collection (Pengumpulan Data)', 'meta_fase' => 'Mengaplikasi', 'urutan' => 3],
            ['nama_sintaks' => 'Data Processing (Pengolahan Data)', 'meta_fase' => 'Mengaplikasi', 'urutan' => 4],
            ['nama_sintaks' => 'Verification (Pembuktian)', 'meta_fase' => 'Merefleksi', 'urutan' => 5],
            ['nama_sintaks' => 'Generalization (Menarik Kesimpulan)', 'meta_fase' => 'Merefleksi', 'urutan' => 6],
        ];
        foreach ($sintaksDiscoveryLearning as $s) {
            $discoveryLearning->sintaks()->firstOrCreate(['urutan' => $s['urutan']], $s);
        }
        $discoveryLearning->update([
            'label_artefak' => 'Stimulus / Rangsangan Awal',
            'deskripsi_artefak' => 'Pertanyaan, fenomena, atau media pemantik yang akan disajikan di awal untuk memicu rasa ingin tahu murid.',
        ]);

        // LOK-R — Literasi, Orientasi, Kolaborasi, Refleksi. Ditemukan dari
        // contoh dokumen "RPP KBC_KLS 5 B. INDO_ IDE POKOK": tahapan Literasi
        // ada di bagian MEMAHAMI; Orientasi & Kolaborasi di MENGAPLIKASI;
        // Refleksi di MEREFLEKSI.
        $lokr = ModelPembelajaran::firstOrCreate(
            ['nama' => 'LOK-R'],
            ['deskripsi' => 'Literasi, Orientasi, Kolaborasi, Refleksi — murid membaca/menyimak sumber belajar, mengorientasikan tugas, berkolaborasi menyelesaikannya, lalu merefleksikan hasil belajar.', 'urutan' => 2]
        );

        $sintaksLokr = [
            ['nama_sintaks' => 'Literasi', 'meta_fase' => 'Memahami', 'urutan' => 1],
            ['nama_sintaks' => 'Orientasi', 'meta_fase' => 'Mengaplikasi', 'urutan' => 2],
            ['nama_sintaks' => 'Kolaborasi', 'meta_fase' => 'Mengaplikasi', 'urutan' => 3],
            ['nama_sintaks' => 'Refleksi', 'meta_fase' => 'Merefleksi', 'urutan' => 4],
        ];
        foreach ($sintaksLokr as $s) {
            $lokr->sintaks()->firstOrCreate(['urutan' => $s['urutan']], $s);
        }

        // 4 model pembelajaran lain (PBL, Cooperative Learning, PjBL, Inquiry
        // Learning) — dari bahan referensi "5 Model Pembelajaran Wajib Guru
        // Inovatif Abad 21" (bahanperubahan/). Sintaks tiap model dikelompokkan
        // ke meta_fase Memahami/Mengaplikasi/Merefleksi mengikuti pola pembagian
        // Discovery Learning di atas (fase-fase awal = Memahami, fase tengah =
        // Mengaplikasi, fase penutup = Merefleksi).
        $pbl = ModelPembelajaran::firstOrCreate(
            ['nama' => 'Problem Based Learning (PBL)'],
            ['deskripsi' => 'Murid belajar lewat pemecahan masalah kontekstual secara individu maupun kelompok.', 'urutan' => 3]
        );
        $sintaksPbl = [
            ['nama_sintaks' => 'Orientasi siswa pada masalah', 'meta_fase' => 'Memahami', 'urutan' => 1],
            ['nama_sintaks' => 'Mengorganisasikan siswa untuk belajar', 'meta_fase' => 'Memahami', 'urutan' => 2],
            ['nama_sintaks' => 'Membimbing penyelidikan individu maupun kelompok', 'meta_fase' => 'Mengaplikasi', 'urutan' => 3],
            ['nama_sintaks' => 'Mengembangkan dan menyajikan hasil karya', 'meta_fase' => 'Mengaplikasi', 'urutan' => 4],
            ['nama_sintaks' => 'Menganalisis dan mengevaluasi proses pemecahan masalah', 'meta_fase' => 'Merefleksi', 'urutan' => 5],
        ];
        foreach ($sintaksPbl as $s) {
            $pbl->sintaks()->firstOrCreate(['urutan' => $s['urutan']], $s);
        }
        $pbl->update([
            'label_artefak' => 'Rumusan Masalah',
            'deskripsi_artefak' => 'Masalah/skenario kontekstual dan otentik yang akan disajikan ke murid sebagai titik awal penyelidikan.',
        ]);

        $cooperative = ModelPembelajaran::firstOrCreate(
            ['nama' => 'Cooperative Learning'],
            ['deskripsi' => 'Murid belajar berkelompok secara heterogen untuk mencapai tujuan bersama.', 'urutan' => 4]
        );
        $sintaksCooperative = [
            ['nama_sintaks' => 'Menyampaikan tujuan dan memotivasi siswa', 'meta_fase' => 'Memahami', 'urutan' => 1],
            ['nama_sintaks' => 'Menyajikan informasi', 'meta_fase' => 'Memahami', 'urutan' => 2],
            ['nama_sintaks' => 'Mengorganisasikan siswa ke dalam kelompok belajar', 'meta_fase' => 'Mengaplikasi', 'urutan' => 3],
            ['nama_sintaks' => 'Membimbing kelompok bekerja dan belajar', 'meta_fase' => 'Mengaplikasi', 'urutan' => 4],
            ['nama_sintaks' => 'Evaluasi', 'meta_fase' => 'Merefleksi', 'urutan' => 5],
            ['nama_sintaks' => 'Memberikan penghargaan', 'meta_fase' => 'Merefleksi', 'urutan' => 6],
        ];
        foreach ($sintaksCooperative as $s) {
            $cooperative->sintaks()->firstOrCreate(['urutan' => $s['urutan']], $s);
        }
        $cooperative->update([
            'label_artefak' => 'Pembagian Kelompok & Peran',
            'deskripsi_artefak' => 'Dasar pembentukan kelompok (heterogen) dan pembagian peran/tugas tiap anggota.',
        ]);

        $pjbl = ModelPembelajaran::firstOrCreate(
            ['nama' => 'Project Based Learning (PjBL)'],
            ['deskripsi' => 'Murid belajar lewat pengerjaan proyek nyata dari pertanyaan esensial sampai evaluasi hasil.', 'urutan' => 5]
        );
        $sintaksPjbl = [
            ['nama_sintaks' => 'Mulai dengan pertanyaan esensial', 'meta_fase' => 'Memahami', 'urutan' => 1],
            ['nama_sintaks' => 'Merancang perencanaan proyek', 'meta_fase' => 'Memahami', 'urutan' => 2],
            ['nama_sintaks' => 'Menyusun jadwal', 'meta_fase' => 'Mengaplikasi', 'urutan' => 3],
            ['nama_sintaks' => 'Mengawasi jalannya proyek', 'meta_fase' => 'Mengaplikasi', 'urutan' => 4],
            ['nama_sintaks' => 'Penilaian hasil', 'meta_fase' => 'Merefleksi', 'urutan' => 5],
            ['nama_sintaks' => 'Evaluasi pengalaman', 'meta_fase' => 'Merefleksi', 'urutan' => 6],
        ];
        foreach ($sintaksPjbl as $s) {
            $pjbl->sintaks()->firstOrCreate(['urutan' => $s['urutan']], $s);
        }
        $pjbl->update([
            'label_artefak' => 'Perencanaan Proyek',
            'deskripsi_artefak' => 'Produk yang ditargetkan, alat & bahan, serta gambaran jadwal pengerjaan proyek.',
        ]);

        $inquiry = ModelPembelajaran::firstOrCreate(
            ['nama' => 'Inquiry Learning'],
            ['deskripsi' => 'Murid menyelidiki sendiri jawaban dari masalah/pertanyaan lewat hipotesis, data, dan analisis.', 'urutan' => 6]
        );
        $sintaksInquiry = [
            ['nama_sintaks' => 'Orientasi', 'meta_fase' => 'Memahami', 'urutan' => 1],
            ['nama_sintaks' => 'Merumuskan masalah', 'meta_fase' => 'Memahami', 'urutan' => 2],
            ['nama_sintaks' => 'Merumuskan hipotesis', 'meta_fase' => 'Mengaplikasi', 'urutan' => 3],
            ['nama_sintaks' => 'Mengumpulkan data', 'meta_fase' => 'Mengaplikasi', 'urutan' => 4],
            ['nama_sintaks' => 'Menganalisis data', 'meta_fase' => 'Merefleksi', 'urutan' => 5],
            ['nama_sintaks' => 'Membuat kesimpulan', 'meta_fase' => 'Merefleksi', 'urutan' => 6],
        ];
        foreach ($sintaksInquiry as $s) {
            $inquiry->sintaks()->firstOrCreate(['urutan' => $s['urutan']], $s);
        }
        $inquiry->update([
            'label_artefak' => 'Rumusan Masalah & Hipotesis Awal',
            'deskripsi_artefak' => 'Pertanyaan penyelidikan dan dugaan sementara (hipotesis) yang akan diuji murid.',
        ]);
    }
}
