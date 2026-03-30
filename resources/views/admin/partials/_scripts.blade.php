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
@stack('vendor-scripts')

<!-- Global Handler -->
@include('admin.partials._global_handler')

<!-- Page Scripts -->
@stack('scripts')

<!-- Layout Settings -->
<script>layout_change('light');</script>
<script>layout_caption_change('true');</script>
<script>layout_rtl_change('false');</script>
<script>preset_change('preset-1');</script>
