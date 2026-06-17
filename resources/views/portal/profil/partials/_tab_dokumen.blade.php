{{-- TAB: Dokumen Pribadi --}}
<div class="tab-pane-profil hidden" id="pane-dokumen">
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">folder_open</span>
            <h2 class="text-headline-sm text-on-surface">Dokumen Pribadi</h2>
        </div>
        <div class="p-6">

            {{-- Info Box --}}
            <div class="flex items-start gap-3 bg-surface-container-low border border-outline-variant rounded-xl p-4 mb-5">
                <span class="material-symbols-outlined text-primary text-[20px] flex-shrink-0 mt-0.5">info</span>
                <p class="text-body-sm text-on-surface-variant">
                    Isi nomor dokumen yang dimiliki. Dokumen yang tidak dimiliki dapat dikosongkan.
                </p>
            </div>

            <form id="form-dokumen" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="dokumen">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- KIP --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="dok-kip">
                            Nomor KIP
                            <span class="ml-1 text-[11px] text-on-surface-variant opacity-60" title="Kartu Indonesia Pintar — diberikan kepada siswa kurang mampu">(?)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">school</span>
                            </div>
                            <input type="text" id="dok-kip" name="no_kip" maxlength="30"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $dokumen?->no_kip }}" placeholder="Nomor KIP (opsional)">
                        </div>
                    </div>

                    {{-- PKH --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="dok-pkh">
                            Nomor PKH
                            <span class="ml-1 text-[11px] text-on-surface-variant opacity-60" title="Program Keluarga Harapan — program bantuan sosial pemerintah">(?)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">volunteer_activism</span>
                            </div>
                            <input type="text" id="dok-pkh" name="no_pkh" maxlength="30"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $dokumen?->no_pkh }}" placeholder="Nomor PKH (opsional)">
                        </div>
                    </div>

                    {{-- KITAS --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="dok-kitas">
                            Nomor KITAS
                            <span class="ml-1 text-[11px] text-on-surface-variant opacity-60" title="Kartu Izin Tinggal Terbatas — untuk WNA">(?)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">card_membership</span>
                            </div>
                            <input type="text" id="dok-kitas" name="no_kitas" maxlength="30"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $dokumen?->no_kitas }}" placeholder="Nomor KITAS (opsional)">
                        </div>
                    </div>

                    {{-- Paspor --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="dok-paspor">
                            Nomor Paspor
                            <span class="ml-1 text-[11px] text-on-surface-variant opacity-60" title="Nomor paspor untuk WNA atau peserta yang memiliki paspor">(?)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">travel_explore</span>
                            </div>
                            <input type="text" id="dok-paspor" name="no_paspor" maxlength="30"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $dokumen?->no_paspor }}" placeholder="Nomor Paspor (opsional)">
                        </div>
                    </div>

                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Dokumen
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
{{-- /pane-dokumen --}}
