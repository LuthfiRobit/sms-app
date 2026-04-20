{{-- ─────────────────────────────────────
TAB 3 — Alamat
───────────────────────────────────── --}}
<div class="tab-pane-profil d-none" id="pane-alamat">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-geo-alt" aria-hidden="true"></i>Data Alamat
        </div>
        <div class="profil-card-body">
            <form id="form-alamat" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="alamat">
                <input type="hidden" name="lintang" id="inp-lintang" value="{{ $alamat?->lintang }}">
                <input type="hidden" name="bujur" id="inp-bujur" value="{{ $alamat?->bujur }}">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="alamat-lengkap">Alamat Lengkap <span class="text-danger"
                                aria-label="wajib">*</span></label>
                        <textarea id="alamat-lengkap" name="alamat" class="form-control" rows="2" required
                            placeholder="Jl. nama jalan, nomor rumah">{{ $alamat?->alamat }}</textarea>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="alamat-rt">RT</label>
                        <input type="text" id="alamat-rt" name="rt" class="form-control" value="{{ $alamat?->rt }}"
                            placeholder="001">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="alamat-rw">RW</label>
                        <input type="text" id="alamat-rw" name="rw" class="form-control" value="{{ $alamat?->rw }}"
                            placeholder="002">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="alamat-desa">Desa/Kelurahan</label>
                        <input type="text" id="alamat-desa" name="desa_kelurahan" class="form-control"
                            value="{{ $alamat?->desa_kelurahan }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="alamat-kec">Kecamatan</label>
                        <input type="text" id="alamat-kec" name="kecamatan" class="form-control"
                            value="{{ $alamat?->kecamatan }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="alamat-kab">Kabupaten/Kota <span class="text-danger"
                                aria-label="wajib">*</span></label>
                        <input type="text" id="alamat-kab" name="kabupaten_kota" class="form-control"
                            value="{{ $alamat?->kabupaten_kota }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="alamat-prov">Provinsi <span class="text-danger"
                                aria-label="wajib">*</span></label>
                        <input type="text" id="alamat-prov" name="provinsi" class="form-control"
                            value="{{ $alamat?->provinsi }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="alamat-pos">Kode Pos</label>
                        <input type="text" id="alamat-pos" name="kode_pos" class="form-control"
                            value="{{ $alamat?->kode_pos }}" maxlength="10">
                    </div>

                    {{-- GPS Helper --}}
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" id="btn-gps" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-geo me-1" aria-hidden="true"></i>Gunakan GPS
                            </button>
                            <span id="gps-status" class="text-muted" style="font-size:.78rem" aria-live="polite"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan
                                Alamat</span>
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
</div>{{-- /pane-alamat --}}