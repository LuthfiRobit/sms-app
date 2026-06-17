{{-- TAB: Akun & Keamanan --}}
<div class="tab-pane-profil hidden flex flex-col gap-5" id="pane-akun">

    {{-- Informasi Akun --}}
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">manage_accounts</span>
            <h2 class="text-headline-sm text-on-surface">Informasi Akun</h2>
        </div>
        <div class="p-6">
            <form id="form-akun" novalidate>
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="akun-nama">
                            Nama Wali Murid <span class="text-error">*</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">person</span>
                            </div>
                            <input type="text" id="akun-nama" name="nama_lengkap"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $user->name }}" required minlength="3">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="akun-email">
                            Email Wali Murid <span class="text-error">*</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">mail</span>
                            </div>
                            <input type="email" id="akun-email" name="email"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $user->email }}" required>
                        </div>
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="akun-no-hp">
                            No HP Wali Murid <span class="text-error">*</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">phone_iphone</span>
                            </div>
                            <input type="tel" id="akun-no-hp" name="no_hp"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0 input-phone"
                                   value="{{ $user->no_hp }}" required inputmode="numeric" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="flex items-start gap-3 bg-surface-container-low border border-outline-variant rounded-xl p-3 self-end">
                        <span class="material-symbols-outlined text-primary text-[18px] flex-shrink-0 mt-0.5">info</span>
                        <p class="text-body-sm text-on-surface-variant">
                            Terdaftar sejak <strong>{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</strong>
                            · Status: <span class="{{ $user->status === 'active' ? 'text-secondary font-semibold' : 'text-tertiary font-semibold' }}">{{ ucfirst($user->status) }}</span>
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" id="btn-save-akun"
                            class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Perubahan
                        </span>
                        <span class="btn-spinner hidden items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Menyimpan…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ubah Password --}}
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">lock</span>
            <h2 class="text-headline-sm text-on-surface">Ubah Password</h2>
        </div>
        <div class="p-6">
            <form id="form-password" novalidate>
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    @foreach([
                        ['inp-pw-lama',  'password_lama',          'Password Lama',   'current-password'],
                        ['inp-pw-baru',  'password',               'Password Baru',   'new-password'],
                        ['inp-pw-conf',  'password_confirmation',  'Konfirmasi',      'new-password'],
                    ] as $pw)
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="{{ $pw[0] }}">
                            {{ $pw[2] }} <span class="text-error">*</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">lock</span>
                            </div>
                            <input type="password" id="{{ $pw[0] }}" name="{{ $pw[1] }}"
                                   class="flex-1 bg-surface-container-lowest px-3 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   autocomplete="{{ $pw[3] }}"
                                   @if($pw[0] === 'inp-pw-baru') minlength="8" @endif>
                            <button type="button"
                                    class="toggle-pw w-11 bg-surface-container-high flex items-center justify-center border-l border-outline-variant hover:bg-surface-container transition-colors flex-shrink-0"
                                    data-target="{{ $pw[0] }}"
                                    aria-label="Lihat/sembunyikan password">
                                <span class="material-symbols-outlined text-outline text-[20px]">visibility</span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" id="btn-save-pw"
                            class="btn-save bg-gradient-to-r from-tertiary to-tertiary-container text-on-tertiary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">key</span>
                            Ubah Password
                        </span>
                        <span class="btn-spinner hidden items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Menyimpan…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
{{-- /pane-akun --}}
