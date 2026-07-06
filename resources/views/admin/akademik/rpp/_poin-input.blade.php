@php
    $nilai = $rpp?->nilaiUntuk($poin);
@endphp

@if($poin->tipe === 'model_pembelajaran')
    @include('admin.akademik.rpp._poin-inti', ['poin' => $poin])
@else
<div class="mb-3">
    <label class="form-label fw-bold">{{ $poin->label }} @if($poin->is_required)<span class="text-danger">*</span>@endif</label>

    @switch($poin->tipe)
        @case('teks')
            <input type="text" class="form-control" name="poin[{{ $poin->id }}]" value="{{ $nilai->value_teks ?? '' }}">
            <div class="form-text">Isian singkat satu baris.</div>
            @break

        @case('teks_panjang')
            {{-- WYSIWYG (CKEditor) — poin ini menampung prosa bebas format,
                 beda dari daftar_poin/pasangan_kolom yang isinya di-parse
                 terstruktur (jangan disamakan, HTML rich-text akan merusak
                 parsing "satu baris = satu item" di poin lain). --}}
            <textarea class="form-control rpp-wysiwyg" id="poin-teks-{{ $poin->id }}" name="poin[{{ $poin->id }}]" rows="3">{{ $nilai->value_teks ?? '' }}</textarea>
            <div class="form-text">Bisa diformat (tebal, miring, daftar, dll) lewat toolbar di atas kolom.</div>
            @break

        @case('daftar_poin')
            <textarea class="form-control" name="poin[{{ $poin->id }}]" rows="4" placeholder="Satu poin per baris">{{ $nilai?->value_json ? implode("\n", $nilai->value_json) : '' }}</textarea>
            <div class="form-text">Satu poin per baris — tekan Enter untuk poin baru. Cocok untuk butir-butir singkat, bukan kalimat panjang (kalau butir Anda panjang dan berisiko kepotong salah Enter, sebaiknya tetap satu baris utuh tanpa Enter di tengah).</div>
            @break

        @case('pasangan_kolom')
            @php $rows = $nilai->value_json ?? []; @endphp
            <table class="table table-sm table-bordered pasangan-kolom-table mb-1" data-poin-id="{{ $poin->id }}">
                <thead class="table-light">
                    <tr>
                        <th>{{ $poin->kolom1_label }}</th>
                        <th>{{ $poin->kolom2_label }}</th>
                        <th width="40"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                    <tr class="pasangan-row">
                        <td><input type="text" class="form-control form-control-sm" name="poin[{{ $poin->id }}][{{ $i }}][kolom1]" value="{{ $row['kolom1'] ?? '' }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="poin[{{ $poin->id }}][{{ $i }}][kolom2]" value="{{ $row['kolom2'] ?? '' }}"></td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    @empty
                    <tr class="pasangan-row">
                        <td><input type="text" class="form-control form-control-sm" name="poin[{{ $poin->id }}][0][kolom1]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="poin[{{ $poin->id }}][0][kolom2]"></td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-info btn-add-row" data-poin-id="{{ $poin->id }}"><i class="bi bi-plus"></i> Tambah Baris</button>
            <div class="form-text">Isi kedua kolom lalu klik "Tambah Baris" untuk pasangan berikutnya. Baris yang dua kolomnya kosong otomatis diabaikan saat disimpan.</div>
            @break

        @case('pilih_master')
            @php $opsiList = $poin->opsiMaster(); @endphp
            {{-- Marker tersembunyi: kalau semua checkbox di-uncheck, browser tidak
                 mengirim field ini sama sekali — tanpa ini, guru yang sengaja
                 mengosongkan pilihan tidak akan tersimpan (nilai lama tetap nyantol). --}}
            <input type="hidden" name="poin[{{ $poin->id }}][]" value="">
            @forelse($opsiList as $opsi)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="poin[{{ $poin->id }}][]" value="{{ $opsi->id }}" id="opsi-{{ $opsi->id }}"
                        {{ in_array($opsi->id, $nilai->value_json ?? []) ? 'checked' : '' }}>
                    <label class="form-check-label" for="opsi-{{ $opsi->id }}">{{ $opsi->nama }}</label>
                </div>
            @empty
                <div class="text-muted small">Belum ada opsi untuk kategori "{{ $poin->master_kategori }}". Tambahkan dari halaman Kelola Bagian &amp; Poin RPP.</div>
            @endforelse
            @if($opsiList->isNotEmpty())
                <div class="form-text">Boleh centang lebih dari satu.</div>
            @endif
            @break
    @endswitch
</div>
@endif
