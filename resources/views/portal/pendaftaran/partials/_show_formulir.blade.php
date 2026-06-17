{{-- ══ SECTION 1 — FORMULIR DINAMIS ══ --}}
@if($formulirFields->isNotEmpty())
<div class="bg-surface-container-lowest rounded-2xl soft-shadow overflow-hidden border-t-[3px] border-tertiary-fixed-dim">

    <div class="flex items-center justify-between px-6 py-4 bg-surface-container-low border-b border-outline-variant flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-[22px]">assignment</span>
            <h2 class="text-headline-sm font-bold text-on-surface">Formulir Pendaftaran</h2>
        </div>
        @if($isDraft)
        <div id="autosave-indicator" class="flex items-center gap-1.5 text-body-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-[16px]" id="autosave-icon">cloud_done</span>
            <span id="autosave-text">Belum ada perubahan</span>
        </div>
        @endif
    </div>

    <div class="p-6">
        <form id="formulirForm" autocomplete="off">
            <div class="flex flex-col gap-5">
                @foreach($formulirFields->where('tipe_field', '!=', 'file') as $field)
                @php
                    $fv      = $fvMap->get($field->id);
                    $curVal  = $fv?->value ?? '';
                    $isStatis = $field->is_statis;
                    $required = $field->is_required;
                    $fieldId  = 'field_' . $field->id;
                    $opsi     = $field->opsi ?? [];

                    if ($isStatis && $peserta && $field->dapodik_key) {
                        $dk = $field->dapodik_key;
                        $staticVal = match (true) {
                            str_starts_with($dk, 'kontak.') => $peserta->kontak?->{substr($dk, 7)} ?? $curVal,
                            str_starts_with($dk, 'alamat.') => $peserta->alamat?->{substr($dk, 7)} ?? $curVal,
                            default => $peserta->{$dk} ?? $curVal,
                        };
                        $curVal = $staticVal;
                    }
                @endphp

                <div class="{{ $isStatis ? 'opacity-80' : '' }}">
                    <label class="block text-label-md font-semibold text-on-surface-variant mb-2" for="{{ $fieldId }}">
                        {{ $field->label }}
                        @if($required) <span class="text-error ml-1">*</span> @endif
                        @if($isStatis)
                            <span class="ml-2 text-[11px] bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full">Data Profil</span>
                        @endif
                    </label>

                    @if($isStatis)
                        <div class="flex items-center gap-3 bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3">
                            <span class="material-symbols-outlined text-outline text-[18px]">lock</span>
                            <span class="text-body-md text-on-surface">{{ $curVal ?: '—' }}</span>
                        </div>
                        <input type="hidden" name="fields[{{ $field->id }}][formulir_field_id]" value="{{ $field->id }}">
                        <input type="hidden" name="fields[{{ $field->id }}][value]" value="{{ $curVal }}" data-field-id="{{ $field->id }}">
                        <p class="mt-1 text-body-sm text-on-surface-variant">
                            Data dari profil —
                            <a href="{{ route('ppdb.profil.index') }}" target="_blank" class="text-primary font-semibold">ubah di halaman profil</a>
                        </p>

                    @elseif($field->tipe_field === 'select')
                        <div class="relative flex rounded-xl overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all {{ !$isDraft ? 'opacity-70' : '' }}">
                            <select id="{{ $fieldId }}"
                                    class="auto-save-field flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none appearance-none cursor-pointer"
                                    name="fields[{{ $field->id }}][value]"
                                    data-field-id="{{ $field->id }}"
                                    {{ !$isDraft ? 'disabled' : '' }}>
                                <option value="">— Pilih —</option>
                                @foreach($opsi as $opt)
                                @php $optVal = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                                     $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['value'] ?? '') : $opt; @endphp
                                <option value="{{ $optVal }}" {{ $curVal == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
                        </div>

                    @elseif($field->tipe_field === 'radio')
                        <div class="flex flex-wrap gap-3">
                            @foreach($opsi as $opt)
                            @php $optVal = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                                 $optLabel = is_array($opt) ? ($opt['label'] ?? '') : $opt; @endphp
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input class="auto-save-field accent-primary" type="radio"
                                       name="fields[{{ $field->id }}][value]"
                                       id="{{ $fieldId }}_{{ $loop->index }}"
                                       value="{{ $optVal }}"
                                       data-field-id="{{ $field->id }}"
                                       {{ $curVal == $optVal ? 'checked' : '' }}
                                       {{ !$isDraft ? 'disabled' : '' }}>
                                <span class="text-body-md text-on-surface">{{ $optLabel }}</span>
                            </label>
                            @endforeach
                        </div>

                    @elseif($field->tipe_field === 'textarea')
                        <textarea id="{{ $fieldId }}"
                                  class="auto-save-field w-full bg-surface-container-lowest border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl px-4 py-3 text-body-md text-on-surface transition-all {{ !$isDraft ? 'opacity-70' : '' }}"
                                  name="fields[{{ $field->id }}][value]"
                                  data-field-id="{{ $field->id }}"
                                  rows="3"
                                  {{ !$isDraft ? 'readonly' : '' }}>{{ $curVal }}</textarea>

                    @elseif($field->tipe_field === 'date')
                        <input type="date" id="{{ $fieldId }}"
                               class="auto-save-field w-full bg-surface-container-lowest border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl px-4 py-3 text-body-md text-on-surface transition-all {{ !$isDraft ? 'opacity-70' : '' }}"
                               name="fields[{{ $field->id }}][value]"
                               data-field-id="{{ $field->id }}"
                               value="{{ $curVal }}"
                               {{ !$isDraft ? 'readonly' : '' }}>

                    @elseif($field->tipe_field === 'number')
                        <input type="number" id="{{ $fieldId }}"
                               class="auto-save-field w-full bg-surface-container-lowest border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl px-4 py-3 text-body-md text-on-surface transition-all {{ !$isDraft ? 'opacity-70' : '' }}"
                               name="fields[{{ $field->id }}][value]"
                               data-field-id="{{ $field->id }}"
                               value="{{ $curVal }}"
                               {{ !$isDraft ? 'readonly' : '' }}>

                    @else
                        <input type="text" id="{{ $fieldId }}"
                               class="auto-save-field w-full bg-surface-container-lowest border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 rounded-xl px-4 py-3 text-body-md text-on-surface transition-all {{ !$isDraft ? 'opacity-70' : '' }}"
                               name="fields[{{ $field->id }}][value]"
                               data-field-id="{{ $field->id }}"
                               value="{{ $curVal }}"
                               {{ !$isDraft ? 'readonly' : '' }}>
                    @endif

                    <input type="hidden" name="fields[{{ $field->id }}][formulir_field_id]" value="{{ $field->id }}">
                </div>
                @endforeach
            </div>
        </form>
    </div>

</div>
@endif
