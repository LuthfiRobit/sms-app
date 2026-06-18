{{-- TAB: Orang Tua / Wali --}}
<div class="tab-pane-profil hidden" id="pane-ortu">
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">family_restroom</span>
            <h2 class="text-headline-sm text-on-surface">Data Orang Tua / Wali</h2>
            <span class="ml-1 text-body-sm text-on-surface-variant">(minimal isi Ayah atau Ibu)</span>
        </div>

        {{-- Accordion Sections --}}
        @foreach([
            ['key'=>'ayah', 'label'=>'Data Ayah',           'icon'=>'man',         'data'=>$ortu['ayah'],  'req'=>true],
            ['key'=>'ibu',  'label'=>'Data Ibu',             'icon'=>'woman',       'data'=>$ortu['ibu'],   'req'=>true],
            ['key'=>'wali', 'label'=>'Data Wali (Opsional)', 'icon'=>'supervisor_account', 'data'=>$ortu['wali'],  'req'=>false],
        ] as $idx => $ot)
        <div class="{{ !$loop->last ? 'border-b border-outline-variant' : '' }}">

            {{-- Accordion Header --}}
            <button type="button"
                    class="ortu-toggle w-full flex items-center justify-between px-6 py-4 hover:bg-surface-container transition-colors duration-150 text-left"
                    data-target="ortu-body-{{ $ot['key'] }}"
                    aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-[22px]">{{ $ot['icon'] }}</span>
                    <span class="text-headline-sm text-on-surface">{{ $ot['label'] }}</span>
                    @if(!empty($ot['data']?->nama))
                        <span class="text-[11px] bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded-full font-semibold">Terisi</span>
                    @endif
                </div>
                <span class="material-symbols-outlined text-outline text-[20px] ortu-chevron transition-transform duration-200 {{ $idx === 0 ? 'rotate-180' : '' }}">expand_more</span>
            </button>

            {{-- Accordion Body --}}
            <div id="ortu-body-{{ $ot['key'] }}"
                 class="{{ $idx === 0 ? '' : 'hidden' }} px-6 pb-6">
                <form id="form-{{ $ot['key'] }}" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="{{ $ot['key'] }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Nama --}}
                        <div class="md:col-span-2">
                            <label class="block text-label-md text-on-surface-variant mb-2">
                                Nama {{ ucfirst($ot['key']) }}
                                @if($ot['req']) <span class="text-error">*</span> @endif
                            </label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">person</span>
                                </div>
                                <input type="text" name="nama"
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $ot['data']?->nama }}"
                                       {{ $ot['req'] ? 'required' : '' }}
                                       placeholder="Nama lengkap {{ $ot['key'] }}">
                            </div>
                        </div>

                        {{-- NIK --}}
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2">NIK {{ ucfirst($ot['key']) }}</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">badge</span>
                                </div>
                                <input type="text" name="nik" maxlength="16"
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $ot['data']?->nik }}" placeholder="16 digit NIK">
                            </div>
                        </div>

                        {{-- Pekerjaan --}}
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2">Pekerjaan</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">work</span>
                                </div>
                                <input type="text" name="pekerjaan"
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                       value="{{ $ot['data']?->pekerjaan }}" placeholder="Contoh: Wiraswasta">
                            </div>
                        </div>

                        {{-- Penghasilan --}}
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2">Penghasilan / Bulan</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all relative">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">payments</span>
                                </div>
                                <select name="penghasilan"
                                        class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none appearance-none cursor-pointer min-w-0">
                                    <option value="">— Pilih Rentang —</option>
                                    @foreach(['< 500.000','500.000 - 1.000.000','1.000.001 - 2.000.000','2.000.001 - 5.000.000','> 5.000.000'] as $ph)
                                        <option value="{{ $ph }}" {{ $ot['data']?->penghasilan === $ph ? 'selected' : '' }}>Rp {{ $ph }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
                            </div>
                        </div>

                        {{-- Pendidikan --}}
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2">Pendidikan Terakhir</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all relative">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">school</span>
                                </div>
                                <select name="pendidikan"
                                        class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none appearance-none cursor-pointer min-w-0">
                                    <option value="">— Pilih —</option>
                                    @foreach(['SD' => 'SD/Sederajat', 'SMP' => 'SMP/Sederajat', 'SMA' => 'SMA/SMK/Sederajat', 'D1' => 'Diploma 1 (D1)', 'D2' => 'Diploma 2 (D2)', 'D3' => 'Diploma 3 (D3)', 'S1' => 'S1/D4', 'S2' => 'S2 (Magister)', 'S3' => 'S3 (Doktor)', 'Tidak_Sekolah' => 'Tidak Sekolah'] as $val => $label)
                                        <option value="{{ $val }}" {{ $ot['data']?->pendidikan === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
                            </div>
                        </div>

                        {{-- No HP --}}
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-2">Nomor HP</label>
                            <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-outline text-[20px]">call</span>
                                </div>
                                <input type="text" name="no_hp"
                                       class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0 input-phone"
                                       value="{{ $ot['data']?->no_hp }}" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                            <span class="btn-text flex items-center gap-2">
                                <span class="material-symbols-outlined text-[20px]">save</span>
                                Simpan {{ ucfirst($ot['key']) }}
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
        @endforeach

    </div>
</div>
{{-- /pane-ortu --}}
