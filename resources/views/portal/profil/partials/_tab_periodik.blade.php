{{-- ─────────────────────────────────────
TAB 5 — Data Periodik
[DIUBAH] Input number di-wrap lebih
rapi menggunakan input-group stepper
───────────────────────────────────── --}}
<div class="tab-pane-profil d-none" id="pane-periodik">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-heart-pulse" aria-hidden="true"></i>Data Periodik
        </div>
        <div class="profil-card-body">
            <form id="form-periodik" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="periodik">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="p-tinggi">Tinggi Badan <span
                                class="text-muted fw-normal">(cm)</span></label>
                        <input type="number" id="p-tinggi" name="tinggi_badan" class="form-control"
                            value="{{ $periodik?->tinggi_badan }}" min="50" max="250" step="0.1" placeholder="170">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="p-berat">Berat Badan <span
                                class="text-muted fw-normal">(kg)</span></label>
                        <input type="number" id="p-berat" name="berat_badan" class="form-control"
                            value="{{ $periodik?->berat_badan }}" min="10" max="200" step="0.1" placeholder="60">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="p-lingkar">Lingkar Kepala <span
                                class="text-muted fw-normal">(cm)</span></label>
                        <input type="number" id="p-lingkar" name="lingkar_kepala" class="form-control"
                            value="{{ $periodik?->lingkar_kepala }}" min="30" max="80" step="0.1" placeholder="54">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="p-jarak">Jarak ke Sekolah <span
                                class="text-muted fw-normal">(km)</span></label>
                        <input type="number" id="p-jarak" name="jarak_rumah" class="form-control"
                            value="{{ $periodik?->jarak_rumah }}" min="0" step="0.1" placeholder="5.5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="p-waktu">Waktu Tempuh <span
                                class="text-muted fw-normal">(menit)</span></label>
                        <input type="number" id="p-waktu" name="waktu_tempuh" class="form-control"
                            value="{{ $periodik?->waktu_tempuh }}" min="0" placeholder="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="jumlah_saudara">Jumlah Saudara</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="stepNumber('jumlah_saudara',-1)" aria-label="Kurangi">−</button>
                            <input type="number" name="jumlah_saudara" id="jumlah_saudara"
                                class="form-control text-center" value="{{ $periodik?->jumlah_saudara ?? 0 }}" min="0"
                                max="20">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="stepNumber('jumlah_saudara',1)" aria-label="Tambah">+</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Data
                                Periodik</span>
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
</div>{{-- /pane-periodik --}}