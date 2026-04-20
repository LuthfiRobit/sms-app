{{-- TAB 2 — Data Pribadi --}}
<div class="tab-pane-profil d-none" id="pane-pribadi">
    <div class="profil-card">
        <div class="profil-card-header">
            <i class="bi bi-person-vcard" aria-hidden="true"></i>Data Pribadi (Dapodik)
        </div>
        <div class="profil-card-body">
            <form id="form-pribadi" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="pribadi">

                {{-- Upload foto di dalam tab --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3"
                        style="background:var(--c-primary-50);border-radius:var(--r-md);border:1px solid var(--c-primary-light)">
                    <img id="foto-preview-tab"
                            src="{{ $peserta->foto
                                ? asset('storage/'.$peserta->foto)
                                : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=16a34a&color=fff&bold=true&rounded=true' }}"
                            class="rounded-circle object-fit-cover flex-shrink-0"
                            style="width:72px;height:72px;box-shadow:0 0 0 3px #fff,0 0 0 5px var(--c-primary-light)"
                            alt="Foto Profil">
                    <div>
                        <label class="form-label" for="input-foto-tab">Foto Profil</label>
                        <input type="file" name="foto" id="input-foto-tab" accept="image/*"
                                class="form-control form-control-sm" style="max-width:260px">
                        <div class="form-text">JPG/PNG/WebP · maks. 2 MB .</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="pribadi-nama">Nama Lengkap <span class="text-danger" aria-label="wajib">*</span></label>
                        <input type="text" id="pribadi-nama" name="nama_lengkap" class="form-control"
                                value="{{ $peserta->nama_lengkap }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-block">Jenis Kelamin</label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" id="jk-l"
                                        {{ $peserta->jenis_kelamin === 'L' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk-l">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" id="jk-p"
                                        {{ $peserta->jenis_kelamin === 'P' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk-p">Perempuan</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="pribadi-tempat-lahir">Tempat Lahir</label>
                        <input type="text" id="pribadi-tempat-lahir" name="tempat_lahir" class="form-control"
                                value="{{ $peserta->tempat_lahir }}" placeholder="Kota tempat lahir">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="pribadi-tgl-lahir">Tanggal Lahir</label>
                        <input type="date" id="pribadi-tgl-lahir" name="tanggal_lahir" class="form-control"
                                value="{{ $peserta->tanggal_lahir?->format('Y-m-d') }}"
                                max="{{ now()->subDay()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="pribadi-agama">Agama</label>
                        <select id="pribadi-agama" name="agama" class="form-select">
                            <option value="">— Pilih Agama —</option>
                            @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $ag)
                                <option value="{{ $ag }}" {{ $peserta->agama === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="pribadi-kebutuhan">Kebutuhan Khusus</label>
                        <input type="text" id="pribadi-kebutuhan" name="kebutuhan_khusus" class="form-control"
                                value="{{ $peserta->kebutuhan_khusus }}" placeholder="Kosongkan jika tidak ada">
                    </div>

                    {{-- NIK --}}
                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-2" for="pribadi-nik">
                            NIK
                            @if($peserta->nik)
                                <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                        title="Sudah terkunci, hubungi Admin jika ada kesalahan">
                                    🔒 Hanya Admin
                                </span>
                            @else
                                <span class="badge bg-info text-dark" data-bs-toggle="tooltip"
                                        title="Dapat diisi satu kali — pastikan benar">
                                    🔓 Sekali Isi
                                </span>
                            @endif
                        </label>
                        <input type="text" id="pribadi-nik"
                                name="{{ $peserta->nik ? '' : 'nik' }}"
                                class="form-control {{ $peserta->nik ? 'bg-light' : '' }}"
                                value="{{ $peserta->nik }}"
                                placeholder="{{ $peserta->nik ? '' : '16 digit NIK' }}"
                                maxlength="16"
                                {{ $peserta->nik ? 'readonly' : '' }}>
                    </div>

                    {{-- NISN --}}
                    <div class="col-md-6">
                        <label class="form-label d-flex align-items-center gap-2" for="pribadi-nisn">
                            NISN
                            @if($peserta->nisn)
                                <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                        title="Sudah terkunci, hubungi Admin jika ada kesalahan">
                                    🔒 Hanya Admin
                                </span>
                            @else
                                <span class="badge bg-info text-dark" data-bs-toggle="tooltip"
                                        title="Dapat diisi satu kali — pastikan benar">
                                    🔓 Sekali Isi
                                </span>
                            @endif
                        </label>
                        <input type="text" id="pribadi-nisn"
                                name="{{ $peserta->nisn ? '' : 'nisn' }}"
                                class="form-control {{ $peserta->nisn ? 'bg-light' : '' }}"
                                value="{{ $peserta->nisn }}"
                                placeholder="{{ $peserta->nisn ? '' : '10 digit NISN' }}"
                                maxlength="10"
                                {{ $peserta->nisn ? 'readonly' : '' }}>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-save">
                            <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Data Pribadi</span>
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
{{-- /pane-pribadi --}}