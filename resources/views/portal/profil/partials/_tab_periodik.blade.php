{{-- TAB: Data Periodik --}}
<div class="tab-pane-profil hidden" id="pane-periodik">
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">monitor_heart</span>
            <h2 class="text-headline-sm text-on-surface">Data Periodik</h2>
        </div>
        <div class="p-6">
            <form id="form-periodik" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="periodik">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                    {{-- Tinggi Badan --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="p-tinggi">
                            Tinggi Badan <span class="text-outline text-[12px] font-normal">(cm)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">height</span>
                            </div>
                            <input type="number" id="p-tinggi" name="tinggi_badan"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $periodik?->tinggi_badan }}" min="50" max="250" step="0.1" placeholder="170">
                        </div>
                    </div>

                    {{-- Berat Badan --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="p-berat">
                            Berat Badan <span class="text-outline text-[12px] font-normal">(kg)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">scale</span>
                            </div>
                            <input type="number" id="p-berat" name="berat_badan"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $periodik?->berat_badan }}" min="10" max="200" step="0.1" placeholder="60">
                        </div>
                    </div>

                    {{-- Lingkar Kepala --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="p-lingkar">
                            Lingkar Kepala <span class="text-outline text-[12px] font-normal">(cm)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">face</span>
                            </div>
                            <input type="number" id="p-lingkar" name="lingkar_kepala"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $periodik?->lingkar_kepala }}" min="30" max="80" step="0.1" placeholder="54">
                        </div>
                    </div>

                    {{-- Jarak ke Sekolah --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="p-jarak">
                            Jarak ke Sekolah <span class="text-outline text-[12px] font-normal">(km)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">directions_car</span>
                            </div>
                            <input type="number" id="p-jarak" name="jarak_rumah"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $periodik?->jarak_rumah }}" min="0" step="0.1" placeholder="5.5">
                        </div>
                    </div>

                    {{-- Waktu Tempuh --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="p-waktu">
                            Waktu Tempuh <span class="text-outline text-[12px] font-normal">(menit)</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">schedule</span>
                            </div>
                            <input type="number" id="p-waktu" name="waktu_tempuh"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $periodik?->waktu_tempuh }}" min="0" placeholder="30">
                        </div>
                    </div>

                    {{-- Jumlah Saudara --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="jumlah_saudara">
                            Jumlah Saudara
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary transition-all">
                            <button type="button"
                                    class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant hover:bg-surface-container transition-colors flex-shrink-0 text-on-surface font-bold text-xl"
                                    onclick="stepNumber('jumlah_saudara', -1)" aria-label="Kurangi">−</button>
                            <input type="number" name="jumlah_saudara" id="jumlah_saudara"
                                   class="flex-1 bg-surface-container-lowest px-2 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none text-center min-w-0"
                                   value="{{ $periodik?->jumlah_saudara ?? 0 }}" min="0" max="20">
                            <button type="button"
                                    class="w-12 bg-surface-container-high flex items-center justify-center border-l border-outline-variant hover:bg-surface-container transition-colors flex-shrink-0 text-on-surface font-bold text-xl"
                                    onclick="stepNumber('jumlah_saudara', 1)" aria-label="Tambah">+</button>
                        </div>
                    </div>

                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Data Periodik
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
{{-- /pane-periodik --}}
