<form id="lembaga-form">
    <input type="hidden" id="lembaga-id" name="id">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kode <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="f-kode" name="kode" maxlength="20" required placeholder="e.g. MINU-KRK">
        </div>
        <div class="col-md-8">
            <label class="form-label">Nama Lembaga <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="f-nama" name="nama" maxlength="255" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jenis <span class="text-danger">*</span></label>
            <select class="form-select" id="f-jenis" name="jenis" required>
                <option value="">-- Pilih --</option>
                <option value="MI">MI (Madrasah Ibtidaiyah)</option>
                <option value="MTs">MTs (Madrasah Tsanawiyah)</option>
                <option value="SMP">SMP</option>
                <option value="MA">MA (Madrasah Aliyah)</option>
                <option value="SMK">SMK</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">NPSN</label>
            <input type="text" class="form-control" id="f-npsn" name="npsn" maxlength="20">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="f-status" name="status" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Non-Aktif</option>
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">Kepala Sekolah</label>
            <input type="text" class="form-control" id="f-kepala" name="kepala_sekolah" maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label">Telepon</label>
            <input type="text" class="form-control" id="f-telepon" name="telepon" maxlength="20">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="f-email" name="email" maxlength="100">
        </div>
        <div class="col-md-12">
            <label class="form-label">Alamat</label>
            <textarea class="form-control" id="f-alamat" name="alamat" rows="2"></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Urutan Tampil</label>
            <input type="number" class="form-control" id="f-urutan" name="urutan" value="0" min="0">
        </div>
        <div class="col-md-8">
            <label class="form-label">Logo</label>
            <input type="file" class="form-control" id="f-logo" name="logo" accept="image/*">
            <div class="form-text">Format: JPG/PNG, maks 2MB</div>
        </div>
    </div>
</form>
