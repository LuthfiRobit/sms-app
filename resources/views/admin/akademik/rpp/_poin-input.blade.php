@php
    $nilai = $rpp?->nilaiUntuk($poin);

    // "Artefak Khas Model Pembelajaran" (kode tetap 'artefak_model', tipe
    // teks_panjang biasa — lihat RppTemplateSeeder) — label & hint-nya
    // dioverride sesuai Model Pembelajaran yang aktif, baik saat render awal
    // (di sini) maupun saat guru ganti model di form (lihat script di bawah).
    $isArtefakModel = $poin->kode === 'artefak_model';
    if ($isArtefakModel) {
        $selectedModelIdArtefak = $rpp?->model_pembelajaran_id ?? ($modelList->first()->id ?? null);
        $modelUntukArtefak = $modelList->firstWhere('id', $selectedModelIdArtefak);
        $artefakLabel = $modelUntukArtefak?->label_artefak ?: $poin->label;
        $artefakHint = $modelUntukArtefak?->deskripsi_artefak ?: 'Bisa diformat (tebal, miring, daftar, dll) lewat toolbar di atas.';
        $artefakTersembunyi = ! $modelUntukArtefak?->label_artefak;
    }
@endphp

@if($poin->tipe === 'model_pembelajaran')
    @include('admin.akademik.rpp._poin-inti', ['poin' => $poin])
@else
<div class="card rpp-point-card mb-4 border rounded-3 shadow-sm {{ ($isArtefakModel && $artefakTersembunyi) ? 'd-none' : '' }}" id="poin-card-{{ $poin->id }}" style="transition: border-color 0.2s ease, box-shadow 0.2s ease;">
    <div class="card-body p-4">
        <label class="form-label fw-bold text-dark fs-6 d-flex align-items-center justify-content-between mb-2">
            <span id="poin-label-{{ $poin->id }}">{{ $isArtefakModel ? $artefakLabel : $poin->label }}</span>
            @if($poin->is_required)
                <span class="badge bg-light-danger text-danger border border-danger-subtle px-2 py-1" style="font-size: 10px;">Wajib</span>
            @endif
        </label>

        @switch($poin->tipe)
            @case('teks')
                <input type="text" class="form-control form-control-lg fs-6" name="poin[{{ $poin->id }}]" value="{{ $nilai->value_teks ?? '' }}" @if($poin->is_required) required @endif>
                <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>Isian singkat satu baris.</div>
                @break

            @case('teks_panjang')
                {{-- WYSIWYG (CKEditor) — poin ini menampung prosa bebas format,
                     beda dari daftar_poin/pasangan_kolom yang isinya di-parse
                     terstruktur (jangan disamakan, HTML rich-text akan merusak
                     parsing "satu baris = satu item" di poin lain). --}}
                <textarea class="form-control rpp-wysiwyg" id="poin-teks-{{ $poin->id }}" name="poin[{{ $poin->id }}]" rows="4">{{ $nilai->value_teks ?? '' }}</textarea>
                <div class="form-text text-muted" id="poin-hint-{{ $poin->id }}"><i class="bi bi-info-circle me-1"></i><span>{{ $isArtefakModel ? $artefakHint : 'Bisa diformat (tebal, miring, daftar, dll) lewat toolbar di atas.' }}</span></div>
                @break

            @case('daftar_poin')
                <textarea class="form-control fs-6" name="poin[{{ $poin->id }}]" rows="5" placeholder="Satu poin per baris..." @if($poin->is_required) required @endif>{{ $nilai?->value_json ? implode("\n", $nilai->value_json) : '' }}</textarea>
                <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>Satu poin per baris — tekan Enter untuk poin baru. Cocok untuk butir-butir singkat.</div>
                @break

            @case('pasangan_kolom')
                @php $rows = $nilai->value_json ?? []; @endphp
                <div class="table-responsive rounded-3 border mb-2">
                    <table class="table table-hover align-middle mb-0 pasangan-kolom-table" data-poin-id="{{ $poin->id }}">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="py-3 px-3">{{ $poin->kolom1_label }}</th>
                                <th class="py-3 px-3">{{ $poin->kolom2_label }}</th>
                                <th width="60" class="text-center py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $i => $row)
                            <tr class="pasangan-row">
                                <td class="px-3"><input type="text" class="form-control" name="poin[{{ $poin->id }}][{{ $i }}][kolom1]" value="{{ $row['kolom1'] ?? '' }}"></td>
                                <td class="px-3"><input type="text" class="form-control" name="poin[{{ $poin->id }}][{{ $i }}][kolom2]" value="{{ $row['kolom2'] ?? '' }}"></td>
                                <td class="text-center"><button type="button" class="btn btn-icon btn-light-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr class="pasangan-row">
                                <td class="px-3"><input type="text" class="form-control" name="poin[{{ $poin->id }}][0][kolom1]"></td>
                                <td class="px-3"><input type="text" class="form-control" name="poin[{{ $poin->id }}][0][kolom2]"></td>
                                <td class="text-center"><button type="button" class="btn btn-icon btn-light-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary btn-add-row rounded-pill px-3" data-poin-id="{{ $poin->id }}">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Baris
                </button>
                <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i>Isi kedua kolom lalu klik "Tambah Baris". Baris kosong otomatis diabaikan.</div>
                @break

            @case('pilih_master')
                @php $opsiList = $poin->opsiMaster(); @endphp
                {{-- Marker tersembunyi: kalau semua checkbox di-uncheck, browser tidak
                     mengirim field ini sama sekali — tanpa ini, guru yang sengaja
                     mengosongkan pilihan tidak akan tersimpan (nilai lama tetap nyantol). --}}
                <input type="hidden" name="poin[{{ $poin->id }}][]" value="">
                <div class="row g-2 mt-1">
                    @forelse($opsiList as $opsi)
                        <div class="col-md-6">
                            <div class="card border rounded-3 p-3 h-100 check-card transition-all" style="cursor: pointer; transition: all 0.2s ease;">
                                <div class="form-check d-flex align-items-center mb-0">
                                    <input class="form-check-input flex-shrink-0" type="checkbox" name="poin[{{ $poin->id }}][]" value="{{ $opsi->id }}" id="opsi-{{ $opsi->id }}"
                                        {{ in_array($opsi->id, $nilai->value_json ?? []) ? 'checked' : '' }} style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold text-dark ms-2 mb-0 cursor-pointer" for="opsi-{{ $opsi->id }}" style="cursor: pointer; width: 100%;">{{ $opsi->nama }}</label>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Belum ada opsi untuk kategori "{{ $poin->master_kategori }}". Tambahkan dari halaman Kelola Bagian &amp; Poin RPP.</div>
                    @endforelse
                </div>
                @if($opsiList->isNotEmpty())
                    <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i>Boleh centang lebih dari satu.</div>
                @endif
                @break
        @endswitch
    </div>
</div>
@endif

@if($isArtefakModel)
@push('scripts')
<script>
const MODEL_ARTEFAK = @json($modelList->mapWithKeys(fn ($m) => [$m->id => ['label' => $m->label_artefak, 'deskripsi' => $m->deskripsi_artefak]]));

$(document).on('change', '#model_pembelajaran_id', function () {
    const info = MODEL_ARTEFAK[$(this).val()] || {};
    const $card = $('#poin-card-{{ $poin->id }}');

    if (!info.label) {
        $card.addClass('d-none');
    } else {
        $card.removeClass('d-none');
        $('#poin-label-{{ $poin->id }}').text(info.label);
        $('#poin-hint-{{ $poin->id }} span').text(info.deskripsi || 'Bisa diformat (tebal, miring, daftar, dll) lewat toolbar di atas.');
    }

    // Konten lama tidak relevan lagi untuk model baru — kosongkan (mirror
    // perilaku blok Inti yang juga membuang isi lama saat model berganti).
    const editor = rppEditors['poin-teks-{{ $poin->id }}'];
    if (editor) { editor.setData(''); } else { $('#poin-teks-{{ $poin->id }}').val(''); }
});
</script>
@endpush
@endif
