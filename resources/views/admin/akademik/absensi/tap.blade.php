@extends('admin.layouts.app')
@section('title', 'TAP Kehadiran QR')

@section('content')
<div class="row g-3">

  {{-- Filter / Pilih Rombel --}}
  <div class="col-xl-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary"><i class="bi bi-qr-code-scan me-2"></i>TAP Kehadiran QR</h5>
            <small class="text-muted">Scan QR siswa untuk merekam kehadiran secara otomatis.</small>
          </div>
        </div>
      </div>
      <div class="card-body border-bottom bg-light">
        <form method="GET" action="{{ route('admin.akademik.absensi.tap') }}" class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label form-label-sm fw-semibold">Kelas / Rombel</label>
            <select name="rombel_id" class="form-select form-select-sm" required>
              <option value="">-- Pilih Rombel --</option>
              @foreach($rombelList as $r)
              <option value="{{ $r->id }}" @selected($rombelId == $r->id)>
                Kelas {{ $r->tingkat }} – {{ $r->nama }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label form-label-sm fw-semibold">Tanggal</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggal }}" required>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">
              <i class="bi bi-search me-1"></i>Tampilkan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @if($rombelId && $rombel)

  {{-- QR Scanner --}}
  <div class="col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-camera-video me-2 text-primary"></i>Scanner Kamera</h6>
      </div>
      <div class="card-body">

        {{-- Pilih Sesi Absensi --}}
        <div class="mb-3">
          <label class="form-label form-label-sm fw-semibold">Sesi Absensi</label>
          <select id="absensiSelect" class="form-select form-select-sm">
            <option value="">-- Pilih Sesi --</option>
          </select>
        </div>

        <div id="scannerArea" class="d-none">
          <div class="rounded-3 overflow-hidden position-relative mb-3" style="background:#000">
            <video id="scannerVideo" playsinline style="width:100%; max-height:300px; display:block;"></video>
            <canvas id="scannerCanvas" style="display:none;"></canvas>
            <div class="position-absolute top-50 start-50 translate-middle" style="width:200px;height:200px;border:3px solid #22c55e;border-radius:8px;pointer-events:none;"></div>
          </div>
          <div id="scanStatus" class="alert alert-info py-2 small">Arahkan kamera ke QR Code siswa…</div>
        </div>

        <button id="btnStartScan" class="btn btn-success btn-sm w-100 d-none">
          <i class="bi bi-camera me-1"></i>Mulai Scan Kamera
        </button>
        <button id="btnStopScan" class="btn btn-secondary btn-sm w-100 d-none">
          <i class="bi bi-stop-circle me-1"></i>Hentikan Scan
        </button>

        <div id="scanLog" class="mt-3" style="max-height:200px;overflow-y:auto;"></div>
      </div>
    </div>
  </div>

  {{-- Daftar Siswa & QR --}}
  <div class="col-xl-7">
    <div class="card shadow-sm">
      <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">
          <i class="bi bi-people me-2 text-primary"></i>
          Kelas {{ $rombel->tingkat }} – {{ $rombel->nama }}
          <small class="text-muted ms-1">({{ $siswaList->count() }} siswa)</small>
        </h6>
        <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM YYYY') }}</span>
      </div>
      <div class="card-body p-0" style="max-height:520px;overflow-y:auto;">
        @if($siswaList->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
            Belum ada siswa di kelas ini.
          </div>
        @else
          <div class="row g-0">
            @foreach($siswaList as $s)
            <div class="col-6 col-md-4 border-bottom border-end p-2 text-center" id="siswa-{{ $s->id }}">
              <div class="small fw-semibold text-truncate mb-1">{{ $s->nama_lengkap }}</div>
              <div class="text-muted" style="font-size:11px">No. {{ $s->no_absen ?? '-' }}</div>
              {{-- QR Code SVG inline via simple-qrcode --}}
              <div class="d-flex justify-content-center my-1">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate($s->qr_token) !!}
              </div>
              <span class="badge bg-secondary hadir-badge-{{ $s->id }}" style="font-size:10px">Belum</span>
            </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
@if($rombelId)
// Load absensi sessions for this rombel + tanggal
$.get('/admin/akademik/absensi/list', {
  rombel_id: {{ $rombelId }},
  tanggal: '{{ $tanggal }}'
}, function(res) {
  // DataTables returns data.data[]
}).fail(function() {});

// Manually load absensi for this rombel/tanggal
$.ajax({
  url: '{{ route('admin.akademik.absensi.list') }}',
  data: { rombel_id: {{ $rombelId }}, per_page: 100 },
  success(res) {
    const select = document.getElementById('absensiSelect');
    (res.data || []).forEach(row => {
      const opt = document.createElement('option');
      opt.value = row.id;
      opt.textContent = row.mata_pelajaran_nama + ' — ' + row.tanggal_fmt;
      select.appendChild(opt);
    });
  }
});

document.getElementById('absensiSelect').addEventListener('change', function() {
  const btnStart = document.getElementById('btnStartScan');
  btnStart.classList.toggle('d-none', !this.value);
});

let scanActive = false;
let scanInterval = null;
let stream = null;

document.getElementById('btnStartScan').addEventListener('click', async function() {
  const absensiId = document.getElementById('absensiSelect').value;
  if (!absensiId) return;

  try {
    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    const video = document.getElementById('scannerVideo');
    video.srcObject = stream;
    video.play();

    document.getElementById('scannerArea').classList.remove('d-none');
    document.getElementById('btnStartScan').classList.add('d-none');
    document.getElementById('btnStopScan').classList.remove('d-none');

    scanActive = true;
    scanLoop(absensiId);
  } catch(e) {
    document.getElementById('scanStatus').className = 'alert alert-danger py-2 small';
    document.getElementById('scanStatus').textContent = 'Kamera tidak dapat diakses: ' + e.message;
    document.getElementById('scannerArea').classList.remove('d-none');
  }
});

document.getElementById('btnStopScan').addEventListener('click', function() {
  scanActive = false;
  clearTimeout(scanInterval);
  if (stream) stream.getTracks().forEach(t => t.stop());
  document.getElementById('scannerArea').classList.add('d-none');
  document.getElementById('btnStartScan').classList.remove('d-none');
  document.getElementById('btnStopScan').classList.add('d-none');
});

const recentlyScanned = new Set();

function scanLoop(absensiId) {
  if (!scanActive) return;

  const video  = document.getElementById('scannerVideo');
  const canvas = document.getElementById('scannerCanvas');

  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (typeof jsQR === 'function') {
      const code = jsQR(imageData.data, imageData.width, imageData.height);
      if (code && !recentlyScanned.has(code.data)) {
        recentlyScanned.add(code.data);
        setTimeout(() => recentlyScanned.delete(code.data), 5000);
        recordAttendance(code.data, absensiId);
      }
    }
  }

  scanInterval = setTimeout(() => scanLoop(absensiId), 300);
}

function recordAttendance(token, absensiId) {
  $.post('{{ route('admin.akademik.absensi.tap.scan') }}', {
    _token: '{{ csrf_token() }}',
    token,
    absensi_id: absensiId,
  })
  .done(function(res) {
    const nama = res.data?.nama ?? 'Siswa';
    const log  = document.getElementById('scanLog');
    log.insertAdjacentHTML('afterbegin',
      `<div class="alert alert-success py-1 small mb-1"><i class="bi bi-check-circle me-1"></i><strong>${nama}</strong> hadir</div>`
    );
    document.getElementById('scanStatus').className = 'alert alert-success py-2 small';
    document.getElementById('scanStatus').textContent = '✓ ' + nama + ' berhasil dicatat hadir';
    setTimeout(() => {
      document.getElementById('scanStatus').className = 'alert alert-info py-2 small';
      document.getElementById('scanStatus').textContent = 'Arahkan kamera ke QR Code siswa…';
    }, 2000);
  })
  .fail(function(xhr) {
    const msg = xhr.responseJSON?.message ?? 'Gagal';
    document.getElementById('scanStatus').className = 'alert alert-warning py-2 small';
    document.getElementById('scanStatus').textContent = '⚠ ' + msg;
  });
}
@endif
</script>
{{-- jsQR library for QR decoding in browser --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
@endpush
