{{-- ─────────────────────────────────────
TAB 7 — Dokumen Pribadi
───────────────────────────────────── --}}
<div class="tab-pane-profil d-none" id="pane-dokumen">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-file-earmark-text" aria-hidden="true"></i>Dokumen Pribadi
        </div>
        <div class="profil-card-body">
            <div class="info-box mb-4">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <span>Isi nomor dokumen yang dimiliki. Dokumen yang tidak dimiliki dapat dikosongkan.</span>
            </div>
            <form id="form-dokumen" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="dokumen">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-1" for="dok-kip">
                            Nomor KIP
                            <i class="bi bi-question-circle text-muted" aria-hidden="true" data-bs-toggle="tooltip"
                                title="Kartu Indonesia Pintar — diberikan kepada siswa kurang mampu"></i>
                        </label>
                        <input type="text" id="dok-kip" name="no_kip" class="form-control"
                            value="{{ $dokumen?->no_kip }}" maxlength="30" placeholder="Nomor KIP (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-1" for="dok-pkh">
                            Nomor PKH
                            <i class="bi bi-question-circle text-muted" aria-hidden="true" data-bs-toggle="tooltip"
                                title="Program Keluarga Harapan — program bantuan sosial pemerintah"></i>
                        </label>
                        <input type="text" id="dok-pkh" name="no_pkh" class="form-control"
                            value="{{ $dokumen?->no_pkh }}" maxlength="30" placeholder="Nomor PKH (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-1" for="dok-kitas">
                            Nomor KITAS
                            <i class="bi bi-question-circle text-muted" aria-hidden="true" data-bs-toggle="tooltip"
                                title="Kartu Izin Tinggal Terbatas — untuk WNA"></i>
                        </label>
                        <input type="text" id="dok-kitas" name="no_kitas" class="form-control"
                            value="{{ $dokumen?->no_kitas }}" maxlength="30" placeholder="Nomor KITAS (opsional)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-1" for="dok-paspor">
                            Nomor Paspor
                            <i class="bi bi-question-circle text-muted" aria-hidden="true" data-bs-toggle="tooltip"
                                title="Nomor paspor untuk WNA atau peserta yang memiliki paspor"></i>
                        </label>
                        <input type="text" id="dok-paspor" name="no_paspor" class="form-control"
                            value="{{ $dokumen?->no_paspor }}" maxlength="30" placeholder="Nomor Paspor (opsional)">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan
                                Dokumen</span>
                            <span class="btn-spinner d-none" aria-live="polite">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>{{-- /pane-dokumen --}}