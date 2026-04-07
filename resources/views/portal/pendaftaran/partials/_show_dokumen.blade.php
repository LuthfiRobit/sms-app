{{-- ══ SECTION 2 — UPLOAD DOKUMEN ══ --}}
@if($syarat->isNotEmpty())
    <div class="show-section mb-4">
        <div class="show-section-header">
            <div class="show-section-title">
                <i class="bi bi-paperclip me-2 text-success"></i>Dokumen Persyaratan
            </div>
            <span class="text-muted" id="dokumen-counter"
                style="font-size:.78rem;">{{ $prog['dokumen']['uploaded'] }}/{{ $prog['dokumen']['total'] }} dokumen wajib
                diupload</span>
        </div>
        <div class="show-section-body">
            <div class="dokumen-grid">
                @foreach($syarat as $s)
                    @php
                        $dok = $dokMap->get($s->id);
                        $hasDok = !empty($dok);
                        $isValid = ($dok['status_verifikasi'] ?? '') === 'valid';
                        $isInvalid = ($dok['status_verifikasi'] ?? '') === 'invalid';
                        $statBadge = match ($dok['status_verifikasi'] ?? 'none') {
                            'valid' => ['color' => 'success', 'label' => 'Valid ✓', 'icon' => 'bi-check-circle-fill'],
                            'invalid' => ['color' => 'danger', 'label' => 'Invalid ✗', 'icon' => 'bi-x-circle-fill'],
                            'pending' => ['color' => 'warning', 'label' => 'Menunggu', 'icon' => 'bi-hourglass-split'],
                            default => ['color' => 'secondary', 'label' => 'Belum Upload', 'icon' => 'bi-cloud-upload'],
                        };
                        $uploadUrl = route('ppdb.pendaftaran.dokumen.upload', [$pend?->id, $s->id]);
                        $hapusUrl = $hasDok ? route('ppdb.pendaftaran.dokumen.hapus', [$pend?->id, $dok['id']]) : '#';
                        $isPdf = $hasDok && str_contains($dok['mime_type'] ?? '', 'pdf');
                    @endphp
                    <div class="dok-card" id="dok-card-{{ $s->id }}" data-syarat-id="{{ $s->id }}">
                        <div class="dok-card-header">
                            <div class="dok-nama">{{ $s->nama }}</div>
                            <div class="d-flex align-items-center gap-2">
                                @if($s->wajib)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"
                                        style="font-size:.65rem;">Wajib</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size:.65rem;">Opsional</span>
                                @endif
                                <span class="badge bg-{{ $statBadge['color'] }}" id="dok-status-{{ $s->id }}"
                                    style="font-size:.7rem;">
                                    <i class="bi {{ $statBadge['icon'] }} me-1"></i>{{ $statBadge['label'] }}
                                </span>
                            </div>
                        </div>

                        @if($s->keterangan)
                            <div class="dok-keterangan">{{ $s->keterangan }}</div>
                        @endif

                        {{-- Progress bar upload (hidden by default) --}}
                        <div class="dok-upload-progress d-none" id="dok-progress-{{ $s->id }}">
                            <div class="dok-progress-track">
                                <div class="dok-progress-fill" id="dok-pbar-{{ $s->id }}" style="width:0%"></div>
                            </div>
                            <span class="dok-progress-label" id="dok-pbar-pct-{{ $s->id }}">0%</span>
                        </div>

                        @if($hasDok)
                            {{-- File sudah ada --}}
                            <div class="dok-file-row" id="dok-file-row-{{ $s->id }}">
                                <div class="dok-file-info">
                                    <i
                                        class="bi bi-{{ $isPdf ? 'file-earmark-pdf text-danger' : 'file-earmark-image text-primary' }} fs-5"></i>
                                    <div>
                                        <div class="dok-file-name">{{ $dok['nama_file'] }}</div>
                                        <div class="dok-file-size">{{ number_format(($dok['ukuran_file'] ?? 0) / 1024, 1) }} KB</div>
                                    </div>
                                </div>
                                <div class="dok-file-actions">
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="previewDokumen('{{ $dok['url_file'] ?? '' }}','{{ $isPdf ? 'pdf' : 'image' }}','{{ $dok['nama_file'] }}')">
                                        <i class="bi bi-eye me-1"></i>Preview
                                    </button>
                                    @if($isDraft && !$isValid)
                                        <label class="btn btn-sm btn-outline-secondary" for="file-ganti-{{ $s->id }}">
                                            <i class="bi bi-arrow-repeat me-1"></i>Ganti
                                        </label>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="hapusDokumen({{ $dok['id'] }}, {{ $s->id }}, '{{ addslashes($dok['nama_file']) }}')">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if($isDraft && !$isValid)
                                <input type="file" id="file-ganti-{{ $s->id }}" class="file-input-hidden" accept=".pdf,.jpg,.jpeg,.png"
                                    data-syarat-id="{{ $s->id }}" data-upload-url="{{ $uploadUrl }}">
                            @endif

                        @else
                            {{-- Belum ada dokumen —Area drag-and-drop --}}
                            @if($isDraft)
                                <div class="dok-dropzone" id="dropzone-{{ $s->id }}" data-syarat-id="{{ $s->id }}"
                                    data-upload-url="{{ $uploadUrl }}"
                                    onclick="document.getElementById('file-input-{{ $s->id }}').click()"
                                    ondragover="event.preventDefault();this.classList.add('dragover')"
                                    ondragleave="this.classList.remove('dragover')"
                                    ondrop="handleDrop(event, {{ $s->id }}, '{{ $uploadUrl }}')">
                                    <i class="bi bi-cloud-arrow-up dok-drop-icon"></i>
                                    <div class="dok-drop-text">Seret file ke sini atau <span class="text-success fw-semibold">klik untuk
                                            memilih</span></div>
                                    <div class="dok-drop-hint">Format: PDF, JPG, PNG · Maks 5MB</div>
                                    <input type="file" id="file-input-{{ $s->id }}" class="file-input-hidden"
                                        accept=".pdf,.jpg,.jpeg,.png" data-syarat-id="{{ $s->id }}" data-upload-url="{{ $uploadUrl }}">
                                </div>
                            @else
                                <div class="dok-empty-readonly">
                                    <i class="bi bi-dash-circle text-muted me-2"></i>Dokumen belum diupload
                                </div>
                            @endif
                        @endif

                        {{-- Error message area --}}
                        <div class="dok-error d-none text-danger" id="dok-error-{{ $s->id }}"
                            style="font-size:.8rem;margin-top:6px;"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
