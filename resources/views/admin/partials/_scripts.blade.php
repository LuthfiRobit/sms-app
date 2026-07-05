<!-- [Required Js] -->
<!-- Legacy: jQuery First -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<!-- Template Plugins -->
<script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/sekolah-refaktor-template/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/sekolah-refaktor-template/js/fonts/custom-font.js') }}"></script>
<script src="{{ asset('assets/sekolah-refaktor-template/js/script.js') }}"></script>
<script src="{{ asset('assets/sekolah-refaktor-template/js/theme.js') }}"></script>

<!-- Legacy: DataTables JS (Global) -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Vendor Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 4000,
};
</script>
@stack('vendor-scripts')

<!-- Global Handler -->
@include('admin.partials._global_handler')

<!-- Page Scripts -->
@stack('scripts')

<script>
function switchLembaga(lembagaId) {
    $.post('{{ route('admin.switch-lembaga') }}', {
        _token: '{{ csrf_token() }}',
        lembaga_id: lembagaId,
    }, function (res) {
        if (res.status) {
            window.location.reload();
        } else {
            toastr.error(res.message ?? 'Gagal mengganti lembaga');
        }
    });
}
</script>

<!-- Coming-soon badge: inject into placeholder menu items (href="#") -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.pc-navbar a.pc-link[href="#"]').forEach(function(link) {
        var badge = document.createElement('span');
        badge.style.cssText = 'font-size:0.6em;font-weight:600;color:#6c757d;background:#f1f3f4;border:1px solid #d1d5db;border-radius:20px;padding:1px 7px;position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;';
        badge.textContent = 'Segera';
        link.appendChild(badge);
    });
});
</script>

<!-- Layout Settings -->
<script>layout_change('light');</script>
<script>layout_caption_change('true');</script>
<script>layout_rtl_change('false');</script>
<script>preset_change('preset-1');</script>

<script>
    function updateNotificationBadge() {
        $.ajax({
            url: "{{ route('admin.notifikasi.unread-count') }}",
            method: 'GET',
            success: function(res) {
                // Update badge count
                const count = res.unread_count;
                const badge = $('#notification-badge-count');
                
                if (count > 0) {
                    badge.text(count).show();
                } else {
                    badge.hide();
                }

                // Update dropdown list
                const container = $('#notification-list-container');
                if (res.latest.length > 0) {
                    let html = '';
                    res.latest.forEach(notif => {
                        html += `
                        <a href="{{ url('admin/notifikasi') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="user-avtar bg-light-primary"><i class="bi bi-bell"></i></div>
                                </div>
                                <div class="flex-grow-1 ms-1">
                                    <span class="float-end text-muted small">${notif.waktu}</span>
                                    <h5 class="mb-1">${notif.judul}</h5>
                                    <p class="text-body small mb-0">${notif.isi}</p>
                                </div>
                            </div>
                        </a>`;
                    });
                    container.html(html);
                } else {
                    container.html('<div class="list-group-item text-center py-4 text-muted small">Tidak ada notifikasi baru</div>');
                }
            }
        });
    }

    $(document).ready(function() {
        // Initial load
        updateNotificationBadge();
        
        // Poll every 30 seconds
        setInterval(updateNotificationBadge, 30000);
    });
</script>
