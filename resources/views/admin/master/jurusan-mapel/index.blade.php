@extends('admin.layouts.app')
@section('title', 'Mapping Jurusan & Mata Pelajaran')

@section('content')
<div class="row g-3">
    {{-- ── Panel Kiri: Daftar Jurusan ── --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary"><i class="bi bi-diagram-3 me-2"></i>Jurusan</h5>
                <small class="text-muted">Klik jurusan untuk melihat dan mengatur mata pelajaran.</small>
            </div>
            <div class="card-body p-2">
                @forelse($jurusanList as $j)
                    <button type="button"
                        class="btn btn-outline-primary btn-sm w-100 text-start mb-1 jurusan-btn"
                        data-id="{{ $j->id }}"
                        data-nama="{{ $j->nama }}">
                        <i class="bi bi-folder me-1"></i>
                        <strong>[{{ $j->kode }}]</strong> {{ $j->nama }}
                    </button>
                @empty
                    <p class="text-muted small p-2 mb-0">Belum ada jurusan untuk lembaga aktif.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Panel Kanan: Mata Pelajaran ── --}}
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center">
                <div class="flex-grow-1">
                    <h5 class="mb-0 text-primary" id="panel-jurusan-title">
                        <i class="bi bi-journal-text me-2"></i>Mata Pelajaran
                    </h5>
                    <small class="text-muted" id="panel-jurusan-sub">Pilih jurusan terlebih dahulu di sebelah kiri.</small>
                </div>
                <div class="flex-shrink-0">
                    <button class="btn btn-sm btn-success px-3 d-none" id="btn-simpan-mapping">
                        <i class="bi bi-save me-1"></i>Simpan Mapping
                    </button>
                </div>
            </div>
            <div class="card-body" id="panel-mapel-body">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-arrow-left-circle fs-1 d-block mb-2 opacity-50"></i>
                    Pilih jurusan di panel kiri untuk mulai mengatur mata pelajaran.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    let activeJurusanId   = null;
    let activeJurusanNama = null;

    // ── Klik jurusan ─────────────────────────────────────────
    $(document).on('click', '.jurusan-btn', function () {
        $('.jurusan-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('active btn-primary');

        activeJurusanId   = $(this).data('id');
        activeJurusanNama = $(this).data('nama');

        $('#panel-jurusan-title').html('<i class="bi bi-journal-text me-2"></i>' + activeJurusanNama);
        $('#panel-jurusan-sub').text('Centang mata pelajaran yang masuk dalam jurusan ini, lalu atur urutannya.');
        $('#panel-mapel-body').html('<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>');
        $('#btn-simpan-mapping').removeClass('d-none');

        $.get(`{{ url('admin/master/jurusan-mapel') }}/${activeJurusanId}`, function (res) {
            if (res.status !== 200) {
                $('#panel-mapel-body').html('<div class="alert alert-danger">' + (res.message || 'Gagal memuat data.') + '</div>');
                return;
            }
            renderMapelPanel(res.data);
        }).fail(function () {
            $('#panel-mapel-body').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>');
        });
    });

    // ── Render daftar mapel ───────────────────────────────────
    function renderMapelPanel(data) {
        const allMapel  = data.all_mapel  || [];
        const mappedIds = data.mapped_ids || [];
        const urutanMap = data.urutan_map || {};

        if (allMapel.length === 0) {
            $('#panel-mapel-body').html('<p class="text-muted">Belum ada mata pelajaran untuk lembaga ini.</p>');
            return;
        }

        // Group by kelompok
        const groups = {};
        allMapel.forEach(function (m) {
            if (!groups[m.kelompok]) groups[m.kelompok] = [];
            groups[m.kelompok].push(m);
        });

        const kelompokLabel = { wajib: 'Wajib', peminatan: 'Peminatan', mulok: 'Mulok' };
        const kelompokColor = { wajib: 'primary', peminatan: 'success', mulok: 'warning' };

        let html = '<div class="table-responsive"><table class="table table-sm table-hover align-middle">';
        html += '<thead class="bg-light"><tr><th width="5%">Pilih</th><th>Mata Pelajaran</th><th width="15%">Kelompok</th><th width="20%">Urutan</th></tr></thead><tbody>';

        Object.keys(groups).forEach(function (kelompok) {
            groups[kelompok].forEach(function (m) {
                const checked = mappedIds.includes(m.id) ? 'checked' : '';
                const urutan  = urutanMap[m.id] !== undefined ? urutanMap[m.id] : 0;
                const badge   = kelompokColor[kelompok] || 'secondary';
                const label   = kelompokLabel[kelompok] || kelompok;
                html += `<tr>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input mapel-check" value="${m.id}" ${checked}>
                    </td>
                    <td>
                        <span class="fw-semibold">${m.nama}</span>
                        <small class="text-muted ms-1">[${m.kode}]</small>
                    </td>
                    <td><span class="badge bg-light-${badge} text-${badge}">${label}</span></td>
                    <td>
                        <input type="number" class="form-control form-control-sm urutan-input"
                               data-mapel-id="${m.id}" value="${urutan}" min="0" style="width:80px;">
                    </td>
                </tr>`;
            });
        });

        html += '</tbody></table></div>';
        $('#panel-mapel-body').html(html);
    }

    // ── Simpan mapping ────────────────────────────────────────
    $('#btn-simpan-mapping').on('click', function () {
        if (!activeJurusanId) return;

        const mapelIds = [];
        const urutan   = {};

        $('.mapel-check:checked').each(function () {
            const id = $(this).val();
            mapelIds.push(id);
        });

        $('.urutan-input').each(function () {
            const mapelId = $(this).data('mapel-id');
            urutan[mapelId] = $(this).val() || 0;
        });

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url: `{{ url('admin/master/jurusan-mapel') }}/${activeJurusanId}/sync`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                mapel_ids: mapelIds,
                urutan: urutan,
            },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Mapping');
                if (res.status === 200) {
                    Swal.fire({ icon: 'success', title: res.message || 'Mapping berhasil diperbarui.', timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'warning', title: res.message || 'Gagal menyimpan.' });
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Simpan Mapping');
                const errors = xhr.responseJSON?.errors;
                const msg = errors ? Object.values(errors).flat().join('<br>') : 'Terjadi kesalahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });
});
</script>
@endpush
