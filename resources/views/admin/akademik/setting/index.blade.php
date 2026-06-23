@extends('admin.layouts.app')
@section('title', 'Setting Akademik')

@section('content')

{{-- Embed settings data as JSON for JS tab switching --}}
@php
    $settingsJson = collect($settings)->map(function($s) {
        return [
            'allow_manual_nilai' => (bool) $s->allow_manual_nilai,
            'bobot_harian'       => (float) $s->bobot_harian,
            'bobot_uts'          => (float) $s->bobot_uts,
            'bobot_uas'          => (float) $s->bobot_uas,
            'kkm_default'        => (int) $s->kkm_default,
        ];
    })->toJson();
@endphp

<div class="row g-3">

    {{-- Page Header --}}
    <div class="col-12">
        <div class="d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="mb-0 fw-semibold text-primary">
                    <i class="bi bi-sliders2 me-2"></i>Setting Akademik
                </h4>
                <small class="text-muted">Konfigurasi bobot penilaian, KKM default, dan izin input nilai manual per lembaga.</small>
            </div>
        </div>
        <hr class="mt-3 mb-0">
    </div>

    {{-- Lembaga Tabs (only visible if multiple lembaga) --}}
    @if($lembagaList->count() > 1)
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body py-2 px-3">
                <ul class="nav nav-pills gap-1" id="lembaga-tabs" role="tablist">
                    @foreach($lembagaList as $l)
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link {{ ($activeSetting && $activeSetting->lembaga_id == $l->id) || (!$activeSetting && $loop->first) ? 'active' : '' }}"
                            id="tab-lembaga-{{ $l->id }}"
                            type="button"
                            role="tab"
                            data-lembaga-id="{{ $l->id }}"
                        >
                            <i class="bi bi-building me-1"></i>[{{ $l->kode }}] {{ $l->nama }}
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Settings Form --}}
    <div class="col-12">
        <form id="form-setting" novalidate>
            @csrf
            <input type="hidden" id="current-lembaga-id"
                   value="{{ $activeSetting->lembaga_id ?? ($lembagaList->first()->id ?? '') }}">

            <div class="row g-3">

                {{-- Section 1: Input Nilai Manual --}}
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 text-dark fw-semibold">
                                <i class="bi bi-pencil-square me-2 text-warning"></i>Kontrol Input Nilai
                            </h6>
                            <small class="text-muted">Tentukan apakah guru dapat menginput nilai secara manual.</small>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div>
                                    <p class="mb-0 fw-medium">Izinkan Input Nilai Manual</p>
                                    <small class="text-muted">
                                        Jika diaktifkan, guru dapat menginput nilai harian, UTS, dan UAS secara langsung
                                        tanpa melalui proses pengajuan. Jika dinonaktifkan, nilai hanya dapat diinput
                                        melalui alur pengajuan raport yang disetujui.
                                    </small>
                                </div>
                                <div class="flex-shrink-0 ms-4">
                                    <div class="form-check form-switch form-switch-lg mb-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="allow-manual-nilai"
                                            name="allow_manual_nilai"
                                            value="1"
                                            style="width:3rem;height:1.5rem;cursor:pointer;"
                                            {{ ($activeSetting && $activeSetting->allow_manual_nilai) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label ms-2 fw-medium" for="allow-manual-nilai" id="lbl-manual-nilai">
                                            {{ ($activeSetting && $activeSetting->allow_manual_nilai) ? 'Aktif' : 'Nonaktif' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Bobot Penilaian --}}
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 text-dark fw-semibold">
                                <i class="bi bi-percent me-2 text-primary"></i>Formula Bobot Nilai Akhir
                            </h6>
                            <small class="text-muted">
                                Nilai Akhir = (Bobot Harian × NH) + (Bobot UTS × NUTS) + (Bobot UAS × NUAS).
                                Total ketiga bobot <strong>harus 100</strong>.
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">
                                        Bobot Nilai Harian <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            class="form-control bobot-input"
                                            id="bobot-harian"
                                            name="bobot_harian"
                                            min="1" max="98" step="0.01"
                                            value="{{ $activeSetting->bobot_harian ?? 40 }}"
                                            required
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">
                                        Bobot Nilai UTS <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            class="form-control bobot-input"
                                            id="bobot-uts"
                                            name="bobot_uts"
                                            min="1" max="98" step="0.01"
                                            value="{{ $activeSetting->bobot_uts ?? 30 }}"
                                            required
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">
                                        Bobot Nilai UAS <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            class="form-control bobot-input"
                                            id="bobot-uas"
                                            name="bobot_uas"
                                            min="1" max="98" step="0.01"
                                            value="{{ $activeSetting->bobot_uas ?? 30 }}"
                                            required
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Total Bobot</label>
                                    <div id="bobot-total-wrapper"
                                         class="d-flex align-items-center gap-2 py-2 px-3 rounded border"
                                         style="background:#f8f9fa;">
                                        <span id="bobot-total-value" class="fs-4 fw-bold">100</span>
                                        <span class="text-muted">%</span>
                                        <span id="bobot-total-badge" class="badge ms-auto">Sesuai</span>
                                    </div>
                                    <div id="bobot-error" class="invalid-feedback d-none">
                                        Total bobot harus 100%.
                                    </div>
                                </div>
                            </div>

                            {{-- Visual bar preview --}}
                            <div class="mt-4">
                                <p class="mb-2 text-muted small fw-medium">Proporsi bobot:</p>
                                <div class="d-flex rounded overflow-hidden" style="height:28px;">
                                    <div id="bar-harian"
                                         class="d-flex align-items-center justify-content-center text-white small fw-bold"
                                         style="width:40%;background:#0d6efd;transition:width .3s;">
                                        NH
                                    </div>
                                    <div id="bar-uts"
                                         class="d-flex align-items-center justify-content-center text-white small fw-bold"
                                         style="width:30%;background:#6610f2;transition:width .3s;">
                                        UTS
                                    </div>
                                    <div id="bar-uas"
                                         class="d-flex align-items-center justify-content-center text-white small fw-bold"
                                         style="width:30%;background:#198754;transition:width .3s;">
                                        UAS
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 3: KKM Default --}}
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 text-dark fw-semibold">
                                <i class="bi bi-award me-2 text-success"></i>Kriteria Ketuntasan Minimal (KKM)
                            </h6>
                            <small class="text-muted">
                                Nilai ambang batas kelulusan default apabila KKM per mata pelajaran belum diatur.
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">
                                        KKM Default <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="kkm-default"
                                        name="kkm_default"
                                        min="0" max="100"
                                        value="{{ $activeSetting->kkm_default ?? 70 }}"
                                        required
                                    >
                                    <small class="text-muted">Nilai antara 0 hingga 100.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="btn-reset">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary px-4" id="btn-save">
                            <i class="bi bi-check-lg me-1"></i>Simpan Setting
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function ($) {

    /* ── Data from PHP ──────────────────────────────────────────────── */
    var settingsData  = {!! $settingsJson !!};
    var csrfToken     = '{{ csrf_token() }}';
    var updateUrlBase = '{{ url('admin/akademik/setting') }}';

    /* ── Helpers ────────────────────────────────────────────────────── */
    function getFormValues() {
        return {
            bobot_harian:       parseFloat($('#bobot-harian').val()) || 0,
            bobot_uts:          parseFloat($('#bobot-uts').val())    || 0,
            bobot_uas:          parseFloat($('#bobot-uas').val())    || 0,
            kkm_default:        parseInt($('#kkm-default').val())    || 0,
            allow_manual_nilai: $('#allow-manual-nilai').is(':checked') ? 1 : 0,
        };
    }

    function updateBobotUI() {
        var h = parseFloat($('#bobot-harian').val()) || 0;
        var u = parseFloat($('#bobot-uts').val())    || 0;
        var a = parseFloat($('#bobot-uas').val())    || 0;
        var total = Math.round((h + u + a) * 100) / 100;

        $('#bobot-total-value').text(total);

        var $badge   = $('#bobot-total-badge');
        var $wrapper = $('#bobot-total-wrapper');

        if (total === 100) {
            $badge.removeClass('bg-danger').addClass('bg-success').text('Sesuai');
            $wrapper.removeClass('border-danger').addClass('border-success');
            $('#bobot-error').addClass('d-none');
        } else {
            $badge.removeClass('bg-success').addClass('bg-danger').text('Belum 100%');
            $wrapper.removeClass('border-success').addClass('border-danger');
            $('#bobot-error').removeClass('d-none');
        }

        /* Proportion bar */
        var t = h + u + a;
        if (t > 0) {
            $('#bar-harian').css('width', (h / t * 100).toFixed(1) + '%').text(h + '%');
            $('#bar-uts').css('width',    (u / t * 100).toFixed(1) + '%').text(u + '%');
            $('#bar-uas').css('width',    (a / t * 100).toFixed(1) + '%').text(a + '%');
        }
    }

    function fillForm(s) {
        $('#bobot-harian').val(s.bobot_harian);
        $('#bobot-uts').val(s.bobot_uts);
        $('#bobot-uas').val(s.bobot_uas);
        $('#kkm-default').val(s.kkm_default);
        $('#allow-manual-nilai').prop('checked', s.allow_manual_nilai);
        $('#lbl-manual-nilai').text(s.allow_manual_nilai ? 'Aktif' : 'Nonaktif');
        updateBobotUI();
    }

    /* ── Toggle label ───────────────────────────────────────────────── */
    $('#allow-manual-nilai').on('change', function () {
        $('#lbl-manual-nilai').text(this.checked ? 'Aktif' : 'Nonaktif');
    });

    /* ── Bobot live update ──────────────────────────────────────────── */
    $(document).on('input', '.bobot-input', updateBobotUI);

    /* ── Lembaga tab switch ─────────────────────────────────────────── */
    $('#lembaga-tabs').on('click', 'button[data-lembaga-id]', function () {
        var lembagaId = $(this).data('lembaga-id');
        $('#lembaga-tabs button').removeClass('active');
        $(this).addClass('active');
        $('#current-lembaga-id').val(lembagaId);

        var s = settingsData[lembagaId];
        if (s) {
            fillForm(s);
        }
    });

    /* ── Reset button ───────────────────────────────────────────────── */
    $('#btn-reset').on('click', function () {
        var lembagaId = $('#current-lembaga-id').val();
        var s = settingsData[lembagaId];
        if (s) {
            fillForm(s);
        }
    });

    /* ── Form submit ────────────────────────────────────────────────── */
    $('#form-setting').on('submit', function (e) {
        e.preventDefault();

        var vals  = getFormValues();
        var total = Math.round((vals.bobot_harian + vals.bobot_uts + vals.bobot_uas) * 100) / 100;

        if (total !== 100) {
            Swal.fire({
                icon: 'warning',
                title: 'Total Bobot Tidak Valid',
                text: 'Jumlah bobot harian + UTS + UAS harus tepat 100. Saat ini: ' + total + '.',
                confirmButtonText: 'Perbaiki',
            });
            return;
        }

        var lembagaId = $('#current-lembaga-id').val();
        var $btn = $('#btn-save').prop('disabled', true)
                                .html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url:         updateUrlBase + '/' + lembagaId,
            type:        'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:             csrfToken,
                _method:            'POST',
                bobot_harian:       vals.bobot_harian,
                bobot_uts:          vals.bobot_uts,
                bobot_uas:          vals.bobot_uas,
                kkm_default:        vals.kkm_default,
                allow_manual_nilai: vals.allow_manual_nilai,
            }),
            success: function (res) {
                if (res.status === 200) {
                    /* Update cached data so tab switching reflects new values */
                    settingsData[lembagaId] = {
                        allow_manual_nilai: vals.allow_manual_nilai === 1,
                        bobot_harian:       vals.bobot_harian,
                        bobot_uts:          vals.bobot_uts,
                        bobot_uas:          vals.bobot_uas,
                        kkm_default:        vals.kkm_default,
                    };
                    Swal.fire({
                        icon:              'success',
                        title:             'Tersimpan',
                        text:              res.message || 'Setting berhasil disimpan.',
                        timer:             2200,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan Setting');
            }
        });
    });

    /* ── Init ───────────────────────────────────────────────────────── */
    updateBobotUI();

}(jQuery));
</script>
@endpush
