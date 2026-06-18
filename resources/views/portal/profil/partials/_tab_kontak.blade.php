{{-- TAB: Kontak --}}
<div class="tab-pane-profil hidden" id="pane-kontak">
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">call</span>
            <h2 class="text-headline-sm text-on-surface">Kontak Peserta Didik</h2>
        </div>
        <div class="p-6">

            {{-- Info box: beda dengan data akun wali --}}
            <div class="flex items-start gap-3 bg-primary/5 border border-primary/20 rounded-xl p-4 mb-5">
                <span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0 mt-0.5">info</span>
                <div class="text-body-sm text-on-surface-variant">
                    <p class="font-semibold text-on-surface mb-0.5">Ini adalah kontak milik <span class="text-primary">peserta didik (siswa)</span>, bukan wali murid.</p>
                    <p>No HP & email wali murid sudah tercatat di tab <strong>Akun</strong>. Isi bagian ini hanya jika siswa memiliki nomor HP atau email sendiri yang berbeda.</p>
                </div>
            </div>

            <form id="form-kontak" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="kontak">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- No HP Siswa --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="kontak-hp">
                            No HP Siswa
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">phone_iphone</span>
                            </div>
                            <input type="text" id="kontak-hp" name="no_hp"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0 input-phone"
                                   value="{{ $kontak?->no_hp }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <p class="mt-1 text-body-sm text-on-surface-variant">Opsional · Isi jika siswa memiliki HP sendiri · 10–15 digit</p>
                    </div>

                    {{-- Email Siswa --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="kontak-email">Email Siswa</label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">mail</span>
                            </div>
                            <input type="email" id="kontak-email" name="email"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $kontak?->email }}" placeholder="email@contoh.com">
                        </div>
                        <p class="mt-1 text-body-sm text-on-surface-variant">Opsional · Email milik siswa (bukan wali murid)</p>
                    </div>

                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Kontak
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
{{-- /pane-kontak --}}
