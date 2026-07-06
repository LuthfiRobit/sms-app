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

<div class="mb-3">
    <label class="form-label fw-bold">{{ $poin->label }} @if($poin->is_required)<span class="text-danger">*</span>@endif</label>
    <div class="form-text mb-2">Mengikuti tahapan sintaks dari Model Pembelajaran yang dipilih di atas — pilih modelnya dulu, tahapannya menyesuaikan otomatis.</div>
    <div id="inti-container" class="border rounded p-3 bg-light-subtle"></div>
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
        $container.html('<div class="text-muted small">Pilih Model Pembelajaran untuk menampilkan tahapannya.</div>');
        return;
    }

    // Dibangun lewat DOM API (bukan interpolasi string HTML mentah) supaya
    // konten yang sudah tersimpan tidak bisa disalahgunakan jadi markup.
    ['Memahami', 'Mengaplikasi', 'Merefleksi'].forEach(function (fase) {
        const steps = model.sintaks.filter(s => s.meta_fase === fase);
        if (!steps.length) return;

        $container.append($('<h6>').addClass('text-uppercase text-muted mt-2').text(fase));

        steps.forEach(function (s) {
            const existing = (existingKonten[s.id] || []).join("\n");
            const $wrap = $('<div>').addClass('mb-2');
            $wrap.append($('<label>').addClass('form-label').text(s.nama_sintaks));
            $wrap.append(
                $('<textarea>').addClass('form-control').attr({
                    name: `inti[${s.id}]`,
                    rows: 2,
                    placeholder: 'Satu poin per baris',
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
