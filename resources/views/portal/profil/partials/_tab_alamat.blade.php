{{-- TAB: Alamat --}}
<div class="tab-pane-profil hidden" id="pane-alamat">
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">location_on</span>
            <h2 class="text-headline-sm text-on-surface">Data Alamat</h2>
        </div>
        <div class="p-6">
            <form id="form-alamat" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="alamat">
                <input type="hidden" name="lintang" id="inp-lintang" value="{{ $alamat?->lintang }}">
                <input type="hidden" name="bujur" id="inp-bujur" value="{{ $alamat?->bujur }}">

                <div class="flex flex-col gap-4">

                    {{-- Alamat Lengkap --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="alamat-lengkap">
                            Alamat Lengkap <span class="text-error">*</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-start pt-3 justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">home</span>
                            </div>
                            <textarea id="alamat-lengkap" name="alamat" rows="3" required
                                      class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0 resize-y"
                                      placeholder="Jl. nama jalan, nomor rumah">{{ $alamat?->alamat }}</textarea>
                        </div>
                    </div>

                    {{-- RT RW Desa Kecamatan --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach([
                            ['alamat-rt',   'rt',            'RT',            '001'],
                            ['alamat-rw',   'rw',            'RW',            '002'],
                            ['alamat-desa', 'desa_kelurahan','Desa/Kelurahan',''],
                            ['alamat-kec',  'kecamatan',     'Kecamatan',     ''],
                        ] as $f)
                        <div class="{{ $loop->index >= 2 ? 'col-span-2 md:col-span-1' : '' }}">
                            <label class="block text-label-md text-on-surface-variant mb-2" for="{{ $f[0] }}">{{ $f[2] }}</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <input type="text" id="{{ $f[0] }}" name="{{ $f[1] }}"
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $alamat?->{$f[1]} }}"
                                       @if($f[3]) placeholder="{{ $f[3] }}" @endif>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Kabupaten Provinsi Kode Pos --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2" for="alamat-kab">
                                Kabupaten/Kota <span class="text-error">*</span>
                            </label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">location_city</span>
                                </div>
                                <input type="text" id="alamat-kab" name="kabupaten_kota" required
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $alamat?->kabupaten_kota }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2" for="alamat-prov">
                                Provinsi <span class="text-error">*</span>
                            </label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">map</span>
                                </div>
                                <input type="text" id="alamat-prov" name="provinsi" required
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $alamat?->provinsi }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2" for="alamat-pos">Kode Pos</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">markunread_mailbox</span>
                                </div>
                                <input type="text" id="alamat-pos" name="kode_pos" maxlength="10"
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $alamat?->kode_pos }}">
                            </div>
                        </div>
                    </div>

                    {{-- GPS Helper --}}
                    <div class="flex items-center gap-3">
                        <button type="button" id="btn-gps"
                                class="flex items-center gap-2 px-4 py-2 border border-primary text-primary text-label-md rounded-lg hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[18px]">my_location</span>
                            Gunakan GPS
                        </button>
                        <span id="gps-status" class="text-body-sm text-on-surface-variant" aria-live="polite"></span>
                    </div>

                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Alamat
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
{{-- /pane-alamat --}}
