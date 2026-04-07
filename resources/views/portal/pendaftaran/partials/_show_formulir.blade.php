{{-- ══ SECTION 1 — FORMULIR DINAMIS ══ --}}
@if($formulirFields->isNotEmpty())
    <div class="show-section mb-4">
        <div class="show-section-header">
            <div class="show-section-title">
                <i class="bi bi-ui-checks-grid me-2 text-success"></i>Formulir Pendaftaran
            </div>
            @if($isDraft)
                <div class="autosave-indicator" id="autosave-indicator">
                    <i class="bi bi-cloud-check me-1"></i><span id="autosave-text">Belum ada perubahan</span>
                </div>
            @endif
        </div>
        <div class="show-section-body">
            <form id="formulirForm" autocomplete="off">
                @foreach($formulirFields->where('tipe_field', '!=', 'file') as $field)
                    @php
                        $fv = $fvMap->get($field->id);
                        $curVal = $fv?->value ?? '';
                        $isStatis = $field->is_statis;
                        $required = $field->is_required;
                        $fieldId = 'field_' . $field->id;
                        $opsi = $field->opsi ?? [];

                        // Untuk field statis, ambil dari data Dapodik peserta via dapodik_key
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
                    <div class="form-field-wrapper {{ $isStatis ? 'field-statis' : '' }}">
                        <label class="form-label fw-semibold" for="{{ $fieldId }}">
                            {{ $field->label }}
                            @if($required) <span class="text-danger ms-1">*</span> @endif
                            @if($isStatis) <span class="badge bg-secondary-subtle text-secondary ms-2"
                            style="font-size:.65rem;">Data Profil</span> @endif
                        </label>

                        @if($isStatis)
                            {{-- READ ONLY dari Dapodik --}}
                            <div class="field-statis-val">{{ $curVal ?: '—' }}</div>
                            <input type="hidden" name="fields[{{ $field->id }}][formulir_field_id]" value="{{ $field->id }}">
                            <input type="hidden" name="fields[{{ $field->id }}][value]" value="{{ $curVal }}" data-field-id="{{ $field->id }}">
                            <div class="field-statis-hint">
                                <i class="bi bi-info-circle me-1"></i>Data dari profil —
                                <a href="{{ route('ppdb.profil.index') }}" target="_blank">ubah di halaman profil</a>
                            </div>
                        @elseif($field->tipe_field === 'select')
                            <select id="{{ $fieldId }}" class="form-select auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                                name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}" {{ !$isDraft ? 'disabled' : '' }}>
                                <option value="">— Pilih —</option>
                                @foreach($opsi as $opt)
                                    @php $optVal = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                                    $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['value'] ?? '') : $opt; @endphp
                                    <option value="{{ $optVal }}" {{ $curVal == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        @elseif($field->tipe_field === 'radio')
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($opsi as $opt)
                                    @php $optVal = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                                    $optLabel = is_array($opt) ? ($opt['label'] ?? '') : $opt; @endphp
                                    <div class="form-check">
                                        <input class="form-check-input auto-save-field" type="radio"
                                            name="fields[{{ $field->id }}][value]" id="{{ $fieldId }}_{{ $loop->index }}"
                                            value="{{ $optVal }}" data-field-id="{{ $field->id }}" {{ $curVal == $optVal ? 'checked' : '' }} {{ !$isDraft ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="{{ $fieldId }}_{{ $loop->index }}">{{ $optLabel }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($field->tipe_field === 'textarea')
                            <textarea id="{{ $fieldId }}"
                                class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                                name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}" rows="3" {{ !$isDraft ? 'readonly' : '' }}>{{ $curVal }}</textarea>
                        @elseif($field->tipe_field === 'date')
                            <input type="date" id="{{ $fieldId }}"
                                class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                                name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}" value="{{ $curVal }}" {{ !$isDraft ? 'readonly' : '' }}>
                        @elseif($field->tipe_field === 'number')
                            <input type="number" id="{{ $fieldId }}"
                                class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                                name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}" value="{{ $curVal }}" {{ !$isDraft ? 'readonly' : '' }}>
                        @else
                            <input type="text" id="{{ $fieldId }}"
                                class="form-control auto-save-field {{ !$isDraft ? 'readonly-field' : '' }}"
                                name="fields[{{ $field->id }}][value]" data-field-id="{{ $field->id }}" value="{{ $curVal }}" {{ !$isDraft ? 'readonly' : '' }}>
                        @endif

                        <input type="hidden" name="fields[{{ $field->id }}][formulir_field_id]" value="{{ $field->id }}">
                    </div>
                @endforeach
            </form>
        </div>
    </div>
@endif
