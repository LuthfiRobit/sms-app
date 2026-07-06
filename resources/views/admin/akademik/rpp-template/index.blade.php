@extends('admin.layouts.app')
@section('title', 'Kelola Bagian & Poin RPP')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-diagram-3 me-2"></i>Kelola Bagian &amp; Poin RPP</h5>
                    <small class="text-muted">Susun templat RPP di sini — tambah/ubah/nonaktifkan bagian dan poin tanpa perlu developer.</small>
                </div>
                <button class="btn btn-sm btn-primary" id="btn-tambah-bagian"><i class="bi bi-plus-lg me-1"></i>Tambah Bagian</button>
            </div>
            <div class="card-body">
                <div id="bagian-list">
                    @foreach($bagianList as $bagian)
                    <div class="card mb-3 bagian-card" data-id="{{ $bagian->id }}">
                        <div class="card-header d-flex justify-content-between align-items-center {{ $bagian->status === 'nonaktif' ? 'bg-light text-muted' : 'bg-light-subtle' }}">
                            <div>
                                <strong>{{ $bagian->nama }}</strong>
                                @if($bagian->status === 'nonaktif')<span class="badge bg-secondary ms-1">Nonaktif</span>@endif
                            </div>
                            <div>
                                <button class="btn btn-xs btn-icon btn-light-secondary me-1 btn-tambah-poin" data-bagian-id="{{ $bagian->id }}" title="Tambah Poin"><i class="bi bi-plus-lg"></i></button>
                                <button class="btn btn-xs btn-icon btn-light-primary me-1 btn-edit-bagian" data-id="{{ $bagian->id }}" data-nama="{{ $bagian->nama }}" data-urutan="{{ $bagian->urutan }}" data-status="{{ $bagian->status }}" title="Edit Bagian"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-xs btn-icon btn-light-danger btn-hapus-bagian" data-id="{{ $bagian->id }}" data-nama="{{ $bagian->nama }}" title="Hapus Bagian"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">Urutan</th>
                                        <th>Label</th>
                                        <th width="12%">Kode</th>
                                        <th width="14%">Tipe</th>
                                        <th width="8%">Wajib</th>
                                        <th width="8%">Status</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bagian->poin as $poin)
                                    <tr>
                                        <td>{{ $poin->urutan }}</td>
                                        <td>{{ $poin->label }}</td>
                                        <td><code>{{ $poin->kode }}</code></td>
                                        <td><span class="badge bg-light-info text-info">{{ $poin->tipe }}</span></td>
                                        <td>{{ $poin->is_required ? 'Ya' : '-' }}</td>
                                        <td>{{ $poin->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</td>
                                        <td>
                                            <button class="btn btn-xs btn-icon btn-light-primary btn-edit-poin"
                                                data-id="{{ $poin->id }}"
                                                data-rpp_bagian_id="{{ $poin->rpp_bagian_id }}"
                                                data-kode="{{ $poin->kode }}"
                                                data-label="{{ $poin->label }}"
                                                data-tipe="{{ $poin->tipe }}"
                                                data-kolom1_label="{{ $poin->kolom1_label }}"
                                                data-kolom2_label="{{ $poin->kolom2_label }}"
                                                data-master_kategori="{{ $poin->master_kategori }}"
                                                data-is_required="{{ $poin->is_required ? 1 : 0 }}"
                                                data-urutan="{{ $poin->urutan }}"
                                                data-status="{{ $poin->status }}"
                                                title="Edit"><i class="bi bi-pencil"></i></button>
                                            @if($poin->tipe === 'pilih_master')
                                            <button class="btn btn-xs btn-icon btn-light-secondary btn-kelola-opsi" data-kategori="{{ $poin->master_kategori }}" data-label="{{ $poin->label }}" title="Kelola Opsi"><i class="bi bi-list-check"></i></button>
                                            @endif
                                            <button class="btn btn-xs btn-icon btn-light-danger btn-hapus-poin" data-id="{{ $poin->id }}" data-label="{{ $poin->label }}" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="text-center text-muted small py-3">Belum ada poin di bagian ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Bagian --}}
<div class="modal fade" id="modal-bagian" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-bagian">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-bagian-title">Tambah Bagian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bagian-id">
                    <div class="mb-3">
                        <label class="form-label">Nama Bagian</label>
                        <input type="text" class="form-control" id="bagian-nama" maxlength="150" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Urutan</label>
                            <input type="number" class="form-control" id="bagian-urutan" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="bagian-status">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Poin --}}
<div class="modal fade" id="modal-poin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-poin">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-poin-title">Tambah Poin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="poin-id">
                    <input type="hidden" id="poin-rpp_bagian_id">
                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control" id="poin-label" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode (unik, huruf/angka/dash/underscore)</label>
                        <input type="text" class="form-control" id="poin-kode" maxlength="100" pattern="[A-Za-z0-9_\-]+" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <select class="form-select" id="poin-tipe">
                            <option value="teks">Teks singkat</option>
                            <option value="teks_panjang">Teks panjang (paragraf)</option>
                            <option value="daftar_poin">Daftar poin (satu per baris)</option>
                            <option value="pasangan_kolom">Baris 2 kolom berulang</option>
                            <option value="pilih_master">Pilihan dari daftar (checkbox)</option>
                            <option value="model_pembelajaran">Blok Inti (ikut Model Pembelajaran)</option>
                        </select>
                        <div class="form-text" id="poin-tipe-help"></div>
                    </div>
                    <div class="row g-3 poin-pasangan-kolom-fields d-none">
                        <div class="col-6">
                            <label class="form-label">Label Kolom 1</label>
                            <input type="text" class="form-control" id="poin-kolom1_label" maxlength="100">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Label Kolom 2</label>
                            <input type="text" class="form-control" id="poin-kolom2_label" maxlength="100">
                        </div>
                    </div>
                    <div class="mb-3 poin-pilih-master-fields d-none">
                        <label class="form-label">Kategori Master Opsi</label>
                        <input type="text" class="form-control" id="poin-master_kategori" maxlength="100" placeholder="mis. dimensi_profil_lulusan">
                        <div class="form-text">Setelah disimpan, isi daftar opsinya lewat tombol "Kelola Opsi" pada tabel poin.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label">Urutan</label>
                            <input type="number" class="form-control" id="poin-urutan" value="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="poin-status">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label d-block">Wajib diisi</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="poin-is_required">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Kelola Opsi --}}
<div class="modal fade" id="modal-opsi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kelola Opsi — <span id="opsi-label-kategori"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="opsi-kategori">
                <table class="table table-sm" id="opsi-table">
                    <thead><tr><th>Nama</th><th width="70">Urutan</th><th width="80">Status</th><th width="40"></th></tr></thead>
                    <tbody></tbody>
                </table>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="opsi-baru-nama" placeholder="Nama opsi baru">
                    <button class="btn btn-sm btn-info text-nowrap" id="btn-tambah-opsi"><i class="bi bi-plus"></i> Tambah</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const URLS = {
    bagianStore: '{{ route('admin.akademik.rpp-template.bagian.store') }}',
    bagianUpdate: (id) => `/admin/akademik/rpp-template/bagian/${id}`,
    bagianDestroy: (id) => `/admin/akademik/rpp-template/bagian/${id}`,
    poinStore: '{{ route('admin.akademik.rpp-template.poin.store') }}',
    poinUpdate: (id) => `/admin/akademik/rpp-template/poin/${id}`,
    poinDestroy: (id) => `/admin/akademik/rpp-template/poin/${id}`,
    opsiIndex: (kategori) => `/admin/akademik/rpp-template/opsi/${kategori}`,
    opsiStore: '{{ route('admin.akademik.rpp-template.opsi.store') }}',
    opsiUpdate: (id) => `/admin/akademik/rpp-template/opsi/${id}`,
    opsiDestroy: (id) => `/admin/akademik/rpp-template/opsi/${id}`,
};

function reload() { window.location.reload(); }

// ── Bagian ───────────────────────────────────────────────────────────────
$('#btn-tambah-bagian').on('click', function () {
    $('#form-bagian')[0].reset();
    $('#bagian-id').val('');
    $('#modal-bagian-title').text('Tambah Bagian');
    new bootstrap.Modal('#modal-bagian').show();
});

$(document).on('click', '.btn-edit-bagian', function () {
    var d = $(this).data();
    $('#bagian-id').val(d.id);
    $('#bagian-nama').val(d.nama);
    $('#bagian-urutan').val(d.urutan);
    $('#bagian-status').val(d.status);
    $('#modal-bagian-title').text('Edit Bagian');
    new bootstrap.Modal('#modal-bagian').show();
});

$('#form-bagian').on('submit', function (e) {
    e.preventDefault();
    var id = $('#bagian-id').val();
    var data = {
        _token: '{{ csrf_token() }}',
        nama: $('#bagian-nama').val(),
        urutan: $('#bagian-urutan').val(),
        status: $('#bagian-status').val(),
    };
    if (id) data._method = 'PUT';

    $.ajax({
        url: id ? URLS.bagianUpdate(id) : URLS.bagianStore,
        type: 'POST',
        data: data,
        success: function (res) { toastr.success(res.message); reload(); },
        error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menyimpan.'); },
    });
});

$(document).on('click', '.btn-hapus-bagian', function () {
    var id = $(this).data('id'), nama = $(this).data('nama');
    Swal.fire({ title: 'Hapus Bagian?', text: '"' + nama + '" akan dihapus beserta poin di dalamnya.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', confirmButtonColor: '#d33' })
        .then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url: URLS.bagianDestroy(id), type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) { toastr.success(res.message); reload(); },
                error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menghapus.'); },
            });
        });
});

// ── Poin ─────────────────────────────────────────────────────────────────
var TIPE_HELP = {
    teks: 'Kolom isian singkat 1 baris — cocok untuk nilai pendek (kode, label, satu kalimat).',
    teks_panjang: 'Kolom dengan editor teks kaya (bisa tebal/miring/daftar) — cocok untuk paragraf penjelasan panjang.',
    daftar_poin: 'Guru mengisi beberapa butir singkat, satu per baris (Enter = butir baru). Jumlah tidak dibatasi. Kurang cocok untuk butir yang kalimatnya panjang (berisiko kepotong salah Enter) — pakai "Baris 2 Kolom Berulang" atau pisahkan jadi beberapa poin kalau itu masalahnya.',
    pasangan_kolom: 'Baris berulang 2 kolom (isi "Label Kolom 1" & "Label Kolom 2" di bawah untuk judul kolomnya). Guru bisa tambah baris sebanyak dibutuhkan lewat tombol "Tambah Baris" — aman untuk teks panjang karena tiap baris kotak sendiri.',
    pilih_master: 'Guru memilih satu/lebih dari daftar opsi baku (checkbox). Isi "Kategori Master Opsi" di bawah dengan kode bebas (mis. dimensi_profil_lulusan) — setelah poin ini disimpan, isi daftar opsinya lewat tombol "Kelola Opsi" di tabel poin.',
    model_pembelajaran: 'Penanda khusus untuk blok Inti — tidak perlu isian tambahan. Isinya (tahapan sintaks) otomatis mengikuti Model Pembelajaran yang dipilih guru saat mengisi RPP. Cukup satu poin bertipe ini per templat, biasanya di bagian Pengalaman Belajar.',
};

function toggleTipeFields() {
    var tipe = $('#poin-tipe').val();
    $('.poin-pasangan-kolom-fields').toggleClass('d-none', tipe !== 'pasangan_kolom');
    $('.poin-pilih-master-fields').toggleClass('d-none', tipe !== 'pilih_master');
    $('#poin-tipe-help').text(TIPE_HELP[tipe] || '');
}
$('#poin-tipe').on('change', toggleTipeFields);

$(document).on('click', '.btn-tambah-poin', function () {
    $('#form-poin')[0].reset();
    $('#poin-id').val('');
    $('#poin-rpp_bagian_id').val($(this).data('bagian-id'));
    $('#modal-poin-title').text('Tambah Poin');
    toggleTipeFields();
    new bootstrap.Modal('#modal-poin').show();
});

$(document).on('click', '.btn-edit-poin', function () {
    var d = $(this).data();
    $('#poin-id').val(d.id);
    $('#poin-rpp_bagian_id').val(d.rpp_bagian_id);
    $('#poin-label').val(d.label);
    $('#poin-kode').val(d.kode);
    $('#poin-tipe').val(d.tipe);
    $('#poin-kolom1_label').val(d.kolom1_label);
    $('#poin-kolom2_label').val(d.kolom2_label);
    $('#poin-master_kategori').val(d.master_kategori);
    $('#poin-urutan').val(d.urutan);
    $('#poin-status').val(d.status);
    $('#poin-is_required').prop('checked', d.is_required == 1);
    $('#modal-poin-title').text('Edit Poin');
    toggleTipeFields();
    new bootstrap.Modal('#modal-poin').show();
});

$('#form-poin').on('submit', function (e) {
    e.preventDefault();
    var id = $('#poin-id').val();
    var data = {
        _token: '{{ csrf_token() }}',
        rpp_bagian_id: $('#poin-rpp_bagian_id').val(),
        label: $('#poin-label').val(),
        kode: $('#poin-kode').val(),
        tipe: $('#poin-tipe').val(),
        kolom1_label: $('#poin-kolom1_label').val(),
        kolom2_label: $('#poin-kolom2_label').val(),
        master_kategori: $('#poin-master_kategori').val(),
        urutan: $('#poin-urutan').val(),
        status: $('#poin-status').val(),
        is_required: $('#poin-is_required').is(':checked') ? 1 : 0,
    };
    if (id) data._method = 'PUT';

    $.ajax({
        url: id ? URLS.poinUpdate(id) : URLS.poinStore,
        type: 'POST',
        data: data,
        success: function (res) { toastr.success(res.message); reload(); },
        error: function (xhr) {
            var msg = xhr.responseJSON?.message ?? 'Gagal menyimpan.';
            if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            toastr.error(msg);
        },
    });
});

$(document).on('click', '.btn-hapus-poin', function () {
    var id = $(this).data('id'), label = $(this).data('label');
    Swal.fire({ title: 'Hapus Poin?', text: '"' + label + '" akan dihapus.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', confirmButtonColor: '#d33' })
        .then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url: URLS.poinDestroy(id), type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function (res) { toastr.success(res.message); reload(); },
                error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menghapus.'); },
            });
        });
});

// ── Opsi Master ──────────────────────────────────────────────────────────
function loadOpsi(kategori) {
    $.get(URLS.opsiIndex(kategori), function (res) {
        var rows = '';
        (res.data || []).forEach(function (o) {
            rows += '<tr data-id="' + o.id + '">' +
                '<td><input type="text" class="form-control form-control-sm opsi-nama" value="' + o.nama + '"></td>' +
                '<td><input type="number" class="form-control form-control-sm opsi-urutan" value="' + o.urutan + '"></td>' +
                '<td><select class="form-select form-select-sm opsi-status"><option value="aktif"' + (o.status === 'aktif' ? ' selected' : '') + '>Aktif</option><option value="nonaktif"' + (o.status === 'nonaktif' ? ' selected' : '') + '>Non-Aktif</option></select></td>' +
                '<td><button class="btn btn-xs btn-icon btn-light-primary btn-simpan-opsi me-1" title="Simpan"><i class="bi bi-check-lg"></i></button>' +
                '<button class="btn btn-xs btn-icon btn-light-danger btn-hapus-opsi" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
                '</tr>';
        });
        $('#opsi-table tbody').html(rows);
    });
}

$(document).on('click', '.btn-kelola-opsi', function () {
    var kategori = $(this).data('kategori');
    $('#opsi-kategori').val(kategori);
    $('#opsi-label-kategori').text($(this).data('label'));
    $('#opsi-baru-nama').val('');
    loadOpsi(kategori);
    new bootstrap.Modal('#modal-opsi').show();
});

$('#btn-tambah-opsi').on('click', function () {
    var nama = $('#opsi-baru-nama').val().trim();
    if (!nama) return;
    $.ajax({
        url: URLS.opsiStore, type: 'POST',
        data: { _token: '{{ csrf_token() }}', kategori: $('#opsi-kategori').val(), nama: nama, status: 'aktif' },
        success: function () { $('#opsi-baru-nama').val(''); loadOpsi($('#opsi-kategori').val()); },
        error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menambah opsi.'); },
    });
});

$(document).on('click', '.btn-simpan-opsi', function () {
    var $tr = $(this).closest('tr');
    var id = $tr.data('id');
    $.ajax({
        url: URLS.opsiUpdate(id), type: 'POST',
        data: {
            _token: '{{ csrf_token() }}', _method: 'PUT',
            nama: $tr.find('.opsi-nama').val(),
            urutan: $tr.find('.opsi-urutan').val(),
            status: $tr.find('.opsi-status').val(),
        },
        success: function () { toastr.success('Opsi disimpan.'); },
        error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menyimpan.'); },
    });
});

$(document).on('click', '.btn-hapus-opsi', function () {
    var $tr = $(this).closest('tr');
    var id = $tr.data('id');
    Swal.fire({ title: 'Hapus opsi ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', confirmButtonColor: '#d33' })
        .then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({
                url: URLS.opsiDestroy(id), type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function () { $tr.remove(); toastr.success('Opsi dihapus.'); },
                error: function (xhr) { toastr.error(xhr.responseJSON?.message ?? 'Gagal menghapus.'); },
            });
        });
});
</script>
@endpush
