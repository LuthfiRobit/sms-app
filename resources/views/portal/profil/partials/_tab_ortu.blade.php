{{-- ─────────────────────────────────────
             TAB 4 — Orang Tua / Wali
             [DIUBAH] Accordion tetap, hanya
             diperbaiki aksesibilitas dan spacing
             ───────────────────────────────────── --}}
        <div class="tab-pane-profil d-none" id="pane-ortu">
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-people" aria-hidden="true"></i>Data Orang Tua / Wali
                    <small class="ms-1 fw-normal" style="color:var(--c-primary-dark);opacity:.7">(minimal isi Ayah atau Ibu)</small>
                </div>
                <div class="profil-card-body p-0">
                    <div class="accordion accordion-flush" id="accordionOrtu">
                        @foreach([
                            ['key'=>'ayah', 'label'=>'Data Ayah',          'icon'=>'bi-person',      'data'=>$ortu['ayah']],
                            ['key'=>'ibu',  'label'=>'Data Ibu',            'icon'=>'bi-person',      'data'=>$ortu['ibu']],
                            ['key'=>'wali', 'label'=>'Data Wali (Opsional)','icon'=>'bi-person-check','data'=>$ortu['wali']],
                        ] as $idx => $ot)
                        <div class="accordion-item border-0 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $idx > 0 ? 'collapsed' : '' }} fw-semibold"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $ot['key'] }}"
                                        aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse-{{ $ot['key'] }}">
                                    <i class="bi {{ $ot['icon'] }} me-2 text-success" aria-hidden="true"></i>
                                    {{ $ot['label'] }}
                                    @if(!empty($ot['data']?->nama))
                                        <span class="badge bg-success ms-2" style="font-size:.65rem">Terisi</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="collapse-{{ $ot['key'] }}"
                                 class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}"
                                 data-bs-parent="#accordionOrtu">
                                <div class="accordion-body">
                                    <form id="form-{{ $ot['key'] }}" novalidate>
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="section" value="{{ $ot['key'] }}">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    Nama {{ ucfirst($ot['key']) }}
                                                    @if($ot['key'] !== 'wali') <span class="text-danger" aria-label="wajib">*</span> @endif
                                                </label>
                                                <input type="text" name="nama" class="form-control"
                                                       value="{{ $ot['data']?->nama }}"
                                                       {{ $ot['key'] !== 'wali' ? 'required' : '' }}
                                                       placeholder="Nama lengkap {{ $ot['key'] }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">NIK {{ ucfirst($ot['key']) }}</label>
                                                <input type="text" name="nik" class="form-control"
                                                       value="{{ $ot['data']?->nik }}" maxlength="16" placeholder="16 digit NIK">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Pekerjaan</label>
                                                <input type="text" name="pekerjaan" class="form-control" value="{{ $ot['data']?->pekerjaan }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Penghasilan / Bulan</label>
                                                <select name="penghasilan" class="form-select">
                                                    <option value="">— Pilih Rentang —</option>
                                                    @foreach(['< 500.000','500.000 - 1.000.000','1.000.001 - 2.000.000','2.000.001 - 5.000.000','> 5.000.000'] as $ph)
                                                        <option value="{{ $ph }}" {{ $ot['data']?->penghasilan === $ph ? 'selected' : '' }}>Rp {{ $ph }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Pendidikan Terakhir</label>
                                                <select name="pendidikan" class="form-select">
                                                    <option value="">— Pilih —</option>
                                                    @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3','Tidak Sekolah'] as $pdd)
                                                        <option value="{{ $pdd }}" {{ $ot['data']?->pendidikan === $pdd ? 'selected' : '' }}>{{ $pdd }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nomor HP</label>
                                                <input type="text" name="no_hp" class="form-control input-phone"
                                                       value="{{ $ot['data']?->no_hp }}" placeholder="08xxxxxxxxxx">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success btn-save btn-sm">
                                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan {{ ucfirst($ot['key']) }}</span>
                                                    <span class="btn-spinner d-none" aria-live="polite">
                                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>{{-- /pane-ortu --}}