 {{-- TAB 1 — Akun & Keamanan --}}
        <div class="tab-pane-profil" id="pane-akun">

            {{-- Informasi Akun --}}
            <div class="profil-card mb-4">
                <div class="profil-card-header">
                    <i class="bi bi-person-gear" aria-hidden="true"></i>Informasi Akun
                </div>
                <div class="profil-card-body">
                    <form id="form-akun" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="akun-nama">Nama Lengkap <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="text" id="akun-nama" name="nama_lengkap" class="form-control"
                                       value="{{ $user->name }}" required minlength="3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="akun-email">Email <span class="text-danger" aria-label="wajib">*</span></label>
                                <input type="email" id="akun-email" name="email" class="form-control"
                                       value="{{ $user->email }}" required>
                            </div>
                            <div class="col-12">
                                {{-- [DIUBAH] Info box diperhalus dengan ikon yang lebih relevan --}}
                                <div class="info-box">
                                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                                    <span>Terdaftar sejak <strong>{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</strong>
                                    &nbsp;·&nbsp; Status:
                                    <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($user->status) }}
                                    </span></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-save" id="btn-save-akun">
                                    <span class="btn-text"><i class="bi bi-check2 me-1" aria-hidden="true"></i>Simpan Perubahan</span>
                                    <span class="btn-spinner d-none" aria-live="polite">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ubah Password --}}
            <div class="profil-card">
                <div class="profil-card-header">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>Ubah Password
                </div>
                <div class="profil-card-body">
                    <form id="form-password" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="inp-pw-lama">Password Lama <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_lama" id="inp-pw-lama" class="form-control" autocomplete="current-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-lama" aria-label="Lihat/sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="inp-pw-baru">Password Baru <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="inp-pw-baru" class="form-control" minlength="8" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-baru" aria-label="Lihat/sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="inp-pw-conf">Konfirmasi <span class="text-danger" aria-label="wajib">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="inp-pw-conf" class="form-control" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="inp-pw-conf" aria-label="Lihat/sembunyikan password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning fw-semibold btn-save" id="btn-save-pw">
                                    <span class="btn-text"><i class="bi bi-key me-1" aria-hidden="true"></i>Ubah Password</span>
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
        {{-- /pane-akun --}}