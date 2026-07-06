<?php

namespace App\Providers;

use App\Dashboard\DashboardWidgetRegistry;
use App\Dashboard\Widgets\Ppdb\ActionQueueWidget;
use App\Dashboard\Widgets\Ppdb\PembukaanAktifWidget;
use App\Dashboard\Widgets\Ppdb\StatWidget;
use App\Dashboard\Widgets\Ppdb\TrenWidget;
use App\Repositories\Akademik\AbsensiGuruRepository;
use App\Repositories\Akademik\AbsensiGuruRepositoryInterface;
use App\Repositories\Akademik\AbsensiRepository;
use App\Repositories\Akademik\AbsensiRepositoryInterface;
use App\Repositories\Akademik\AkademikSettingRepository;
use App\Repositories\Akademik\AkademikSettingRepositoryInterface;
use App\Repositories\Akademik\KalenderLiburRepository;
use App\Repositories\Akademik\KalenderLiburRepositoryInterface;
use App\Repositories\Akademik\MateriBelajarRepository;
use App\Repositories\Akademik\MateriBelajarRepositoryInterface;
use App\Repositories\Akademik\NilaiHarianLogRepository;
use App\Repositories\Akademik\NilaiHarianLogRepositoryInterface;
use App\Repositories\Akademik\NilaiRepository;
use App\Repositories\Akademik\NilaiRepositoryInterface;
use App\Repositories\Akademik\PengajuanIzinGuruRepository;
use App\Repositories\Akademik\PengajuanIzinGuruRepositoryInterface;
use App\Repositories\Akademik\PengajuanRaportRepository;
use App\Repositories\Akademik\PengajuanRaportRepositoryInterface;
use App\Repositories\Akademik\PerangkatMengajarRepository;
use App\Repositories\Akademik\PerangkatMengajarRepositoryInterface;
use App\Repositories\Akademik\RppBagianRepository;
use App\Repositories\Akademik\RppBagianRepositoryInterface;
use App\Repositories\Akademik\RppMasterOpsiRepository;
use App\Repositories\Akademik\RppMasterOpsiRepositoryInterface;
use App\Repositories\Akademik\RppPoinRepository;
use App\Repositories\Akademik\RppPoinRepositoryInterface;
use App\Repositories\Akademik\RppRepository;
use App\Repositories\Akademik\RppRepositoryInterface;
use App\Repositories\DeviceTokenRepository;
use App\Repositories\DeviceTokenRepositoryInterface;
use App\Repositories\Kinerja\KpiRepository;
use App\Repositories\Kinerja\KpiRepositoryInterface;
use App\Repositories\Master\GuruRepository;
// Cluster Peserta
use App\Repositories\Master\GuruRepositoryInterface;
use App\Repositories\Master\JadwalKbmRepository;
use App\Repositories\Master\JadwalKbmRepositoryInterface;
use App\Repositories\Master\JurusanRepository;
use App\Repositories\Master\JurusanRepositoryInterface;
use App\Repositories\Master\KurikulumRepository;
use App\Repositories\Master\KurikulumRepositoryInterface;
use App\Repositories\Master\LembagaRepository;
use App\Repositories\Master\LembagaRepositoryInterface;
use App\Repositories\Master\MataPelajaranRepository;
use App\Repositories\Master\MataPelajaranRepositoryInterface;
use App\Repositories\Master\ModelPembelajaranRepository;
use App\Repositories\Master\ModelPembelajaranRepositoryInterface;
use App\Repositories\Master\ProfilSekolahRepository;
// Cluster Ppdb
use App\Repositories\Master\ProfilSekolahRepositoryInterface;
use App\Repositories\Master\RombelRepository;
use App\Repositories\Master\RombelRepositoryInterface;
use App\Repositories\Master\SemesterRepository;
use App\Repositories\Master\SemesterRepositoryInterface;
use App\Repositories\Master\TahunPelajaranRepository;
use App\Repositories\Master\TahunPelajaranRepositoryInterface;
use App\Repositories\Peserta\PesertaAlamatRepository;
use App\Repositories\Peserta\PesertaAlamatRepositoryInterface;
use App\Repositories\Peserta\PesertaDokumenPribadiRepository;
use App\Repositories\Peserta\PesertaDokumenPribadiRepositoryInterface;
use App\Repositories\Peserta\PesertaKontakRepository;
use App\Repositories\Peserta\PesertaKontakRepositoryInterface;
use App\Repositories\Peserta\PesertaOrangTuaRepository;
// Cluster Transaksi
use App\Repositories\Peserta\PesertaOrangTuaRepositoryInterface;
use App\Repositories\Peserta\PesertaPeriodikRepository;
use App\Repositories\Peserta\PesertaPeriodikRepositoryInterface;
use App\Repositories\Peserta\PesertaRepository;
use App\Repositories\Peserta\PesertaRepositoryInterface;
use App\Repositories\Ppdb\BiayaRegistrasiRepository;
use App\Repositories\Ppdb\BiayaRegistrasiRepositoryInterface;
use App\Repositories\Ppdb\FormulirPendaftaranRepository;
use App\Repositories\Ppdb\FormulirPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\JadwalPendaftaranRepository;
// Cluster Master
use App\Repositories\Ppdb\JadwalPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\JalurPendaftaranRepository;
use App\Repositories\Ppdb\JalurPendaftaranRepositoryInterface;
use App\Repositories\Ppdb\KuotaJurusanRepository;
use App\Repositories\Ppdb\KuotaJurusanRepositoryInterface;
use App\Repositories\Ppdb\PembukaanPpdbRepository;
use App\Repositories\Ppdb\PembukaanPpdbRepositoryInterface;
use App\Repositories\Ppdb\SyaratPendaftaranRepository;
use App\Repositories\Ppdb\SyaratPendaftaranRepositoryInterface;
use App\Repositories\ProgramKerja\ProgramKerjaRepository;
use App\Repositories\ProgramKerja\ProgramKerjaRepositoryInterface;
use App\Repositories\Rbac\PermissionRepository;
use App\Repositories\Rbac\PermissionRepositoryInterface;
use App\Repositories\Rbac\RoleRepository;
use App\Repositories\Rbac\RoleRepositoryInterface;
use App\Repositories\Rbac\UserRepository;
use App\Repositories\Rbac\UserRepositoryInterface;
use App\Repositories\Transaksi\DokumenPesertaRepository;
use App\Repositories\Transaksi\DokumenPesertaRepositoryInterface;
use App\Repositories\Transaksi\HasilSeleksiRepository;
use App\Repositories\Transaksi\HasilSeleksiRepositoryInterface;
use App\Repositories\Transaksi\PembayaranPpdbRepository;
use App\Repositories\Transaksi\PembayaranPpdbRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranFieldValueRepository;
use App\Repositories\Transaksi\PendaftaranFieldValueRepositoryInterface;
use App\Repositories\Transaksi\PendaftaranRepository;
use App\Repositories\Transaksi\PendaftaranRepositoryInterface;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(LembagaRepositoryInterface::class, LembagaRepository::class);
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
        $this->app->bind(JadwalPendaftaranRepositoryInterface::class, JadwalPendaftaranRepository::class);

        // Cluster Transaksi
        $this->app->bind(PendaftaranRepositoryInterface::class, PendaftaranRepository::class);
        $this->app->bind(DokumenPesertaRepositoryInterface::class, DokumenPesertaRepository::class);
        $this->app->bind(PembayaranPpdbRepositoryInterface::class, PembayaranPpdbRepository::class);
        $this->app->bind(HasilSeleksiRepositoryInterface::class, HasilSeleksiRepository::class);
        $this->app->bind(PendaftaranFieldValueRepositoryInterface::class, PendaftaranFieldValueRepository::class);

        // Cluster Master
        $this->app->bind(JurusanRepositoryInterface::class, JurusanRepository::class);
        $this->app->bind(MataPelajaranRepositoryInterface::class, MataPelajaranRepository::class);
        $this->app->bind(RombelRepositoryInterface::class, RombelRepository::class);
        $this->app->bind(GuruRepositoryInterface::class, GuruRepository::class);
        $this->app->bind(JadwalKbmRepositoryInterface::class, JadwalKbmRepository::class);
        $this->app->bind(ModelPembelajaranRepositoryInterface::class, ModelPembelajaranRepository::class);

        // Cluster Akademik
        $this->app->bind(PerangkatMengajarRepositoryInterface::class, PerangkatMengajarRepository::class);
        $this->app->bind(MateriBelajarRepositoryInterface::class, MateriBelajarRepository::class);
        $this->app->bind(RppRepositoryInterface::class, RppRepository::class);
        $this->app->bind(RppBagianRepositoryInterface::class, RppBagianRepository::class);
        $this->app->bind(RppPoinRepositoryInterface::class, RppPoinRepository::class);
        $this->app->bind(RppMasterOpsiRepositoryInterface::class, RppMasterOpsiRepository::class);
        $this->app->bind(AbsensiRepositoryInterface::class, AbsensiRepository::class);
        $this->app->bind(AbsensiGuruRepositoryInterface::class, AbsensiGuruRepository::class);
        $this->app->bind(DeviceTokenRepositoryInterface::class, DeviceTokenRepository::class);
        $this->app->bind(NilaiRepositoryInterface::class, NilaiRepository::class);
        $this->app->bind(NilaiHarianLogRepositoryInterface::class, NilaiHarianLogRepository::class);
        $this->app->bind(AkademikSettingRepositoryInterface::class, AkademikSettingRepository::class);
        $this->app->bind(PengajuanRaportRepositoryInterface::class, PengajuanRaportRepository::class);
        $this->app->bind(KalenderLiburRepositoryInterface::class, KalenderLiburRepository::class);
        $this->app->bind(PengajuanIzinGuruRepositoryInterface::class, PengajuanIzinGuruRepository::class);

        // Cluster Kinerja
        $this->app->bind(KpiRepositoryInterface::class, KpiRepository::class);

        // Cluster Program Kerja
        $this->app->bind(ProgramKerjaRepositoryInterface::class, ProgramKerjaRepository::class);

        // Default binding untuk active_lembaga_id agar tidak throw BindingResolutionException
        $this->app->bind('active_lembaga_id', function () {
            return session('active_lembaga_id');
        });

        // Dashboard Widget Registry
        $this->app->singleton(DashboardWidgetRegistry::class, function () {
            $registry = new DashboardWidgetRegistry;

            // ── Modul PPDB (aktif) ──────────────────────────────────────────
            $registry->register(
                new StatWidget,
                new TrenWidget,
                new ActionQueueWidget,
                new PembukaanAktifWidget,
            );

            // ── Modul Akademik (uncomment ketika siap) ─────────────────────
            // $registry->register(
            //     new \App\Dashboard\Widgets\Akademik\JadwalHariIniWidget(),
            //     new \App\Dashboard\Widgets\Akademik\AbsensiWidget(),
            // );

            // ── Modul Keuangan (uncomment ketika siap) ─────────────────────
            // $registry->register(
            //     new \App\Dashboard\Widgets\Keuangan\RekapSppWidget(),
            // );

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
