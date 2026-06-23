<div class="dash-card h-100">
    <div class="dash-card-head">
        <div>
            <div class="dash-card-title">Perlu Tindakan</div>
            <div class="dash-card-sub">Pendaftaran masuk & dalam verifikasi</div>
        </div>
        <a href="{{ route('admin.pendaftaran.index') }}" class="btn btn-sm btn-outline-secondary">
            Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="dash-card-body p-0">
        @if($recentPendaftaran->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Tidak ada pendaftaran yang perlu diproses
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 dash-table">
                    <thead>
                        <tr>
                            <th>Nama Peserta</th>
                            @if(!$lid)<th>Lembaga</th>@endif
                            <th>Jalur</th>
                            <th>Status</th>
                            <th>Masuk</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPendaftaran as $p)
                        <tr>
                            <td class="fw-600">{{ $p->peserta?->nama_lengkap ?? '—' }}</td>
                            @if(!$lid)
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $p->lembaga?->kode ?? '—' }}</span>
                                </td>
                            @endif
                            <td class="text-muted small">{{ $p->jalurPendaftaran?->nama ?? '—' }}</td>
                            <td>
                                @if($p->status === 'submit')
                                    <span class="badge bg-primary">Dikirim</span>
                                @elseif($p->status === 'verifikasi')
                                    <span class="badge bg-warning text-dark">Verifikasi</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $p->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.pendaftaran.show', $p->id) }}"
                                   class="btn btn-xs btn-outline-primary">Proses</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
