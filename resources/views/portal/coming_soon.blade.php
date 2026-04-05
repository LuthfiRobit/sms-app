@extends('layouts.portal')
@section('title', $fitur ?? 'Segera Hadir')
@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-6 text-center">
        <div class="portal-card p-5">
            <div style="font-size:4rem;margin-bottom:16px">🚧</div>
            <h2 class="fw-bold text-success mb-2">{{ $fitur ?? 'Fitur' }}</h2>
            <p class="text-muted mb-4">Fitur ini sedang dalam pengembangan dan akan segera tersedia.</p>
            <a href="{{ route('ppdb.dashboard') }}" class="btn btn-success">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
