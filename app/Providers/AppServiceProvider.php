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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
