@php
    $bulanNama = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
    ];
    $statusKegiatanConfig = \App\Models\ProgramKerja\KegiatanProgramKerja::statusConfig();
@endphp

@if($kegiatan->isEmpty())
    <div class="text-center py-4 text-muted">
        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
        Belum ada kegiatan. {{ $canAddKegiatan ? 'Klik "Tambah Kegiatan" untuk mulai.' : '' }}
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover table-bordered tbl-kegiatan mb-0">
            <thead>
                <tr>
                    <th width="4%" class="text-center">No</th>
                    <th>Nama Kegiatan</th>
                    <th width="12%">PJ</th>
                    <th width="12%">Target / Indikator</th>
                    <th width="10%">Anggaran</th>
                    <th width="10%">Bulan</th>
                    <th width="12%">Realisasi</th>
                    <th width="9%" class="text-center">Status</th>
                    @if($canEdit || $canRealisasi)
                        <th width="9%" class="text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($kegiatan as $i => $k)
                    @php
                        $sConfig = $statusKegiatanConfig[$k->status_kegiatan] ?? ['class' => 'secondary', 'label' => '-'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-medium">{{ $k->nama_kegiatan }}</div>
                            @if($k->deskripsi)
                                <small class="text-muted">{{ Str::limit($k->deskripsi, 60) }}</small>
                            @endif
                        </td>
                        <td>{{ $k->penanggung_jawab ?? '-' }}</td>
                        <td>
                            @if($k->target)
                                <div><small class="text-muted">Target:</small> {{ Str::limit($k->target, 40) }}</div>
                            @endif
                            @if($k->indikator)
                                <div><small class="text-muted">Indikator:</small> {{ Str::limit($k->indikator, 40) }}</div>
                            @endif
                            @if(!$k->target && !$k->indikator)
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($k->anggaran)
                                <div>Rp {{ number_format($k->anggaran, 0, ',', '.') }}</div>
                                @if($k->realisasi_anggaran)
                                    <small class="text-muted">Real: Rp {{ number_format($k->realisasi_anggaran, 0, ',', '.') }}</small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <small>
                                {{ $bulanNama[$k->bulan_mulai] ?? '-' }}
                                @if($k->bulan_selesai && $k->bulan_selesai !== $k->bulan_mulai)
                                    – {{ $bulanNama[$k->bulan_selesai] ?? '-' }}
                                @endif
                            </small>
                        </td>
                        <td>
                            @php $persen = $k->realisasi_persen ?? 0; @endphp
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-grow-1" style="height:6px;">
                                    <div class="progress-bar bg-success" style="width:{{ $persen }}%"></div>
                                </div>
                                <small class="text-nowrap">{{ $persen }}%</small>
                            </div>
                            @if($k->catatan_realisasi)
                                <small class="text-muted">{{ Str::limit($k->catatan_realisasi, 40) }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $sConfig['class'] }}">{{ $sConfig['label'] }}</span>
                        </td>
                        @if($canEdit || $canRealisasi)
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    @if($canEdit)
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="editKegiatan(
                                                    {{ $k->id }},
                                                    '{{ addslashes($k->nama_kegiatan) }}',
                                                    '{{ addslashes($k->penanggung_jawab ?? '') }}',
                                                    '{{ addslashes($k->target ?? '') }}',
                                                    '{{ addslashes($k->indikator ?? '') }}',
                                                    {{ $k->anggaran ?? 0 }},
                                                    {{ $k->bulan_mulai }},
                                                    {{ $k->bulan_selesai }},
                                                    '{{ addslashes($k->deskripsi ?? '') }}'
                                                )"
                                                title="Edit Kegiatan">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="hapusKegiatan({{ $k->id }})"
                                                title="Hapus Kegiatan">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                    @if($canRealisasi)
                                        <button class="btn btn-sm btn-outline-warning"
                                                onclick="updateRealisasi(
                                                    {{ $k->id }},
                                                    {{ $k->realisasi_persen ?? 0 }},
                                                    '{{ $k->status_kegiatan ?? 'belum' }}',
                                                    {{ $k->realisasi_anggaran ?? '' }},
                                                    '{{ addslashes($k->catatan_realisasi ?? '') }}'
                                                )"
                                                title="Update Realisasi">
                                            <i class="bi bi-graph-up"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
