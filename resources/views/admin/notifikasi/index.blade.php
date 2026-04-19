@extends('admin.layouts.app')

@section('title', 'Notifikasi Saya')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Notifikasi</h5>
                <div>
                    <button id="btnMarkAllRead" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check-all me-1"></i> Tandai Semua Dibaca
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex gap-2">
                    <a href="{{ route('admin.notifikasi.index') }}" 
                       class="btn btn-sm {{ !request()->has('unread') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Semua Notifikasi
                    </a>
                    <a href="{{ route('admin.notifikasi.index', ['unread' => 1]) }}" 
                       class="btn btn-sm {{ request()->has('unread') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Belum Dibaca
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Notifikasi</th>
                                <th style="width: 150px;">Waktu</th>
                                <th style="width: 150px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notifikasi as $notif)
                                <tr class="{{ !$notif->dibaca ? 'table-info-light' : '' }}" id="notif-row-{{ $notif->id }}">
                                    <td>
                                        <div class="user-avtar bg-light-primary"><i class="bi bi-bell"></i></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $notif->judul }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 500px;">
                                            {{ $notif->isi }}
                                        </div>
                                    </td>
                                    <td class="small text-muted">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </td>
                                    <td class="text-center">
                                        @if(!$notif->dibaca)
                                            <button class="btn btn-icon btn-sm btn-light-success btnMarkRead" 
                                                    data-id="{{ $notif->id }}" title="Tandai Dibaca">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @else
                                            <span class="badge bg-light-secondary text-secondary">Sudah Dibaca</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                                        Belum ada notifikasi untuk Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $notifikasi->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-info-light {
        background-color: rgba(63, 135, 245, 0.05) !important;
        border-left: 4px solid #3F87F5;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mark Single Read
        $('.btnMarkRead').on('click', function() {
            const id = $(this).data('id');
            const btn = $(this);

            $.post(`{{ url('admin/notifikasi') }}/${id}/read`, function(res) {
                if (res.success) {
                    $(`#notif-row-${id}`).removeClass('table-info-light');
                    btn.parent().html('<span class="badge bg-light-secondary text-secondary">Sudah Dibaca</span>');
                    
                    // Trigger refresh count in navbar
                    if (typeof updateNotificationBadge === 'function') {
                        updateNotificationBadge();
                    }
                }
            });
        });

        // Mark All Read
        $('#btnMarkAllRead').on('click', function() {
            if (!confirm('Tandai semua notifikasi sebagai sudah dibaca?')) return;

            $.post(`{{ route('admin.notifikasi.mark-all-read') }}`, function(res) {
                if (res.success) {
                    window.location.reload();
                }
            });
        });
    });
</script>
@endpush
