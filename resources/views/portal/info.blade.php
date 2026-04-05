@extends('layouts.portal')
@section('title', 'Informasi PPDB')
@section('content')
<div class="row justify-content-center py-4">
    <div class="col-lg-8">
        <div class="portal-card p-4">
            <h1 class="fs-4 fw-bold text-success mb-4"><i class="bi bi-info-circle me-2"></i>Informasi PPDB 2026/2027</h1>
            <p class="text-muted">Halaman ini berisi informasi lengkap tentang tata cara, persyaratan, dan alur pendaftaran PPDB. Konten akan dilengkapi oleh admin sekolah.</p>
            <a href="{{ route('ppdb.beranda') }}" class="btn btn-outline-success mt-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
