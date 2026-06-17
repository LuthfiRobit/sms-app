{{-- ══ SECTION 2 — UPLOAD DOKUMEN ══ --}}
@if($syarat->isNotEmpty())
<div class="bg-surface-container-lowest rounded-2xl soft-shadow overflow-hidden border-t-[3px] border-tertiary-fixed-dim">

    <div class="flex items-center justify-between px-6 py-4 bg-surface-container-low border-b border-outline-variant flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-[22px]">attach_file</span>
            <h2 class="text-headline-sm font-bold text-on-surface">Dokumen Persyaratan</h2>
        </div>
        <span class="text-body-sm text-on-surface-variant" id="dokumen-counter">{{ $prog['dokumen']['uploaded'] }}/{{ $prog['dokumen']['total'] }} dokumen wajib diupload</span>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($syarat as $s)
            @php
                $dok       = $dokMap->get($s->id);
                $hasDok    = !empty($dok);
                $isValid   = ($dok['status_verifikasi'] ?? '') === 'valid';
                $isInvalid = ($dok['status_verifikasi'] ?? '') === 'invalid';
                $statInfo  = match ($dok['status_verifikasi'] ?? 'none') {
                    'valid'   => ['icon'=>'check_circle', 'label'=>'Valid', 'cls'=>'bg-green-50 text-green-700 border-green-200'],
                    'invalid' => ['icon'=>'cancel',       'label'=>'Invalid', 'cls'=>'bg-red-50 text-red-700 border-red-200'],
                    'pending' => ['icon'=>'hourglass_empty','label'=>'Menunggu','cls'=>'bg-amber-50 text-amber-700 border-amber-200'],
                    default   => ['icon'=>'cloud_upload', 'label'=>'Belum Upload','cls'=>'bg-surface-container text-on-surface-variant border-outline-variant'],
                };
                $uploadUrl = route('ppdb.pendaftaran.dokumen.upload', [$pend?->id, $s->id]);
                $isPdf     = $hasDok && str_contains($dok['mime_type'] ?? '', 'pdf');
            @endphp

            <div class="bg-surface-container-low border border-outline-variant rounded-xl overflow-hidden flex flex-col"
                 id="dok-card-{{ $s->id }}" data-syarat-id="{{ $s->id }}">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-4 py-3 bg-surface-container border-b border-outline-variant gap-2 flex-wrap">
                    <div class="font-semibold text-label-md text-on-surface">{{ $s->nama }}</div>
                    <div class="flex items-center gap-2">
                        @if($s->wajib)
                            <span class="text-[11px] bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full font-semibold">Wajib</span>
                        @else
                            <span class="text-[11px] bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full">Opsional</span>
                        @endif
                        <span id="dok-status-{{ $s->id }}"
                              class="inline-flex items-center gap-1 {{ $statInfo['cls'] }} text-label-sm font-semibold px-2.5 py-1 rounded-full border text-[11px]">
                            <span class="material-symbols-outlined text-[13px]">{{ $statInfo['icon'] }}</span>
                            {{ $statInfo['label'] }}
                        </span>
                    </div>
                </div>

                @if($s->keterangan)
                    <p class="text-body-sm text-on-surface-variant px-4 pt-3">{{ $s->keterangan }}</p>
                @endif

                {{-- Upload progress (hidden by default) --}}
                <div class="hidden px-4 pt-3" id="dok-progress-{{ $s->id }}">
                    <div class="flex items-center gap-2 text-body-sm text-on-surface-variant mb-1">
                        <span>Mengupload...</span>
                        <span id="dok-pbar-pct-{{ $s->id }}" class="font-semibold ml-auto">0%</span>
                    </div>
                    <div class="h-2 bg-surface-container-high rounded-full overflow-hidden">
                        <div id="dok-pbar-{{ $s->id }}" class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all" style="width:0%"></div>
                    </div>
                </div>

                {{-- File info OR Dropzone --}}
                <div class="p-4 flex-1 flex flex-col justify-end">
                    @if($hasDok)
                        <div class="flex items-center gap-3" id="dok-file-row-{{ $s->id }}">
                            <div class="w-10 h-10 rounded-xl {{ $isPdf ? 'bg-red-50' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined {{ $isPdf ? 'text-red-500' : 'text-blue-500' }} text-[20px]" style="font-variation-settings:'FILL' 1">{{ $isPdf ? 'picture_as_pdf' : 'image' }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-label-md font-semibold text-on-surface truncate">{{ $dok['nama_file'] }}</div>
                                <div class="text-body-sm text-on-surface-variant">{{ number_format(($dok['ukuran_file'] ?? 0) / 1024, 1) }} KB</div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button type="button"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors"
                                        onclick="previewDokumen('{{ $dok['url_file'] ?? '' }}','{{ $isPdf ? 'pdf' : 'image' }}','{{ $dok['nama_file'] }}')"
                                        title="Preview">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                </button>
                                @if($isDraft && !$isValid)
                                    <label class="w-8 h-8 rounded-lg bg-surface-container text-on-surface-variant flex items-center justify-center hover:bg-surface-container-high transition-colors cursor-pointer" for="file-ganti-{{ $s->id }}" title="Ganti">
                                        <span class="material-symbols-outlined text-[16px]">swap_horiz</span>
                                    </label>
                                    <button type="button"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors"
                                            onclick="hapusDokumen({{ $dok['id'] }}, {{ $s->id }}, '{{ addslashes($dok['nama_file']) }}')"
                                            title="Hapus">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @if($isDraft && !$isValid)
                            <input type="file" id="file-ganti-{{ $s->id }}" class="file-input-hidden hidden" accept=".pdf,.jpg,.jpeg,.png"
                                   data-syarat-id="{{ $s->id }}" data-upload-url="{{ $uploadUrl }}">
                        @endif

                    @else
                        @if($isDraft)
                            <div class="dok-dropzone border-2 border-dashed border-outline-variant hover:border-primary rounded-xl p-6 flex flex-col items-center text-center cursor-pointer transition-colors bg-surface-container-lowest hover:bg-primary/5"
                                 id="dropzone-{{ $s->id }}"
                                 data-syarat-id="{{ $s->id }}"
                                 data-upload-url="{{ $uploadUrl }}"
                                 onclick="document.getElementById('file-input-{{ $s->id }}').click()"
                                 ondragover="event.preventDefault();this.classList.add('!border-primary','!bg-primary/10')"
                                 ondragleave="this.classList.remove('!border-primary','!bg-primary/10')"
                                 ondrop="handleDrop(event, {{ $s->id }}, '{{ $uploadUrl }}')">
                                <span class="material-symbols-outlined text-[36px] text-primary/40 mb-2">cloud_upload</span>
                                <div class="text-body-sm text-on-surface-variant">
                                    Seret file ke sini atau <span class="text-primary font-semibold">klik untuk memilih</span>
                                </div>
                                <div class="text-body-xs text-on-surface-variant mt-1 opacity-60">Format: PDF, JPG, PNG · Maks 5MB</div>
                                <input type="file" id="file-input-{{ $s->id }}" class="file-input-hidden hidden"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       data-syarat-id="{{ $s->id }}"
                                       data-upload-url="{{ $uploadUrl }}">
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-body-sm text-on-surface-variant py-3">
                                <span class="material-symbols-outlined text-[18px]">remove_circle_outline</span>
                                Dokumen belum diupload
                            </div>
                        @endif
                    @endif

                    {{-- Error message area --}}
                    <div class="hidden text-body-sm text-red-600 mt-2" id="dok-error-{{ $s->id }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endif
