{{-- TAB: Data Pribadi --}}
<div class="tab-pane-profil hidden" id="pane-pribadi">
    <div class="bg-surface-container-lowest rounded-xl border-t-[3px] border-tertiary-fixed-dim soft-shadow overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-surface-container-low border-b border-outline-variant">
            <span class="material-symbols-outlined text-primary text-[22px]">badge</span>
            <h2 class="text-headline-sm text-on-surface">Data Pribadi (Dapodik)</h2>
        </div>
        <div class="p-6">
            <form id="form-pribadi" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="pribadi">

                {{-- Foto Upload --}}
                <div class="flex items-center gap-4 mb-6 p-4 bg-surface-container-low rounded-xl border border-outline-variant">
                    <img id="foto-preview-tab"
                         src="{{ $peserta->foto ? asset('storage/'.$peserta->foto) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=80&background=00843d&color=fff&bold=true&rounded=true' }}"
                         class="w-16 h-16 rounded-xl object-cover ring-2 ring-primary/20 flex-shrink-0"
                         alt="Foto Profil">
                    <div class="flex-1 min-w-0">
                        <label class="block text-label-md text-on-surface-variant mb-2" for="input-foto-tab">Foto Profil</label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">photo_camera</span>
                            </div>
                            <input type="file" name="foto" id="input-foto-tab" accept="image/*"
                                   class="flex-1 bg-surface-container-lowest px-4 py-2.5 text-body-sm text-on-surface border-none focus:ring-0 focus:outline-none min-w-0 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-body-sm file:font-semibold file:bg-surface-container file:text-on-surface-variant hover:file:bg-surface-container-high">
                        </div>
                        <p class="mt-1 text-body-sm text-on-surface-variant">JPG/PNG/WebP · maks. 2 MB</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Nama Lengkap --}}
                    <div class="md:col-span-2">
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-nama">
                            Nama Lengkap Siswa <span class="text-error">*</span>
                        </label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">person</span>
                            </div>
                            <input type="text" id="pribadi-nama" name="nama_lengkap"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $peserta->nama_lengkap }}" required
                                   placeholder="Sesuai Akta Kelahiran atau Ijazah">
                        </div>
                    </div>

                    {{-- Tempat Lahir --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-tempat-lahir">Tempat Lahir</label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">location_on</span>
                            </div>
                            <input type="text" id="pribadi-tempat-lahir" name="tempat_lahir"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $peserta->tempat_lahir }}" placeholder="Kota/Kabupaten">
                        </div>
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-tgl-lahir">Tanggal Lahir</label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">calendar_today</span>
                            </div>
                            <input type="date" id="pribadi-tgl-lahir" name="tanggal_lahir"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $peserta->tanggal_lahir?->format('Y-m-d') }}"
                                   max="{{ now()->subDay()->format('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Agama --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-agama">Agama</label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all relative">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">church</span>
                            </div>
                            <select id="pribadi-agama" name="agama"
                                    class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none appearance-none cursor-pointer min-w-0">
                                <option value="">— Pilih Agama —</option>
                                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $ag)
                                    <option value="{{ $ag }}" {{ $peserta->agama === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
                        </div>
                    </div>

                    {{-- Kebutuhan Khusus --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-kebutuhan">Kebutuhan Khusus</label>
                        <div class="flex rounded-lg overflow-hidden border border-outline-variant focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">accessibility</span>
                            </div>
                            <input type="text" id="pribadi-kebutuhan" name="kebutuhan_khusus"
                                   class="flex-1 bg-surface-container-lowest px-4 py-3 text-body-md text-on-surface border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $peserta->kebutuhan_khusus }}" placeholder="Kosongkan jika tidak ada">
                        </div>
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="md:col-span-2">
                        <label class="block text-label-md text-on-surface-variant mb-3">Jenis Kelamin</label>
                        <div class="grid grid-cols-2 gap-3 max-w-sm">
                            @foreach([['L','Laki-laki','man'], ['P','Perempuan','woman']] as $jk)
                            <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all duration-150
                                {{ $peserta->jenis_kelamin === $jk[0] ? 'border-primary bg-surface-container-low' : 'border-outline-variant bg-surface-container-lowest hover:bg-surface-container' }}">
                                <input type="radio" name="jenis_kelamin" value="{{ $jk[0] }}"
                                       class="hidden peer"
                                       {{ $peserta->jenis_kelamin === $jk[0] ? 'checked' : '' }}>
                                <div class="w-5 h-5 rounded-full border-2 {{ $peserta->jenis_kelamin === $jk[0] ? 'border-primary' : 'border-outline' }} flex items-center justify-center flex-shrink-0">
                                    @if($peserta->jenis_kelamin === $jk[0])
                                        <div class="w-2.5 h-2.5 rounded-full bg-primary"></div>
                                    @endif
                                </div>
                                <span class="material-symbols-outlined text-[20px] text-outline">{{ $jk[2] }}</span>
                                <span class="text-body-md text-on-surface font-medium">{{ $jk[1] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- NIK --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-nik">
                            NIK
                            @if($peserta->nik)
                                <span class="ml-1 text-[10px] bg-surface-container-highest text-on-surface-variant px-2 py-0.5 rounded-full">🔒 Hanya Admin</span>
                            @else
                                <span class="ml-1 text-[10px] bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded-full">Sekali Isi</span>
                            @endif
                        </label>
                        <div class="flex rounded-lg overflow-hidden border {{ $peserta->nik ? 'border-outline-variant bg-surface-container-high' : 'border-outline-variant' }} focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">badge</span>
                            </div>
                            <input type="text" id="pribadi-nik"
                                   name="{{ $peserta->nik ? '' : 'nik' }}"
                                   class="flex-1 {{ $peserta->nik ? 'bg-surface-container-high text-on-surface-variant' : 'bg-surface-container-lowest text-on-surface' }} px-4 py-3 text-body-md border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $peserta->nik }}"
                                   placeholder="{{ $peserta->nik ? '' : '16 digit NIK' }}"
                                   maxlength="16"
                                   {{ $peserta->nik ? 'readonly' : '' }}>
                        </div>
                    </div>

                    {{-- NISN --}}
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-2" for="pribadi-nisn">
                            NISN
                            @if($peserta->nisn)
                                <span class="ml-1 text-[10px] bg-surface-container-highest text-on-surface-variant px-2 py-0.5 rounded-full">🔒 Hanya Admin</span>
                            @else
                                <span class="ml-1 text-[10px] bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded-full">Sekali Isi</span>
                            @endif
                        </label>
                        <div class="flex rounded-lg overflow-hidden border {{ $peserta->nisn ? 'border-outline-variant bg-surface-container-high' : 'border-outline-variant' }} focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <div class="w-12 bg-surface-container-high flex items-center justify-center border-r border-outline-variant flex-shrink-0">
                                <span class="material-symbols-outlined text-outline text-[20px]">school</span>
                            </div>
                            <input type="text" id="pribadi-nisn"
                                   name="{{ $peserta->nisn ? '' : 'nisn' }}"
                                   class="flex-1 {{ $peserta->nisn ? 'bg-surface-container-high text-on-surface-variant' : 'bg-surface-container-lowest text-on-surface' }} px-4 py-3 text-body-md border-none focus:ring-0 focus:outline-none min-w-0"
                                   value="{{ $peserta->nisn }}"
                                   placeholder="{{ $peserta->nisn ? '' : '10 digit NISN' }}"
                                   maxlength="10"
                                   {{ $peserta->nisn ? 'readonly' : '' }}>
                        </div>
                    </div>

                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                            class="btn-save bg-gradient-to-r from-primary to-primary-container text-on-primary text-label-md py-3 px-6 rounded-[10px] flex items-center gap-2 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        <span class="btn-text flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Simpan Data Pribadi
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
{{-- /pane-pribadi --}}
