<?php

namespace Database\Seeders;

use App\Models\Master\Guru;
use App\Models\Master\Lembaga;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $lembaga = Lembaga::orderBy('urutan')->pluck('id', 'kode');

        // ── Data guru per lembaga ─────────────────────────────────────
        // Format: [gelar_depan, nama, gelar_belakang, jk, nip]
        // NIP null = guru honorer / non-ASN
        $data = [

            // ── MINU Kraksaan (MI) ────────────────────────────────────
            'MINU-KRK' => [
                ['', 'Ahmad Fauzi', 'S.Pd.I.', 'L', '197803142005011002'],
                ['Hj.', 'Siti Maimunah', 'S.Pd.I.', 'P', '198005202006042001'],
                ['', 'Muhammad Zainul Arifin', 'S.Pd.', 'L', null],
                ['', 'Nurul Hidayah', 'S.Pd.I.', 'P', null],
                ['', 'Abdul Hamid', 'S.Pd.I.', 'L', '197606102007011008'],
                ['', 'Fatimah Azzahra', 'S.Pd.', 'P', null],
                ['', 'Khoirul Anam', 'S.Pd.I.', 'L', null],
                ['', 'Roudlotul Jannah', 'S.Pd.I.', 'P', '198209142008012003'],
                ['', 'Syaiful Bahri', 'S.Pd.', 'L', null],
                ['', 'Umi Kulsum', 'S.Pd.I.', 'P', null],
            ],

            // ── MINU Sunan Ampel Maron (MI) ───────────────────────────
            'MINU-SAM' => [
                ['', 'Moch. Cholil', 'S.Pd.I.', 'L', '197904172006041003'],
                ['', 'Nur Aini', 'S.Pd.I.', 'P', '198103052007012005'],
                ['', 'Imam Syafi\'i', 'S.Pd.', 'L', null],
                ['', 'Lilik Masruroh', 'S.Pd.I.', 'P', null],
                ['', 'Fathur Rohman', 'S.Pd.I.', 'L', '197811222006011004'],
                ['', 'Kholifah', 'S.Pd.I.', 'P', null],
                ['', 'Agus Salim', 'S.Pd.', 'L', null],
                ['', 'Siti Aminah', 'S.Pd.I.', 'P', '198307282008012002'],
                ['', 'Wahyudi Santoso', 'S.Pd.', 'L', null],
            ],

            // ── MTs NU Sunan Ampel Maron (MTs) ───────────────────────
            'MTSNU-SAM' => [
                ['Drs.', 'Abdul Karim', 'M.Pd.I.', 'L', '196905201997031002'],
                ['Hj.', 'Siti Zuhriyah', 'S.Pd.I.', 'P', '197202142000032001'],
                ['', 'Muhammad Arif Wibowo', 'S.Pd.', 'L', '198001072005011005'],
                ['', 'Dewi Rahmawati', 'S.Pd.', 'P', null],
                ['', 'Faisol Amin', 'S.Ag.', 'L', '197808192004011003'],
                ['', 'Lailatul Fitriyah', 'S.Pd.', 'P', null],
                ['', 'Misbahul Munir', 'S.Pd.I.', 'L', null],
                ['', 'Nikmatul Khoir', 'S.Pd.', 'P', '198510032009012004'],
                ['', 'Saifulloh', 'S.Pd.I.', 'L', null],
                ['', 'Tutik Handayani', 'S.Pd.', 'P', null],
                ['', 'Zainal Abidin', 'S.Pd.', 'L', '197903122006011002'],
                ['', 'Arina Manasikana', 'S.Pd.', 'P', null],
                ['', 'Busyairi', 'S.Ag.', 'L', null],
            ],

            // ── MTs NU Maron (MTs) ────────────────────────────────────
            'MTSNU-MRN' => [
                ['Drs.', 'Nurul Huda', 'M.Pd.', 'L', '196712141994031003'],
                ['', 'Sri Wahyuni', 'S.Pd.', 'P', '197506082002122001'],
                ['', 'Ahmad Muzakki', 'S.Pd.I.', 'L', '198204172008011002'],
                ['', 'Siti Fatimah', 'S.Pd.', 'P', null],
                ['', 'Nur Kholis', 'S.Pd.', 'L', '198009212006011007'],
                ['', 'Khoirun Nisa\'', 'S.Pd.', 'P', null],
                ['', 'Abdurrohman', 'S.Pd.I.', 'L', null],
                ['', 'Luluk Masrohah', 'S.Pd.', 'P', '198612192009012005'],
                ['', 'Moh. Fathoni', 'S.Pd.', 'L', null],
                ['', 'Nurul Aini', 'S.Pd.I.', 'P', null],
                ['', 'Rif\'atul Mahmudah', 'S.Pd.', 'P', null],
                ['', 'Syamsul Arifin', 'S.Kom.', 'L', null],
            ],

            // ── SMP Ma'arif Kraksaan (SMP) ────────────────────────────
            'SMP-KRK' => [
                ['Drs.', 'Slamet Widodo', 'M.Pd.', 'L', '196804221997031004'],
                ['', 'Indah Kurniawati', 'S.Pd.', 'P', '197902032003122002'],
                ['', 'Ahmad Fadholi', 'S.Pd.', 'L', '198107142006011003'],
                ['', 'Ratna Dewi Astuti', 'S.Pd.', 'P', null],
                ['', 'Heri Susanto', 'S.Pd.', 'L', '197805182005011005'],
                ['', 'Yuliana Rahayu', 'S.Pd.', 'P', null],
                ['', 'Muchamad Soleh', 'S.Pd.', 'L', null],
                ['', 'Fitria Ningsih', 'S.Pd.', 'P', '198809012010012003'],
                ['', 'Dwi Prasetyo', 'S.Pd.', 'L', null],
                ['', 'Maftukhah', 'S.Pd.I.', 'P', null],
                ['', 'Arif Budiman', 'S.Kom.', 'L', null],
                ['', 'Evi Wahyuningsih', 'S.Pd.', 'P', null],
            ],

            // ── MANU Maron (MA) ───────────────────────────────────────
            'MANU-MRN' => [
                ['Drs.', 'H. Moch. Anwar', 'M.Pd.', 'L', '196503121990031003'],
                ['Dra.', 'Hj. Siti Rohmah', 'M.Ag.', 'P', '196807221993032001'],
                ['', 'Ahmad Syafi\'i', 'S.Pd.', 'L', '197904112004011002'],
                ['', 'Nining Wahyuni', 'S.Pd.', 'P', '198003242006042002'],
                ['', 'Ali Murtadho', 'S.Pd.I.', 'L', '198206172008011004'],
                ['', 'Umi Habibah', 'S.Pd.', 'P', null],
                ['', 'Firdaus Muttaqin', 'S.Pd.', 'L', null],
                ['', 'Rif\'atul Izzah', 'S.Pd.', 'P', '198911032012012001'],
                ['', 'Ainul Yaqin', 'S.Ag.', 'L', '197712052002121002'],
                ['', 'Nur Laila', 'S.Pd.', 'P', null],
                ['', 'Zainul Muttaqin', 'S.Pd.I.', 'L', null],
                ['Dr.', 'Lutfiyah Hanifah', 'M.Pd.', 'P', null],
                ['', 'Achmad Fauzan', 'S.Pd.', 'L', null],
                ['', 'Sofiyatun', 'S.Pd.', 'P', null],
                ['', 'Moh. Khoirul Umam', 'S.H.I.', 'L', null],
            ],

            // ── SMK Ma'arif Gending (SMK) ─────────────────────────────
            'SMK-GDG' => [
                ['Drs.', 'Suprapto', 'M.T.', 'L', '196901181994031002'],
                ['', 'Anik Setyowati', 'S.Pd.', 'P', '197706142003122004'],
                ['', 'Moh. Lutfi Hakim', 'S.T.', 'L', '198104032006011006'],
                ['', 'Dwi Agustina', 'S.Pd.', 'P', null],
                ['', 'Eko Prasetyo', 'S.Kom.', 'L', '198308192008011003'],
                ['', 'Sari Murniati', 'S.Pd.', 'P', null],
                ['', 'Yusuf Effendi', 'S.T.', 'L', null],
                ['', 'Winda Puspitasari', 'S.Pd.', 'P', '199003122014022001'],
                ['', 'Rizki Firmansyah', 'S.Kom.', 'L', null],
                ['', 'Nur Azizah', 'S.Pd.', 'P', null],
                ['', 'Agung Setiawan', 'S.T.', 'L', null],
                ['', 'Dian Permatasari', 'S.Pd.', 'P', null],
                ['', 'Fachrul Rozi', 'S.Kom.', 'L', null],
                ['', 'Maya Kusuma Dewi', 'S.Pd.', 'P', null],
                ['', 'Ibnu Hajar', 'S.Pd.I.', 'L', null],
                ['', 'Silvia Andriani', 'S.Pd.', 'P', '199107242015032002'],
            ],
        ];

        foreach ($data as $kode => $guruList) {
            $lembagaId = $lembaga[$kode] ?? null;
            if (! $lembagaId) {
                continue;
            }

            foreach ($guruList as [$gelarDepan, $nama, $gelarBelakang, $jk, $nip]) {
                Guru::firstOrCreate(
                    ['lembaga_id' => $lembagaId, 'nama' => $nama],
                    [
                        'gelar_depan'    => $gelarDepan ?: null,
                        'gelar_belakang' => $gelarBelakang ?: null,
                        'jenis_kelamin'  => $jk,
                        'nip'            => $nip,
                        'status'         => 'aktif',
                    ]
                );
            }
        }

        $total = Guru::count();
        $this->command->info("GuruSeeder: {$total} guru berhasil di-seed untuk 7 lembaga.");
    }
}
