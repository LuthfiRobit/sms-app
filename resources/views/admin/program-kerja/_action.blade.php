@php
    $id     = $row->id;
    $status = $row->status;
@endphp

<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('admin.program-kerja.show', $id) }}"
       class="btn btn-sm btn-outline-primary"
       title="Lihat Detail">
        <i class="bi bi-eye"></i>
    </a>

    @if(in_array($status, ['draft','ditolak']))
        <button class="btn btn-sm btn-success"
                onclick="submitProgram({{ $id }})"
                title="Ajukan ke Verifikasi">
            <i class="bi bi-send"></i>
        </button>
    @endif

    @if($status === 'diajukan')
        <button class="btn btn-sm btn-warning"
                onclick="tarikProgram({{ $id }})"
                title="Tarik Pengajuan">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button class="btn btn-sm btn-info text-white"
                onclick="verifikasiProgram({{ $id }})"
                title="Verifikasi">
            <i class="bi bi-check-circle"></i>
        </button>
    @endif

    @if($status === 'diverifikasi')
        <button class="btn btn-sm btn-success"
                onclick="approvalProgram({{ $id }})"
                title="Setujui">
            <i class="bi bi-patch-check"></i>
        </button>
    @endif

    @if(in_array($status, ['diajukan','diverifikasi']))
        <button class="btn btn-sm btn-danger"
                onclick="tolakProgram({{ $id }})"
                title="Tolak">
            <i class="bi bi-x-circle"></i>
        </button>
    @endif

    @if(in_array($status, ['disetujui','aktif','selesai']))
        <a href="{{ route('admin.program-kerja.cetak', $id) }}"
           class="btn btn-sm btn-outline-secondary"
           title="Cetak PDF"
           target="_blank">
            <i class="bi bi-printer"></i>
        </a>
    @endif

    @if(in_array($status, ['draft','ditolak']))
        <button class="btn btn-sm btn-outline-danger"
                onclick="hapusProgram({{ $id }})"
                title="Hapus">
            <i class="bi bi-trash"></i>
        </button>
    @endif
</div>
