@extends('admin.layouts.app')
@section('title', 'Kuota Jurusan PPDB')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
    <style>
        .kuota-input {
            width: 90px;
            text-align: center;
            font-weight: 600;
        }
        .progress { border-radius: 20px; }
        .progress-bar { border-radius: 20px; transition: width 0.4s ease; }
        .table > tbody > tr:hover { background-color: rgba(13, 110, 253, 0.04); }
        .jurusan-card { transition: box-shadow 0.2s ease; }
        .jurusan-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important; }
        .alert-no-record { border-left: 4px solid #ffc107; }
    </style>
@endpush

@section('content')

{{-- ======== HEADER FILTER CARD ======== --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Kuota Jurusan PPDB</h5>
                <small class="text-muted">Atur distribusi kuota pendaftar per jurusan untuk setiap jalur pendaftaran.</small>
            </div>
            <div class="card-body bg-light py-3">
                <form method="GET" action="{{ route('admin.ppdb.kuota.index') }}" id="form-filter">
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small mb-1">Jalur Pendaftaran <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="jalur_id" data-live-search="true" id="filter-jalur">
                                <option value="">-- Pilih Jalur --</option>
                                @foreach($jalurList as $jalur)
                                    <option value="{{ $jalur->id }}" {{ $jalurId == $jalur->id ? 'selected' : '' }}>
                                        {{ $jalur->pembukaanPpdb?->nama }} — {{ $jalur->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small mb-1">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-control selectpicker" name="tahun_pelajaran_id" data-live-search="true" id="filter-tahun">
                                <option value="">-- Pilih Tahun Pelajaran --</option>
                                @foreach($tahunPelajaranList as $tahun)
                                    <option value="{{ $tahun->id }}" {{ $tahunPelajaranId == $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Tampilkan Kuota
                            </button>
                            @if($jalurId && $tahunPelajaranId)
                                <a href="{{ route('admin.ppdb.kuota.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ======== TABEL KUOTA ======== --}}
@if($jalurId && $tahunPelajaranId)

    {{-- Konteks info --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center py-2 mb-0">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Jalur:</strong> {{ $jalurSelected?->pembukaanPpdb?->nama }} — {{ $jalurSelected?->nama }}
                    &nbsp;|&nbsp;
                    <strong>Tahun:</strong> {{ $tahunSelected?->nama }}
                </div>
            </div>
        </div>
    </div>

    {{-- Warning jika ada jurusan belum diisi kuota --}}
    @php
        $belumDiisi = $kuotaData->filter(fn($k) => !$k['has_record'] || $k['kuota'] == 0)->count();
    @endphp
    @if($belumDiisi > 0)
        <div class="alert alert-warning alert-no-record shadow-sm border-0 mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Perhatian:</strong> Terdapat <strong>{{ $belumDiisi }} jurusan</strong> yang belum memiliki kuota.
            Isi kuota pada kolom di bawah, lalu klik <strong>Simpan Semua Kuota</strong>.
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Distribusi Kuota per Jurusan</h6>
                        <small class="text-muted">Edit nilai kuota langsung di tabel. Kolom <em>Terisi</em> tidak bisa diedit manual.</small>
                    </div>
                    @if(auth()->user()->hasPermissionTo('admin.ppdb.kuota.upsert'))
                        <button class="btn btn-success shadow-sm" id="btn-simpan-kuota">
                            <i class="bi bi-save me-1"></i> Simpan Semua Kuota
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0" id="kuota-table">
                            <thead class="table-light text-center">
                                <tr>
                                    <th class="text-start ps-3" style="width: 30%">Jurusan</th>
                                    <th style="width: 18%">Kuota<br><small class="text-muted fw-normal">(Dapat diedit)</small></th>
                                    <th style="width: 12%">Terisi<br><small class="text-muted fw-normal">(Read-only)</small></th>
                                    <th style="width: 12%">Sisa</th>
                                    <th style="width: 18%">Persentase Terisi</th>
                                    <th style="width: 10%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kuotaData as $item)
                                    @php
                                        $persen  = ($item['kuota'] > 0) ? round(($item['terisi'] / $item['kuota']) * 100, 1) : 0;
                                        $status  = ($item['kuota'] > 0 && $item['terisi'] >= $item['kuota']) ? 'penuh' : 'tersedia';
                                        $barColor = $persen >= 100 ? 'danger' : ($persen >= 80 ? 'warning' : 'success');
                                    @endphp
                                    <tr data-jurusan-id="{{ $item['jurusan_id'] }}"
                                        class="{{ !$item['has_record'] || $item['kuota'] == 0 ? 'table-warning' : '' }}">
                                        <td class="ps-3">
                                            <div class="fw-bold">{{ $item['jurusan_nama'] }}</div>
                                            <small class="text-muted">Kode: {{ $item['jurusan_kode'] ?? '-' }}</small>
                                            @if(!$item['has_record'])
                                                <span class="badge bg-warning text-dark ms-1">Baru</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   class="form-control form-control-sm kuota-input mx-auto kuota-field"
                                                   data-jurusan="{{ $item['jurusan_id'] }}"
                                                   data-terisi="{{ $item['terisi'] }}"
                                                   value="{{ $item['kuota'] }}"
                                                   min="{{ $item['terisi'] }}"
                                                   placeholder="0"
                                                   {{ auth()->user()->hasPermissionTo('admin.ppdb.kuota.upsert') ? '' : 'disabled' }}>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold text-{{ $item['terisi'] > 0 ? 'danger' : 'secondary' }}">
                                                {{ $item['terisi'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold text-{{ $item['sisa'] > 0 ? 'success' : 'danger' }} sisa-val">
                                                {{ $item['sisa'] }}
                                            </span>
                                        </td>
                                        <td class="px-3">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $barColor }} persen-bar"
                                                     role="progressbar"
                                                     style="width: {{ $persen }}%;"
                                                     aria-valuenow="{{ $persen }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted persen-label">{{ $persen }}%</small>
                                        </td>
                                        <td class="text-center">
                                            @if($item['kuota'] == 0)
                                                <span class="badge bg-warning text-dark">Belum Diisi</span>
                                            @elseif($status === 'penuh')
                                                <span class="badge bg-danger">Penuh</span>
                                            @else
                                                <span class="badge bg-success">Tersedia</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Tidak ada jurusan aktif yang ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($kuotaData->count() > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="ps-3">TOTAL</td>
                                    <td class="text-center" id="total-kuota">{{ $kuotaData->sum('kuota') }}</td>
                                    <td class="text-center text-danger">{{ $kuotaData->sum('terisi') }}</td>
                                    <td class="text-center text-success">{{ $kuotaData->sum('sisa') }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="bi bi-funnel fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">Silahkan pilih Jalur Pendaftaran dan Tahun Pelajaran</h5>
                    <p class="text-muted mb-0">Data kuota jurusan akan ditampilkan setelah filter dipilih.</p>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function () {
    // Live preview sisa kuota saat input berubah
    $(document).on('input', '.kuota-field', function () {
        const $row    = $(this).closest('tr');
        const kuota   = parseInt($(this).val()) || 0;
        const terisi  = parseInt($(this).data('terisi')) || 0;
        const minVal  = parseInt($(this).attr('min')) || 0;

        // Highlight merah jika kuota < terisi
        if (kuota < terisi) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }

        const sisa   = Math.max(0, kuota - terisi);
        const persen = kuota > 0 ? Math.round((terisi / kuota) * 100 * 10) / 10 : 0;
        const color  = persen >= 100 ? 'danger' : (persen >= 80 ? 'warning' : 'success');

        // Update kolom sisa
        $row.find('.sisa-val')
            .text(sisa)
            .removeClass('text-success text-danger text-secondary')
            .addClass(sisa > 0 ? 'text-success' : 'text-danger');

        // Update progress bar
        $row.find('.persen-bar')
            .css('width', persen + '%')
            .attr('aria-valuenow', persen)
            .removeClass('bg-success bg-warning bg-danger')
            .addClass('bg-' + color);

        $row.find('.persen-label').text(persen + '%');

        // Update total footer
        updateTotals();
    });

    function updateTotals() {
        let totalKuota = 0;
        $('.kuota-field').each(function () {
            totalKuota += parseInt($(this).val()) || 0;
        });
        $('#total-kuota').text(totalKuota);
    }

    // Tombol Simpan Semua
    $('#btn-simpan-kuota').on('click', function () {
        // Validasi: tidak boleh ada input yang invalid
        if ($('.kuota-field.is-invalid').length > 0) {
            Swal.fire('Kuota Tidak Valid', 'Kuota jurusan tidak boleh lebih kecil dari jumlah pendaftar yang sudah terisi.', 'error');
            return;
        }

        // Kumpulkan data semua baris
        let kuotaData = [];
        $('.kuota-field').each(function () {
            kuotaData.push({
                jurusan_id : parseInt($(this).data('jurusan')),
                kuota      : parseInt($(this).val()) || 0
            });
        });

        Swal.fire({
            title: 'Simpan Semua Kuota?',
            text: `${kuotaData.length} data kuota jurusan akan disimpan.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url     : "{{ route('admin.ppdb.kuota.upsert') }}",
                method  : 'POST',
                contentType: 'application/json',
                data    : JSON.stringify({
                    _token               : "{{ csrf_token() }}",
                    jalur_id             : {{ $jalurId ?? 0 }},
                    tahun_pelajaran_id   : {{ $tahunPelajaranId ?? 0 }},
                    kuota_data           : kuotaData
                }),
                beforeSend: function () {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function (res) {
                    Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                },
                error: function (err) {
                    let msg = err.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    Swal.fire('Gagal!', msg, 'error');
                }
            });
        });
    });
});
</script>
@endpush
