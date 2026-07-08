@php
    $selectedModelId = $rpp->model_pembelajaran_id ?? ($modelList->first()->id ?? null);

    $modelListForJs = $modelList->map(function ($m) {
        return [
            'id' => $m->id,
            'sintaks' => $m->sintaks->map(function ($s) {
                return ['id' => $s->id, 'nama_sintaks' => $s->nama_sintaks, 'meta_fase' => $s->meta_fase];
            })->values(),
        ];
    });

    $existingIntiForJs = $rpp
        ? $rpp->inti->mapWithKeys(function ($i) {
            return [(string) $i->model_pembelajaran_sintaks_id => $i->konten];
        })
        : (object) [];
@endphp

<div class="card rpp-point-card mb-4 border rounded-3 shadow-sm" style="transition: border-color 0.2s ease, box-shadow 0.2s ease;">
    <div class="card-body p-4">
        <label class="form-label fw-bold text-dark fs-6 d-flex align-items-center justify-content-between mb-2">
            <span>{{ $poin->label }}</span>
            @if($poin->is_required)
                <span class="badge bg-light-danger text-danger border border-danger-subtle px-2 py-1" style="font-size: 10px;">Wajib</span>
            @endif
        </label>
        <div class="form-text text-muted mb-3"><i class="bi bi-info-circle me-1"></i>Mengikuti tahapan sintaks dari Model Pembelajaran yang dipilih di atas — pilih modelnya dulu, tahapannya menyesuaikan otomatis.</div>
        <div id="inti-container" class="p-3 bg-light rounded-3 border"></div>
    </div>
</div>

@push('scripts')
<script>
const MODEL_LIST = @json($modelListForJs);
const EXISTING_INTI = @json($existingIntiForJs);

function renderIntiBlocks(modelId, existingKonten) {
    existingKonten = existingKonten || {};
    const model = MODEL_LIST.find(m => String(m.id) === String(modelId));
    const $container = $('#inti-container');
    $container.empty();

    if (!model || !model.sintaks.length) {
        $container.html('<div class="text-muted small p-2"><i class="bi bi-exclamation-triangle me-1"></i>Pilih Model Pembelajaran untuk menampilkan tahapannya.</div>');
        return;
    }

    // Dibangun lewat DOM API (bukan interpolasi string HTML mentah) supaya
    // konten yang sudah tersimpan tidak bisa disalahgunakan jadi markup.
    ['Memahami', 'Mengaplikasi', 'Merefleksi'].forEach(function (fase) {
        const steps = model.sintaks.filter(s => s.meta_fase === fase);
        if (!steps.length) return;

        $container.append($('<h6>').addClass('text-uppercase text-muted fw-bold mt-3 mb-2').css({ 'font-size': '11px', 'letter-spacing': '0.5px' }).text(fase));

        steps.forEach(function (s) {
            const existing = (existingKonten[s.id] || []).join("\n");
            const $wrap = $('<div>').addClass('mb-3');
            $wrap.append($('<label>').addClass('form-label fw-semibold text-secondary small').text(s.nama_sintaks));
            $wrap.append(
                $('<textarea>').addClass('form-control').attr({
                    name: `inti[${s.id}]`,
                    rows: 3,
                    placeholder: 'Satu poin per baris... (contoh: Guru membagikan lembar kerja, Murid membaca cerita)',
                }).val(existing)
            );
            $container.append($wrap);
        });
    });
}

$(document).on('change', '#model_pembelajaran_id', function () {
    renderIntiBlocks($(this).val(), {});
});

$(function () {
    renderIntiBlocks(@json($selectedModelId), EXISTING_INTI);
});
</script>
@endpush
