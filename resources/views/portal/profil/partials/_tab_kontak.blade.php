{{-- ─────────────────────────────────────
TAB 6 — Kontak
───────────────────────────────────── --}}
<div class="tab-pane-profil d-none" id="pane-kontak">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-telephone" aria-hidden="true"></i>Data Kontak
        </div>
        <div class="profil-card-body">
            <form id="form-kontak" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="kontak">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="kontak-hp">Nomor HP <span class="text-danger"
                                aria-label="wajib">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone" aria-hidden="true"></i></span>
                            <input type="text" id="kontak-hp" name="no_hp" class="form-control input-phone"
                                value="{{ $kontak?->no_hp }}" required placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="form-text">Hanya angka · 10–15 digit</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="kontak-email">Email Kontak</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                            <input type="email" id="kontak-email" name="email" class="form-control"
                                value="{{ $kontak?->email }}" placeholder="email@contoh.com">
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan
                                Kontak</span>
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
</div>{{-- /pane-kontak --}}