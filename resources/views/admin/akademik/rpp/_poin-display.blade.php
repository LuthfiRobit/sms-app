@php
    $nilai = $rpp->nilaiUntuk($poin);
@endphp

<div class="mb-3">
    <div class="fw-bold">{{ $poin->label }}</div>

    @if($poin->tipe === 'model_pembelajaran')
        @php $intiByFase = $rpp->inti->groupBy(fn ($i) => $i->sintaks?->meta_fase); @endphp
        @forelse(['Memahami', 'Mengaplikasi', 'Merefleksi'] as $fase)
            @if(($intiByFase[$fase] ?? collect())->isNotEmpty())
                <div class="text-uppercase text-muted small mt-2">{{ $fase }}</div>
                @foreach($intiByFase[$fase]->sortBy('urutan') as $item)
                    <div class="ms-2 mt-1">
                        <div class="fst-italic small">{{ $item->sintaks?->nama_sintaks }}</div>
                        @if(!empty($item->konten))
                            <ul class="mb-0">
                                @foreach($item->konten as $baris)
                                    <li>{{ $baris }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </div>
                @endforeach
            @endif
        @empty
        @endforelse
    @elseif($poin->tipe === 'pasangan_kolom')
        @php $rows = $nilai->value_json ?? []; @endphp
        @if(count($rows))
            <table class="table table-sm table-bordered mt-1">
                <thead class="table-light"><tr><th>{{ $poin->kolom1_label }}</th><th>{{ $poin->kolom2_label }}</th></tr></thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr><td>{{ $row['kolom1'] ?? '' }}</td><td>{{ $row['kolom2'] ?? '' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-muted small">—</div>
        @endif
    @elseif($poin->tipe === 'pilih_master')
        @php
            $selected = $nilai->value_json ?? [];
            $opsiTerpilih = $poin->opsiMaster()->whereIn('id', $selected);
        @endphp
        @if($opsiTerpilih->isNotEmpty())
            @foreach($opsiTerpilih as $opsi)
                <span class="badge bg-light-primary text-primary me-1">{{ $opsi->nama }}</span>
            @endforeach
        @else
            <div class="text-muted small">—</div>
        @endif
    @elseif($poin->tipe === 'daftar_poin')
        @if(!empty($nilai->value_json))
            <ul class="mb-0">
                @foreach($nilai->value_json as $baris)
                    <li>{{ $baris }}</li>
                @endforeach
            </ul>
        @else
            <div class="text-muted small">—</div>
        @endif
    @else
        {{-- teks_panjang disimpan sebagai HTML dari WYSIWYG (CKEditor) — render
             apa adanya, jangan di-escape (beda dari poin lain yang isinya
             plain text biasa). --}}
        @if(! empty($nilai->value_teks))
            <div class="rpp-rich-content">{!! $nilai->value_teks !!}</div>
        @else
            <div class="text-muted small">—</div>
        @endif
    @endif
</div>
