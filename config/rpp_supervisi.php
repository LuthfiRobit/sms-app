<?php

// Instrumen Supervisi RPP — rubrik resmi (Perencanaan/Pelaksanaan/Asesmen
// Pembelajaran), ditranskrip dari bahanperubahan/INSTRUMEN PEMBELAJARAN.docx.
// Kriteria bersifat tetap (bukan master data yang diedit admin lewat UI),
// jadi disimpan sebagai config, bukan tabel.

return [

    'instrumen' => [

        'perencanaan' => [
            'label' => 'Perencanaan Pembelajaran',
            // % Capaian => Predikat, dicek dari atas ke bawah (nilai tertinggi duluan).
            'predikat' => [
                91 => 'Sangat Baik',
                81 => 'Baik',
                71 => 'Cukup',
                0 => 'Kurang',
            ],
            'kriteria' => [
                ['kode' => 'PR1', 'tahap' => null, 'teks' => 'Identifikasi Murid: Sesuai kondisi obyektif dan menunjukkan kesiapan belajar'],
                ['kode' => 'PR2', 'tahap' => null, 'teks' => 'Dimensi profil lulusan yang dipilih sesuai dengan materi pembelajaran.'],
                ['kode' => 'PR3', 'tahap' => null, 'teks' => 'Mengintegrasikan Topik Panca Cinta dalam Pembelajaran'],
                ['kode' => 'PR4', 'tahap' => null, 'teks' => 'Tujuan pembelajaran jelas, terukur, relevan serta mengandung komponen minimal yakni kompetensi dan materi serta mengintegrasikan topik panca cinta'],
                ['kode' => 'PR5', 'tahap' => null, 'teks' => 'Praktek pedagogis sesuai prinsip berkesadaran, bermakna, menggembirakan (mendukung pembelajaran siswa aktif)'],
                ['kode' => 'PR6', 'tahap' => null, 'teks' => 'Kemitraan pembelajaran melibatkan stakeholder yang mendukung tercapainya tujuan pembelajaran'],
                ['kode' => 'PR7', 'tahap' => null, 'teks' => 'Lingkungan pembelajaran mendukung suasana aman, inklusif, kolaborasi, refleksi, eksplorasi, dan berbagi ide, dapat mengakomodasi berbagai gaya belajar serta melibatkan pembentukan norma positif.'],
                ['kode' => 'PR8', 'tahap' => null, 'teks' => 'Pemanfaatan teknologi/digital mendukung pembelajaran interaktif dan kolaboratif.'],
                ['kode' => 'PR9', 'tahap' => null, 'teks' => 'Langkah-langkah pembelajaran runtut, sesuai waktu dan sesuai dengan tahapan pengalaman belajar (Memahami, Mengaplikasi dan Merefleksi).'],
            ],
        ],

        'pelaksanaan' => [
            'label' => 'Pelaksanaan Pembelajaran',
            'predikat' => [
                91 => 'Sangat Baik',
                81 => 'Baik',
                71 => 'Cukup',
                0 => 'Kurang',
            ],
            'kriteria' => [
                ['kode' => 'PL1', 'tahap' => 'Memahami', 'teks' => 'Guru menyampaikan tujuan pembelajaran'],
                ['kode' => 'PL2', 'tahap' => 'Memahami', 'teks' => 'Guru membuka pembelajaran dengan apersepsi dan motivasi.'],
                ['kode' => 'PL3', 'tahap' => 'Memahami', 'teks' => 'Menyajikan materi runtut, jelas, menarik.'],
                ['kode' => 'PL4', 'tahap' => 'Memahami', 'teks' => 'Memfasilitasi pertanyaan dan penggalian pengetahuan awal.'],
                ['kode' => 'PL5', 'tahap' => 'Memahami', 'teks' => 'Mengimplementasikan Prinsip Pembelajaran (Berkesadaran, Bermakna, Menggembirakan)'],
                ['kode' => 'PL6', 'tahap' => 'Mengaplikasi', 'teks' => 'Menggunakan media pembelajaran yang relevan dan interaktif.'],
                ['kode' => 'PL7', 'tahap' => 'Mengaplikasi', 'teks' => 'Memberikan pengalaman nyata (kontekstualisasi) melalui kegiatan praktik, proyek, simulasi dan lainnya.'],
                ['kode' => 'PL8', 'tahap' => 'Mengaplikasi', 'teks' => 'Melibatkan siswa aktif dalam kolaborasi dan diskusi.'],
                ['kode' => 'PL9', 'tahap' => 'Mengaplikasi', 'teks' => 'Mengarahkan berpikir kritis dan kreatif.'],
                ['kode' => 'PL10', 'tahap' => 'Mengaplikasi', 'teks' => 'Mengimplementasikan Prinsip Pembelajaran (Berkesadaran, Bermakna, Menggembirakan)'],
                ['kode' => 'PL11', 'tahap' => 'Merefleksi', 'teks' => 'Mengajak murid merefleksikan proses dan hasil belajar.'],
                ['kode' => 'PL12', 'tahap' => 'Merefleksi', 'teks' => 'Memberikan umpan balik dan rencana tindak lanjut'],
                ['kode' => 'PL13', 'tahap' => 'Merefleksi', 'teks' => 'Mengintegrasikan Topik Panca Cinta dalam merefleksikan pembelajaran'],
                ['kode' => 'PL14', 'tahap' => 'Merefleksi', 'teks' => 'Mengimplementasikan Prinsip Pembelajaran (Berkesadaran, Bermakna, Menggembirakan)'],
            ],
        ],

        'asesmen' => [
            'label' => 'Asesmen Pembelajaran',
            'predikat' => [
                91 => 'Sangat Baik',
                80 => 'Baik',
                71 => 'Cukup',
                0 => 'Kurang',
            ],
            'kriteria' => [
                ['kode' => 'AS1', 'tahap' => null, 'teks' => 'Terdapat Kriteria Ketercapaian Tujuan Pembelajaran (KKTP) yang dikembangkan dari Tujuan Pembelajaran'],
                ['kode' => 'AS2', 'tahap' => null, 'teks' => 'Instrumen penilaian sesuai tujuan pembelajaran.'],
                ['kode' => 'AS3', 'tahap' => null, 'teks' => 'Penilaian mencakup proses dan hasil belajar.'],
                ['kode' => 'AS4', 'tahap' => null, 'teks' => 'Penilaian berorientasi pada dimensi profil lulusan dan Topik Panca Cinta'],
                ['kode' => 'AS5', 'tahap' => null, 'teks' => 'Menggunakan teknik beragam (tes, non-tes, observasi, portofolio).'],
                ['kode' => 'AS6', 'tahap' => null, 'teks' => 'Memberikan umpan balik yang membangun.'],
                ['kode' => 'AS7', 'tahap' => null, 'teks' => 'Memanfaatkan hasil asesmen untuk perbaikan pembelajaran.'],
            ],
        ],

    ],

];
