@extends('admin.layouts.app')
@section('title', $supervisi ? 'Edit Supervisi RPP' : 'Supervisi RPP Baru')

@section('content')
@php
    $instrumenIcon = ['perencanaan' => 'bi-clipboard-data', 'pelaksanaan' => 'bi-easel2', 'asesmen' => 'bi-check2-square'];
    $rowsPerInstrumen = [];
    foreach (array_keys($instrumenList) as $key) {
        $rowsPerInstrumen[$key] = $supervisi ? $supervisi->hasil($key)['kriteria'] : collect($instrumenList[$key]['kriteria'])->map(fn ($k) => [...$k, 'skor' => null, 'catatan' => null]);
    }
@endphp

<style>
    #supervisi-wizard-steps .list-group-item { border: 1px solid var(--bs-border-color-translucent); border-radius: 12px !important; margin-bottom: 8px; }
    #supervisi-wizard-steps .list-group-item.active { background-color: rgba(13, 92, 62, 0.06) !important; color: #0d5c3e !important; border: 1.5px solid #0d5c3e !important; }
    .wizard-panel { display: none; }
    .wizard-panel.active { display: block; }
    .kriteria-row { border: 1px solid var(--bs-border-color); border-radius: 10px; padding: 14px 16px; margin-bottom: 12px; }
    .skor-radio-group .btn { min-width: 42px; }
    @media (min-width: 992px) { .sticky-sidebar { position: sticky; top: 24px; } }
</style>

<div class="row g-4">
    <div class="col-lg-4 col-xl-3">
        <div class="sticky-sidebar">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <span class="text-uppercase text-muted fw-bold d-block mb-3 px-2" style="font-size: 11px; letter-spacing: 1px;">Tahapan Supervisi</span>
                    <div class="list-group list-group-flush gap-1" id="supervisi-wizard-steps">
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3 active" data-step="0">
                            <i class="bi bi-info-circle-fill"></i> Identitas &amp; Supervisor
                        </button>
                        @foreach($instrumenList as $key => $cfg)
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-3" data-step="{{ $loop->index + 1 }}">
                            <i class="bi {{ $instrumenIcon[$key] }}"></i> {{ $cfg['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-xl-9" id="supervisi-card-wrapper">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-clipboard-check me-2"></i>{{ $supervisi ? 'Edit Supervisi RPP' : 'Supervisi RPP Baru' }}</h5>
                    <small class="text-muted">{{ $rpp->guru?->nama_lengkap }} — {{ $rpp->mataPelajaran?->nama }} — {{ $rpp->materi }}</small>
                </div>
                <a href="{{ $supervisi ? route('admin.akademik.supervisi-rpp.show', $supervisi->id) : route('admin.akademik.rpp.show', $rpp->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            </div>

            <div class="card-body p-4 p-md-5">
                <div id="form-alert" class="alert d-none rounded-3"></div>

                <form id="form-supervisi" action="{{ $supervisi ? route('admin.akademik.supervisi-rpp.update', $supervisi->id) : route('admin.akademik.supervisi-rpp.store') }}" method="POST">
                    @csrf
                    @if($supervisi) @method('PUT') @endif
                    <input type="hidden" name="rpp_id" value="{{ $rpp->id }}">

                    {{-- STEP 0: Identitas & Supervisor --}}
                    <div class="wizard-panel active" data-panel-index="0">
                        <h4 class="fw-bold text-dark mb-4"><i class="bi bi-info-circle text-primary me-2"></i>Identitas &amp; Supervisor</h4>

                        <div class="card border-0 bg-light rounded-3 mb-4">
                            <div class="card-body p-3 d-flex flex-wrap gap-4">
                                <div><small class="text-muted d-block text-uppercase" style="font-size:11px;">Madrasah</small><strong>{{ $rpp->lembaga?->nama }}</strong></div>
                                <div><small class="text-muted d-block text-uppercase" style="font-size:11px;">Guru</small><strong>{{ $rpp->guru?->nama_lengkap }}</strong></div>
                                <div><small class="text-muted d-block text-uppercase" style="font-size:11px;">Mata Pelajaran</small><strong>{{ $rpp->mataPelajaran?->nama }}</strong></div>
                                <div><small class="text-muted d-block text-uppercase" style="font-size:11px;">Fase / Kelas</small><strong>{{ $rpp->fase_kelas }}</strong></div>
                                <div><small class="text-muted d-block text-uppercase" style="font-size:11px;">Jumlah Jam Tatap Muka</small><strong>{{ $rpp->alokasi_waktu }}</strong></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tanggal Supervisi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_supervisi" value="{{ old('tanggal_supervisi', optional($supervisi?->tanggal_supervisi)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nama Supervisor</label>
                                <input type="text" class="form-control" name="nama_supervisor" placeholder="{{ $profilSekolah?->kepala_sekolah }}" value="{{ old('nama_supervisor', $supervisi?->nama_supervisor ?? $profilSekolah?->kepala_sekolah) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Jabatan Supervisor</label>
                                <input type="text" class="form-control" name="jabatan_supervisor" placeholder="Kepala Madrasah" value="{{ old('jabatan_supervisor', $supervisi?->jabatan_supervisor ?? 'Kepala Madrasah') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">NIP Supervisor</label>
                                <input type="text" class="form-control" name="nip_supervisor" placeholder="{{ $profilSekolah?->nip_kepsek }}" value="{{ old('nip_supervisor', $supervisi?->nip_supervisor ?? $profilSekolah?->nip_kepsek) }}">
                            </div>
                        </div>
                        <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i>Nama/jabatan/NIP supervisor otomatis terisi dari Profil Sekolah — silakan ubah kalau supervisi ini dilakukan oleh orang lain (mis. Wakasek Kurikulum).</div>
                    </div>

                    {{-- STEPS 1..3: tiap instrumen --}}
                    @foreach($instrumenList as $key => $cfg)
                    <div class="wizard-panel" data-panel-index="{{ $loop->index + 1 }}">
                        <h4 class="fw-bold text-dark mb-2"><i class="bi {{ $instrumenIcon[$key] }} text-primary me-2"></i>{{ $cfg['label'] }}</h4>
                        <p class="text-muted small mb-4">Beri skor 0–3 untuk tiap komponen (0 = belum tampak, 3 = sangat baik).</p>

                        @php $tahapSaatIni = null; @endphp
                        @foreach($rowsPerInstrumen[$key] as $i => $row)
                            @if($row['tahap'] && $row['tahap'] !== $tahapSaatIni)
                                @php $tahapSaatIni = $row['tahap']; @endphp
                                <h6 class="text-uppercase text-muted fw-bold mt-3 mb-2" style="font-size:11px; letter-spacing:0.5px;">Tahap {{ $tahapSaatIni }}</h6>
                            @endif
                            <div class="kriteria-row">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div class="flex-grow-1" style="min-width: 250px;">
                                        <span class="badge bg-light text-secondary border me-1">{{ $i + 1 }}</span>
                                        {{ $row['teks'] }}
                                    </div>
                                    <div class="btn-group skor-radio-group" role="group">
                                        @foreach([0, 1, 2, 3] as $nilai)
                                        <input type="radio" class="btn-check" name="skor[{{ $key }}][{{ $row['kode'] }}][skor]" id="skor-{{ $key }}-{{ $row['kode'] }}-{{ $nilai }}" value="{{ $nilai }}" {{ (int) old("skor.{$key}.{$row['kode']}.skor", $row['skor'] ?? -1) === $nilai ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-primary btn-sm" for="skor-{{ $key }}-{{ $row['kode'] }}-{{ $nilai }}">{{ $nilai }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-2" name="skor[{{ $key }}][{{ $row['kode'] }}][catatan]" placeholder="Catatan (opsional)" value="{{ old("skor.{$key}.{$row['kode']}.catatan", $row['catatan'] ?? '') }}">
                            </div>
                        @endforeach

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Catatan Khusus Hasil Supervisi</label>
                                <textarea class="form-control" name="catatan_{{ $key }}" rows="3">{{ old("catatan_{$key}", $supervisi?->{"catatan_{$key}"}) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rencana Tindak Lanjut</label>
                                <textarea class="form-control" name="rtl_{{ $key }}" rows="3">{{ old("rtl_{$key}", $supervisi?->{"rtl_{$key}"}) }}</textarea>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4 btn-wizard-back py-2 rounded-pill"><i class="bi bi-arrow-left me-2"></i>Sebelumnya</button>
                        <div>
                            <button type="button" class="btn btn-primary px-4 btn-wizard-next py-2 rounded-pill">Berikutnya<i class="bi bi-arrow-right ms-2"></i></button>
                            <button type="submit" class="btn btn-success px-5 btn-wizard-submit d-none py-2 rounded-pill"><i class="bi bi-check-lg me-2"></i>Simpan Supervisi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    let currentStepIndex = 0;
    const panels = $('.wizard-panel');
    const steps = $('#supervisi-wizard-steps button');

    function validateCurrentStep() {
        let isValid = true;
        $('.wizard-panel.active').find('[required]').each(function () {
            if (!this.checkValidity()) { this.reportValidity(); isValid = false; return false; }
        });
        return isValid;
    }

    function updateWizard() {
        panels.removeClass('active');
        $(panels[currentStepIndex]).addClass('active');
        steps.removeClass('active');
        $(steps[currentStepIndex]).addClass('active');

        $('.btn-wizard-back').prop('disabled', currentStepIndex === 0).toggleClass('opacity-50', currentStepIndex === 0);
        $('.btn-wizard-next').toggleClass('d-none', currentStepIndex === panels.length - 1);
        $('.btn-wizard-submit').toggleClass('d-none', currentStepIndex !== panels.length - 1);

        $('html, body').animate({ scrollTop: $('#supervisi-card-wrapper').offset().top - 80 }, 200);
    }

    steps.on('click', function () {
        const targetIndex = steps.index(this);
        if (targetIndex === currentStepIndex) return;
        if (targetIndex > currentStepIndex) {
            for (let i = currentStepIndex; i < targetIndex; i++) {
                panels.removeClass('active');
                $(panels[i]).addClass('active');
                if (!validateCurrentStep()) { currentStepIndex = i; updateWizard(); return; }
            }
        }
        currentStepIndex = targetIndex;
        updateWizard();
    });

    $('.btn-wizard-next').on('click', function () {
        if (validateCurrentStep()) { currentStepIndex++; updateWizard(); }
    });

    $('.btn-wizard-back').on('click', function () {
        if (currentStepIndex > 0) { currentStepIndex--; updateWizard(); }
    });

    updateWizard();
});

$('#form-supervisi').on('submit', function (e) {
    e.preventDefault();
    $('#form-alert').addClass('d-none');
    const $submitBtn = $('.btn-wizard-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function (res) {
            if (res.status === 200) {
                toastr.success(res.message);
                window.location.href = '{{ route('admin.akademik.supervisi-rpp.index') }}';
            } else {
                $('#form-alert').removeClass('d-none').addClass('alert-danger').html(res.message);
            }
        },
        error: function (xhr) {
            let msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
            if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            $('#form-alert').removeClass('d-none').addClass('alert-danger').html(msg);
        },
        complete: function () {
            $submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Simpan Supervisi');
        },
    });
});
</script>
@endpush
