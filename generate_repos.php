<?php

$config = [
    'Peserta' => [
        'Peserta' => [
            'model' => 'App\Models\Peserta\Peserta',
            'custom_interface' => "    public function findByNisn(string \$nisn): ?Peserta;\n    public function findByNik(string \$nik): ?Peserta;\n    public function findByUserId(int \$userId): ?Peserta;\n",
            'custom_impl' => "    public function findByNisn(string \$nisn): ?Peserta { return \$this->model->where('nisn', \$nisn)->first(); }\n    public function findByNik(string \$nik): ?Peserta { return \$this->model->where('nik', \$nik)->first(); }\n    public function findByUserId(int \$userId): ?Peserta { return \$this->model->where('user_id', \$userId)->first(); }\n"
        ],
        'PesertaAlamat' => [
            'model' => 'App\Models\Peserta\PesertaAlamat',
            'custom_interface' => "    public function findByPesertaId(int \$pesertaId): ?PesertaAlamat;\n    public function upsert(int \$pesertaId, array \$data): PesertaAlamat;\n",
            'custom_impl' => "    public function findByPesertaId(int \$pesertaId): ?PesertaAlamat { return \$this->model->where('peserta_id', \$pesertaId)->first(); }\n    public function upsert(int \$pesertaId, array \$data): PesertaAlamat { return \$this->model->updateOrCreate(['peserta_id' => \$pesertaId], \$data); }\n"
        ],
        'PesertaOrangTua' => [
            'model' => 'App\Models\Peserta\PesertaOrangTua',
            'custom_interface' => "    public function findByPesertaIdAndTipe(int \$pesertaId, string \$tipe): ?PesertaOrangTua;\n    public function upsertByTipe(int \$pesertaId, string \$tipe, array \$data): PesertaOrangTua;\n",
            'custom_impl' => "    public function findByPesertaIdAndTipe(int \$pesertaId, string \$tipe): ?PesertaOrangTua { return \$this->model->where('peserta_id', \$pesertaId)->where('tipe', \$tipe)->first(); }\n    public function upsertByTipe(int \$pesertaId, string \$tipe, array \$data): PesertaOrangTua { return \$this->model->updateOrCreate(['peserta_id' => \$pesertaId, 'tipe' => \$tipe], \$data); }\n"
        ],
        'PesertaPeriodik' => [
            'model' => 'App\Models\Peserta\PesertaPeriodik',
            'custom_interface' => "",
            'custom_impl' => ""
        ],
        'PesertaKontak' => [
            'model' => 'App\Models\Peserta\PesertaKontak',
            'custom_interface' => "",
            'custom_impl' => ""
        ],
        'PesertaDokumenPribadi' => [
            'model' => 'App\Models\Peserta\PesertaDokumenPribadi',
            'custom_interface' => "",
            'custom_impl' => ""
        ]
    ],
    'Ppdb' => [
        'PembukaanPpdb' => [
            'model' => 'App\Models\Ppdb\PembukaanPpdb',
            'custom_interface' => "    public function findAktif(): ?PembukaanPpdb;\n    public function toggleStatus(int \$id): bool;\n",
            'custom_impl' => "    public function findAktif(): ?PembukaanPpdb { return \$this->model->where('status', 'buka')->where('selesai', '>=', now())->first(); }\n    public function toggleStatus(int \$id): bool { \$pembukaan = \$this->model->find(\$id); if(\$pembukaan) { \$pembukaan->status = \$pembukaan->status === 'buka' ? 'tutup' : 'buka'; return \$pembukaan->save(); } return false; }\n"
        ],
        'JalurPendaftaran' => [
            'model' => 'App\Models\Ppdb\JalurPendaftaran',
            'custom_interface' => "    public function findAktifByPembukaan(int \$pembukaanId): \Illuminate\Database\Eloquent\Collection;\n",
            'custom_impl' => "    public function findAktifByPembukaan(int \$pembukaanId): \Illuminate\Database\Eloquent\Collection { return \$this->model->where('pembukaan_ppdb_id', \$pembukaanId)->where('status', 'aktif')->get(); }\n"
        ],
        'SyaratPendaftaran' => [
            'model' => 'App\Models\Ppdb\SyaratPendaftaran',
            'custom_interface' => "",
            'custom_impl' => ""
        ],
        'FormulirPendaftaran' => [
            'model' => 'App\Models\Ppdb\FormulirPendaftaran',
            'custom_interface' => "    public function findWithFields(int \$id): ?FormulirPendaftaran;\n",
            'custom_impl' => "    public function findWithFields(int \$id): ?FormulirPendaftaran { return \$this->model->with('formulirField')->find(\$id); }\n"
        ],
        'BiayaRegistrasi' => [
            'model' => 'App\Models\Ppdb\BiayaRegistrasi',
            'custom_interface' => "",
            'custom_impl' => ""
        ],
        'KuotaJurusan' => [
            'model' => 'App\Models\Ppdb\KuotaJurusan',
            'custom_interface' => "    public function incrementTerisi(int \$id): bool;\n    public function decrementTerisi(int \$id): bool;\n",
            'custom_impl' => "    public function incrementTerisi(int \$id): bool { return \$this->model->where('id', \$id)->increment('terisi'); }\n    public function decrementTerisi(int \$id): bool { return \$this->model->where('id', \$id)->decrement('terisi'); }\n"
        ]
    ],
    'Transaksi' => [
        'Pendaftaran' => [
            'model' => 'App\Models\Transaksi\Pendaftaran',
            'custom_interface' => "    public function generateNoPendaftaran(int \$tahunPelajaranId): string;\n    public function updateStatus(int \$id, string \$status, array \$extra = []): bool;\n    public function findByNoPendaftaran(string \$no): ?Pendaftaran;\n",
            'custom_impl' => "    public function generateNoPendaftaran(int \$tahunPelajaranId): string { \$count = \$this->model->where('tahun_pelajaran_id', \$tahunPelajaranId)->count() + 1; return 'PPDB' . date('Y') . str_pad(\$count, 5, '0', STR_PAD_LEFT); }\n    public function updateStatus(int \$id, string \$status, array \$extra = []): bool { return \$this->model->where('id', \$id)->update(array_merge(['status' => \$status], \$extra)); }\n    public function findByNoPendaftaran(string \$no): ?Pendaftaran { return \$this->model->where('no_pendaftaran', \$no)->first(); }\n"
        ],
        'DokumenPeserta' => [
            'model' => 'App\Models\Transaksi\DokumenPeserta',
            'custom_interface' => "    public function findByPendaftaranId(int \$pendaftaranId): \Illuminate\Database\Eloquent\Collection;\n    public function verifikasi(int \$id, string \$status, ?string \$keterangan, int \$userId): bool;\n",
            'custom_impl' => "    public function findByPendaftaranId(int \$pendaftaranId): \Illuminate\Database\Eloquent\Collection { return \$this->model->where('pendaftaran_id', \$pendaftaranId)->get(); }\n    public function verifikasi(int \$id, string \$status, ?string \$keterangan, int \$userId): bool { return \$this->model->where('id', \$id)->update(['status_verifikasi' => \$status, 'keterangan_verifikasi' => \$keterangan, 'verified_by' => \$userId, 'verified_at' => now()]); }\n"
        ],
        'PembayaranPpdb' => [
            'model' => 'App\Models\Transaksi\PembayaranPpdb',
            'custom_interface' => "    public function findByOrderId(string \$orderId): ?PembayaranPpdb;\n    public function updateByOrderId(string \$orderId, array \$data): bool;\n",
            'custom_impl' => "    public function findByOrderId(string \$orderId): ?PembayaranPpdb { return \$this->model->where('order_id', \$orderId)->first(); }\n    public function updateByOrderId(string \$orderId, array \$data): bool { return \$this->model->where('order_id', \$orderId)->update(\$data); }\n"
        ],
        'HasilSeleksi' => [
            'model' => 'App\Models\Transaksi\HasilSeleksi',
            'custom_interface' => "    public function rankingByJalur(int \$jalurId): \Illuminate\Database\Eloquent\Collection;\n",
            'custom_impl' => "    public function rankingByJalur(int \$jalurId): \Illuminate\Database\Eloquent\Collection { return \$this->model->whereHas('pendaftaran', function(\$q) use (\$jalurId) { \$q->where('jalur_pendaftaran_id', \$jalurId); })->orderByDesc('total_nilai')->get(); }\n"
        ]
    ],
    'Master' => [
        'Jurusan' => [
            'model' => 'App\Models\Master\Jurusan',
            'custom_interface' => "",
            'custom_impl' => ""
        ]
    ]
];

$baseDir = __DIR__ . '/app/Repositories';

foreach ($config as $cluster => $entities) {
    if (!is_dir($baseDir . '/' . $cluster)) {
        mkdir($baseDir . '/' . $cluster, 0777, true);
    }

    foreach ($entities as $entity => $data) {
        $interfaceContent = "<?php\n\nnamespace App\Repositories\\$cluster;\n\nuse Illuminate\Database\Eloquent\Builder;\nuse Illuminate\Database\Eloquent\Collection;\nuse {$data['model']};\n\ninterface {$entity}RepositoryInterface\n{\n    public function all(array \$filters = [], array \$with = []): Collection;\n    public function findById(int \$id, array \$with = []): ?{$entity};\n    public function create(array \$data): {$entity};\n    public function update(int \$id, array \$data): {$entity};\n    public function delete(int \$id): bool;\n    public function datatable(array \$filters = []): Builder;\n\n" . $data['custom_interface'] . "}\n";

        $implContent = "<?php\n\nnamespace App\Repositories\\$cluster;\n\nuse Illuminate\Database\Eloquent\Builder;\nuse Illuminate\Database\Eloquent\Collection;\nuse {$data['model']};\n\nclass {$entity}Repository implements {$entity}RepositoryInterface\n{\n    public function __construct(protected {$entity} \$model)\n    {\n    }\n\n    public function all(array \$filters = [], array \$with = []): Collection\n    {\n        \$query = \$this->model->with(\$with);\n        foreach (\$filters as \$key => \$value) {\n            \$query->where(\$key, \$value);\n        }\n        return \$query->get();\n    }\n\n    public function findById(int \$id, array \$with = []): ?{$entity}\n    {\n        return \$this->model->with(\$with)->find(\$id);\n    }\n\n    public function create(array \$data): {$entity}\n    {\n        return \$this->model->create(\$data);\n    }\n\n    public function update(int \$id, array \$data): {$entity}\n    {\n        \$record = \$this->model->find(\$id);\n        if (\$record) {\n            \$record->update(\$data);\n            return \$record;\n        }\n        throw new \Exception(\"Record not found\");\n    }\n\n    public function delete(int \$id): bool\n    {\n        \$record = \$this->model->find(\$id);\n        return \$record ? \$record->delete() : false;\n    }\n\n    public function datatable(array \$filters = []): Builder\n    {\n        \$query = \$this->model->query();\n        foreach (\$filters as \$key => \$value) {\n            \$query->where(\$key, \$value);\n        }\n        return \$query;\n    }\n\n" . $data['custom_impl'] . "}\n";

        file_put_contents($baseDir . "/$cluster/{$entity}RepositoryInterface.php", $interfaceContent);
        file_put_contents($baseDir . "/$cluster/{$entity}Repository.php", $implContent);
    }
}
echo "All 34 files generated successfully.\n";
