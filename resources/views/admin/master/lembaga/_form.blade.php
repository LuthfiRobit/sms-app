@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush
@push('vendor-scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

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

        <div class="col-md-12">
            <hr class="my-1">
            <label class="form-label mb-0">Titik Lokasi Sekolah</label>
            <div class="form-text mt-0 mb-2">Dipakai untuk memvalidasi jarak guru saat absen (geofence). Kosongkan jika belum ingin mengaktifkan validasi lokasi untuk lembaga ini.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Latitude</label>
            <input type="number" step="any" class="form-control" id="f-latitude" name="latitude" placeholder="-7.858300">
        </div>
        <div class="col-md-4">
            <label class="form-label">Longitude</label>
            <input type="number" step="any" class="form-control" id="f-longitude" name="longitude" placeholder="113.378200">
        </div>
        <div class="col-md-4">
            <label class="form-label">Radius (meter)</label>
            <input type="number" step="1" min="10" max="5000" class="form-control" id="f-radius" name="radius_meter" placeholder="100">
        </div>
        <div class="col-md-12">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-ambil-lokasi">
                <i class="bi bi-geo-alt"></i> Ambil Lokasi Saat Ini
            </button>
            <a href="#" target="_blank" id="link-lihat-peta" class="btn btn-outline-secondary btn-sm d-none">
                <i class="bi bi-map"></i> Lihat di Google Maps
            </a>
            <div class="form-text">Buka halaman ini dari HP/laptop yang sedang berada di lokasi sekolah, lalu klik tombol di atas untuk mengisi koordinat secara otomatis. Atau klik/geser pin langsung di peta di bawah.</div>
        </div>
        <div class="col-md-12">
            <div id="map-lokasi" style="height: 260px; border-radius: 8px;"></div>
            <div class="form-text mt-1" id="lokasi-nama"></div>
        </div>
    </div>
</form>
