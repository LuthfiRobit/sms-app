@extends('admin.layouts.app')
@section('title', 'Pratinjau Raport')

@section('content')
<div class="row g-3">

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-start align-items-md-center flex-column flex-md-row gap-3">

                    {{-- Info block --}}
                    <div class="flex-grow-1">
                        <h5 class="mb-1 text-primary fw-semibold">
                            <i class="bi bi-journal-richtext me-2"></i>Pratinjau Raport
                        </h5>
                        <div class="d-flex flex-wrap gap-3 text-muted small">
                            <span><i class="bi bi-people me-1"></i>
                                <strong>Rombel:</strong> {{ $pengajuan->rombel->nama }}
                                (Kelas {{ $pengajuan->rombel->tingkat }})
                            </span>
                            <span><i class="bi bi-calendar3 me-1"></i>
                                <strong>Semester:</strong> {{ $pengajuan->semester->nama }}
                            </span>
                            <span><i class="bi bi-mortarboard me-1"></i>
                                <strong>Tahun Pelajaran:</strong> {{ $pengajuan->tahunPelajaran->nama }}
                            </span>
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i>
                                <strong>Disetujui oleh:</strong>
                                {{ $pengajuan->disetujuiOleh?->name ?? '-' }}
                                @if($pengajuan->disetujui_at)
                                    · {{ $pengajuan->disetujui_at->translatedFormat('d F Y') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="{{ route('admin.akademik.raport.cetak-semua', $pengajuan->id) }}"
                           class="btn btn-success btn-sm"
                           target="_blank">
                            <i class="bi bi-printer-fill me-1"></i>Cetak Semua PDF
                        </a>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ── Summary strip --}}
    <div class="col-12">
        <div class="alert alert-success border-0 py-2 px-3 mb-0 d-flex align-items-center gap-2 small">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>
                Menampilkan raport untuk <strong>{{ count($siswaData) }} siswa</strong> pada rombel
                <strong>{{ $pengajuan->rombel->nama }}</strong>.
                Bobot nilai: Harian {{ $setting->bobot_harian }}% · UTS {{ $setting->bobot_uts }}% · UAS {{ $setting->bobot_uas }}%.
                KKM default: <strong>{{ $setting->kkm_default }}</strong>.
            </div>
        </div>
    </div>

    {{-- ── Per-siswa cards ──────────────────────────────────────────────────── --}}
    @forelse($siswaData as $index => $sd)
        @php
            $peserta   = $sd['peserta'];
            $nilaiRows = $sd['nilaiRows'];
            $absensi   = $sd['absensiRekap'];
        @endphp

        <div class="col-12">
            <div class="card shadow-sm border-0">

                {{-- Card header: identitas siswa --}}
                <div class="card-header bg-light py-2 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                        <strong>{{ $peserta?->nama_lengkap ?? '-' }}</strong>
                        <span class="text-muted small ms-2">
                            NISN: {{ $peserta?->nisn ?? '-' }}
                        </span>
                        <span class="text-muted small ms-2">
                            | Kelas {{ $pengajuan->rombel->tingkat }} – {{ $pengajuan->rombel->nama }}
                        </span>
                    </div>
                    @if($peserta)
                    <a href="{{ route('admin.akademik.raport.cetak-satu', [$pengajuan->id, $peserta->id]) }}"
                       class="btn btn-outline-primary btn-sm"
                       target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Cetak PDF Siswa Ini
                    </a>
                    @endif
                </div>

                <div class="card-body p-0">
                    <div class="row g-0">

                        {{-- ── Nilai table ─────────────────────────────── --}}
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0 align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th width="4%" class="text-start ps-3">No</th>
                                            <th class="text-start">Mata Pelajaran</th>
                                            <th width="6%">KKM</th>
                                            <th width="8%">H</th>
                                            <th width="8%">UTS</th>
                                            <th width="8%">UAS</th>
                                            <th width="8%" class="bg-light">NA</th>
                                            <th width="7%">Predikat</th>
                                            <th width="8%">Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($nilaiRows as $no => $nilai)
                                            @php
                                                $kkm   = $nilai->mataPelajaran?->kkm ?? $setting->kkm_default;
                                                $na    = $nilai->nilai_akhir;
                                                $lulus = $na !== null && $na >= $kkm;
                                            @endphp
                                            <tr class="{{ ($na !== null && !$lulus) ? 'table-danger' : '' }}">
                                                <td class="text-center">{{ $no + 1 }}</td>
                                                <td class="ps-3">
                                                    {{ $nilai->mataPelajaran?->nama ?? '-' }}
                                                    @if($nilai->catatan_guru)
                                                        <br><small class="text-muted fst-italic">{{ $nilai->catatan_guru }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $kkm }}</td>
                                                <td class="text-center">
                                                    {{ $nilai->nilai_harian !== null ? number_format($nilai->nilai_harian, 2) : '–' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $nilai->nilai_uts !== null ? number_format($nilai->nilai_uts, 2) : '–' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $nilai->nilai_uas !== null ? number_format($nilai->nilai_uas, 2) : '–' }}
                                                </td>
                                                <td class="text-center fw-semibold {{ $na !== null ? ($lulus ? 'text-success' : 'text-danger') : '' }}">
                                                    {{ $na !== null ? number_format($na, 2) : '–' }}
                                                </td>
                                                <td class="text-center">
                                                    @if($nilai->predikat)
                                                        <span class="badge
                                                            {{ $nilai->predikat === 'A' ? 'bg-success' :
                                                               ($nilai->predikat === 'B' ? 'bg-primary' :
                                                               ($nilai->predikat === 'C' ? 'bg-warning text-dark' :
                                                               ($nilai->predikat === 'D' ? 'bg-orange text-dark' : 'bg-danger'))) }}">
                                                            {{ $nilai->predikat }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">–</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($na !== null)
                                                        @if($lulus)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Tuntas</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">–</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-3">
                                                    Tidak ada data nilai.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ── Absensi rekap ────────────────────────────── --}}
                        <div class="col-12 border-top">
                            <div class="px-3 py-2 d-flex align-items-center gap-4 small">
                                <span class="fw-semibold text-muted">
                                    <i class="bi bi-calendar-check me-1"></i>Kehadiran:
                                </span>
                                <span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle me-1">Hadir</span>
                                    {{ $absensi?->hadir ?? 0 }}x
                                </span>
                                <span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1">Sakit</span>
                                    {{ $absensi?->sakit ?? 0 }}x
                                </span>
                                <span>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle me-1">Izin</span>
                                    {{ $absensi?->izin ?? 0 }}x
                                </span>
                                <span>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">Alpa</span>
                                    {{ $absensi?->alpa ?? 0 }}x
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Belum ada data nilai siswa pada pengajuan raport ini.
            </div>
        </div>
    @endforelse

    {{-- Bottom action bar --}}
    @if(count($siswaData) > 0)
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2 pb-3">
            <a href="{{ route('admin.akademik.raport.cetak-semua', $pengajuan->id) }}"
               class="btn btn-success"
               target="_blank">
                <i class="bi bi-printer-fill me-1"></i>Cetak Semua PDF ({{ count($siswaData) }} Siswa)
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
