<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Rbac\RoleRepositoryInterface;
use App\Repositories\Rbac\RoleRepository;
use App\Repositories\Rbac\PermissionRepositoryInterface;
use App\Repositories\Rbac\PermissionRepository;
use App\Repositories\Rbac\UserRepositoryInterface;
use App\Repositories\Rbac\UserRepository;
use App\Repositories\Master\TahunPelajaranRepositoryInterface;
use App\Repositories\Master\TahunPelajaranRepository;
use App\Repositories\Master\SemesterRepositoryInterface;
use App\Repositories\Master\SemesterRepository;
use App\Repositories\Master\ProfilSekolahRepositoryInterface;
use App\Repositories\Master\ProfilSekolahRepository;
use App\Repositories\Master\KurikulumRepositoryInterface;
use App\Repositories\Master\KurikulumRepository;

// Cluster Peserta
use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Peserta\PesertaRepository;
use App\Repositories\Peserta\PesertaAlamatRepositoryInterface;
use App\Repositories\Peserta\PesertaAlamatRepository;
use App\Repositories\Peserta\PesertaOrangTuaRepositoryInterface;
use App\Repositories\Peserta\PesertaOrangTuaRepository;
use App\Repositories\Peserta\PesertaPeriodikRepositoryInterface;
use App\Repositories\Peserta\PesertaRepository as PesertaPeriodikRepository;
use App\Repositories\Peserta\PesertaKontakRepositoryInterface;
use App\Repositories\Peserta\PesertaRepository as PesertaKontakRepository;
use App\Repositories\Peserta\PesertaDokumenPribadiRepositoryInterface;
use App\Repositories\Peserta\PesertaRepository as PesertaDokumenPribadiRepository;

// Cluster Ppdb
use App\Repositories\Ppdb\PembukaanPpdbRepositoryInterface;
use App\Repositories\Ppdb\PembukaanPpdbRepository;
use App\Repositories\Ppdb\JalurPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\JalurPendaftaranRepository;
use App\Repositories\Ppdb\SyaratPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\SyaratPendaftaranRepository;
use App\Repositories\Ppdb\FormulirPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\FormulirPendaftaranRepository;
use App\Repositories\Ppdb\BiayaRegistrasiRepositoryInterface;
use App\Repositories\Ppdb\BiayaRegistrasiRepository;
use App\Repositories\Ppdb\KuotaJurusanRepositoryInterface;
use App\Repositories\Ppdb\KuotaJurusanRepository;

// Cluster Transaksi
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranRepository;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepository;
use App\Repositories\Transaksi\PembayaranPpdbRepositoryInterface;
use App\Repositories\Transaksi\PembayaranPpdbRepository;
use App\Repositories\Transaksi\HasilSeleksiRepositoryInterface;
use App\Repositories\Transaksi\HasilSeleksiRepository;

// Cluster Master
use App\Repositories\Master\JurusanRepositoryInterface;
use App\Repositories\Master\JurusanRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TahunPelajaranRepositoryInterface::class, TahunPelajaranRepository::class);
        $this->app->bind(SemesterRepositoryInterface::class, SemesterRepository::class);
        $this->app->bind(ProfilSekolahRepositoryInterface::class, ProfilSekolahRepository::class);
        $this->app->bind(KurikulumRepositoryInterface::class, KurikulumRepository::class);

        // Cluster Peserta
        $this->app->bind(PesertaRepositoryInterface::class, PesertaRepository::class);
        $this->app->bind(PesertaAlamatRepositoryInterface::class, PesertaAlamatRepository::class);
        $this->app->bind(PesertaOrangTuaRepositoryInterface::class, PesertaOrangTuaRepository::class);
        $this->app->bind(PesertaPeriodikRepositoryInterface::class, PesertaPeriodikRepository::class);
        $this->app->bind(PesertaKontakRepositoryInterface::class, PesertaKontakRepository::class);
        $this->app->bind(PesertaDokumenPribadiRepositoryInterface::class, PesertaDokumenPribadiRepository::class);

        // Cluster Ppdb
        $this->app->bind(PembukaanPpdbRepositoryInterface::class, PembukaanPpdbRepository::class);
        $this->app->bind(JalurPendaftaranRepositoryInterface::class, JalurPendaftaranRepository::class);
        $this->app->bind(SyaratPendaftaranRepositoryInterface::class, SyaratPendaftaranRepository::class);
        $this->app->bind(FormulirPendaftaranRepositoryInterface::class, FormulirPendaftaranRepository::class);
        $this->app->bind(BiayaRegistrasiRepositoryInterface::class, BiayaRegistrasiRepository::class);
        $this->app->bind(KuotaJurusanRepositoryInterface::class, KuotaJurusanRepository::class);

        // Cluster Transaksi
        $this->app->bind(PendaftaranRepositoryInterface::class, PendaftaranRepository::class);
        $this->app->bind(DokumenPesertaRepositoryInterface::class, DokumenPesertaRepository::class);
        $this->app->bind(PembayaranPpdbRepositoryInterface::class, PembayaranPpdbRepository::class);
        $this->app->bind(HasilSeleksiRepositoryInterface::class, HasilSeleksiRepository::class);

        // Cluster Master
        $this->app->bind(JurusanRepositoryInterface::class, JurusanRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
