@extends('admin.layouts.app')
@section('title', $rpp ? 'Edit RPP' : 'Tambah RPP')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-primary"><i class="bi bi-journal-text me-2"></i>{{ $rpp ? 'Edit RPP' : 'Tambah RPP' }}</h5>
                    <small class="text-muted">Isi value dari tiap poin — sistem yang merangkai jadi dokumen RPP.</small>
                </div>
                <div>
                    @if(!$rpp && app()->environment(['local', 'testing']))
                        <button type="button" class="btn btn-sm btn-outline-warning me-2" id="btn-dummy"><i class="bi bi-magic me-1"></i>Isi Data Dummy</button>
                    @endif
                    <a href="{{ route('admin.akademik.rpp.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div id="form-alert" class="alert d-none"></div>

                <form id="form-rpp" action="{{ $rpp ? route('admin.akademik.rpp.update', $rpp->id) : route('admin.akademik.rpp.store') }}" method="POST">
                    @csrf
                    @if($rpp) @method('PUT') @endif

                    {{-- Identitas --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Lembaga <span class="text-danger">*</span></label>
                            <select class="form-select" name="lembaga_id" id="f-lembaga_id" required>
                                <option value="">-- Pilih Lembaga --</option>
                                @foreach($lembagaList as $l)
                                    <option value="{{ $l->id }}" {{ ($rpp->lembaga_id ?? app('active_lembaga_id')) == $l->id ? 'selected' : '' }}>[{{ $l->kode }}] {{ $l->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Guru <span class="text-danger">*</span></label>
                            <select class="form-select" name="guru_id" id="f-guru_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guruList as $g)
                                    <option value="{{ $g->id }}" data-lembaga="{{ $g->lembaga_id }}" {{ ($rpp->guru_id ?? null) == $g->id ? 'selected' : '' }}>{{ $g->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mata_pelajaran_id" id="f-mapel_id" required>
                                <option value="">-- Pilih Guru Terlebih Dahulu --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="tahun_pelajaran_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($tahunList as $t)
                                    <option value="{{ $t->id }}" {{ ($rpp->tahun_pelajaran_id ?? null) == $t->id ? 'selected' : '' }}>{{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" name="semester_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach($semesterList as $s)
                                    <option value="{{ $s->id }}" {{ ($rpp->semester_id ?? null) == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fase / Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="fase_kelas" maxlength="100" placeholder="C / V" value="{{ $rpp->fase_kelas ?? '' }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Alokasi Waktu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="alokasi_waktu" maxlength="100" placeholder="4 x 35 Menit" value="{{ $rpp->alokasi_waktu ?? '' }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Materi / Judul RPP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="materi" maxlength="255" placeholder="mis. Kisah Teladan Umar bin Khattab R.A." value="{{ $rpp->materi ?? '' }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Submateri</label>
                            <table class="table table-sm mb-1" id="submateri-table">
                                <tbody>
                                    @forelse(($rpp->submateri ?? []) as $i => $sub)
                                    <tr class="submateri-row">
                                        <td class="text-center text-muted submateri-nomor" width="30">{{ $i + 1 }}</td>
                                        <td><input type="text" class="form-control form-control-sm" name="submateri[]" maxlength="255" value="{{ $sub->teks }}"></td>
                                        <td width="40"><button type="button" class="btn btn-sm btn-danger btn-remove-submateri"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                    @empty
                                    <tr class="submateri-row">
                                        <td class="text-center text-muted submateri-nomor" width="30">1</td>
                                        <td><input type="text" class="form-control form-control-sm" name="submateri[]" maxlength="255"></td>
                                        <td width="40"><button type="button" class="btn btn-sm btn-danger btn-remove-submateri"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-info" id="btn-tambah-submateri"><i class="bi bi-plus"></i> Tambah Submateri</button>
                            <div class="form-text">Setiap baris = satu submateri, panjang bebas, jumlah tidak dibatasi. Inilah yang ditampilkan sebagai daftar materi per-sesi di aplikasi mobile guru — kosongkan semua kalau RPP ini cuma satu sesi (mobile akan pakai Materi/Judul RPP di atas sebagai gantinya).</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Model Pembelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="model_pembelajaran_id" id="model_pembelajaran_id" required>
                                @foreach($modelList as $m)
                                    <option value="{{ $m->id }}" {{ ($rpp->model_pembelajaran_id ?? null) == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Menentukan tahapan yang tampil di poin "Inti".</div>
                        </div>
                    </div>

                    {{-- Poin per bagian --}}
                    <div class="accordion" id="accordion-bagian">
                        @foreach($bagianList as $bIndex => $bagian)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $bIndex > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#bagian-{{ $bagian->id }}">
                                    {{ $bagian->nama }}
                                </button>
                            </h2>
                            <div id="bagian-{{ $bagian->id }}" class="accordion-collapse collapse {{ $bIndex === 0 ? 'show' : '' }}" data-bs-parent="#accordion-bagian">
                                <div class="accordion-body">
                                    @forelse($bagian->poin as $poin)
                                        @include('admin.akademik.rpp._poin-input', ['poin' => $poin, 'rpp' => $rpp])
                                    @empty
                                        <div class="text-muted small">Belum ada poin di bagian ini.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('admin.akademik.rpp.index') }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary" id="btn-submit"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
function showAlert(sel, type, msg) {
    $(sel).removeClass('d-none alert-success alert-danger').addClass('alert-' + type).html(msg);
}

// ── WYSIWYG (CKEditor) untuk semua poin bertipe teks_panjang ────────────────
// Jumlahnya dinamis (tergantung templat RPP), jadi diinisialisasi lewat loop,
// beda dari pola materi-belajar yang cuma punya 1 instance tetap.
var rppEditors = {};
$('.rpp-wysiwyg').each(function () {
    var $el = $(this);
    ClassicEditor
        // insertTable — dipakai untuk poin seperti Formatif Proses/Sumatif yang
        // kadang butuh tabel (mis. rubrik penilaian), bukan cuma paragraf/daftar.
        .create(this, { toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'insertTable'] })
        .then(function (editor) { rppEditors[$el.attr('id')] = editor; })
        .catch(function (error) { console.error(error); });
});

function syncWysiwygFields() {
    $('.rpp-wysiwyg').each(function () {
        var editor = rppEditors[$(this).attr('id')];
        if (editor) {
            $(this).val(editor.getData());
        }
    });
}

// ── Dropdown guru (difilter lembaga) & mapel (dependent ke guru) ──────────
function filterGuruByLembaga(lembagaId) {
    var $guru = $('#f-guru_id');
    var selectedBefore = $guru.val();
    $guru.find('option[data-lembaga]').each(function () {
        var opt = $(this);
        if (!lembagaId || opt.data('lembaga') == lembagaId) {
            opt.show();
        } else {
            opt.hide();
            if (opt.is(':selected')) opt.prop('selected', false);
        }
    });
    if ($guru.val() !== selectedBefore) {
        $guru.trigger('change');
    }
}

function loadMapelByGuru(guruId, selectedMapelId) {
    var $mapel = $('#f-mapel_id');
    $mapel.empty().append('<option value="">-- Memuat... --</option>').prop('disabled', true);

    if (!guruId) {
        $mapel.empty().append('<option value="">-- Pilih Guru Terlebih Dahulu --</option>').prop('disabled', true);
        return;
    }

    var url = '{{ route('admin.akademik.rpp.guru-mapel', ':guruId') }}'.replace(':guruId', guruId);

    $.get(url, function (res) {
        $mapel.empty().append('<option value="">-- Pilih Mata Pelajaran --</option>').prop('disabled', false);
        if (res.status === 200 && res.data.length) {
            $.each(res.data, function (i, item) {
                var selected = (selectedMapelId && item.id == selectedMapelId) ? 'selected' : '';
                $mapel.append('<option value="' + item.id + '" ' + selected + '>' + item.nama + '</option>');
            });
        } else {
            $mapel.empty().append('<option value="">-- Guru tidak memiliki jadwal mengajar --</option>').prop('disabled', true);
        }
    }).fail(function () {
        $mapel.empty().append('<option value="">-- Gagal memuat data --</option>').prop('disabled', true);
    });
}

$('#f-lembaga_id').on('change', function () {
    filterGuruByLembaga($(this).val());
}).trigger('change');

$('#f-guru_id').on('change', function () {
    loadMapelByGuru($(this).val(), {{ $rpp->mata_pelajaran_id ?? 'null' }});
}).trigger('change');

// ── Baris berulang untuk poin bertipe pasangan_kolom ──────────────────────
$(document).on('click', '.btn-add-row', function () {
    var poinId = $(this).data('poin-id');
    var $table = $('.pasangan-kolom-table[data-poin-id="' + poinId + '"]');
    var idx = $table.find('tbody tr').length;
    var row = $('<tr class="pasangan-row">' +
        '<td><input type="text" class="form-control form-control-sm" name="poin[' + poinId + '][' + idx + '][kolom1]"></td>' +
        '<td><input type="text" class="form-control form-control-sm" name="poin[' + poinId + '][' + idx + '][kolom2]"></td>' +
        '<td><button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>' +
        '</tr>');
    $table.find('tbody').append(row);
});

$(document).on('click', '.btn-remove-row', function () {
    var $tbody = $(this).closest('tbody');
    if ($tbody.find('tr').length > 1) {
        $(this).closest('tr').remove();
    } else {
        $(this).closest('tr').find('input').val('');
    }
});

// ── Baris berulang untuk Submateri ──────────────────────────────────────────
function renumberSubmateri() {
    $('#submateri-table .submateri-row').each(function (i) {
        $(this).find('.submateri-nomor').text(i + 1);
    });
}

$('#btn-tambah-submateri').on('click', function () {
    var row = $('<tr class="submateri-row">' +
        '<td class="text-center text-muted submateri-nomor" width="30"></td>' +
        '<td><input type="text" class="form-control form-control-sm" name="submateri[]" maxlength="255"></td>' +
        '<td width="40"><button type="button" class="btn btn-sm btn-danger btn-remove-submateri"><i class="bi bi-trash"></i></button></td>' +
        '</tr>');
    $('#submateri-table tbody').append(row);
    renumberSubmateri();
    row.find('input').trigger('focus');
});

$(document).on('click', '.btn-remove-submateri', function () {
    var $tbody = $('#submateri-table tbody');
    if ($tbody.find('tr').length > 1) {
        $(this).closest('tr').remove();
        renumberSubmateri();
    } else {
        $(this).closest('tr').find('input').val('');
    }
});

// ── Isi Data Dummy (khusus testing, tidak tampil di production) ────────────
function randInt(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }
function pickOne(arr) { return arr[randInt(0, arr.length - 1)]; }
function pickMany(arr, n) {
    var sisa = arr.slice();
    var hasil = [];
    n = Math.min(n, sisa.length);
    for (var i = 0; i < n; i++) {
        hasil.push(sisa.splice(randInt(0, sisa.length - 1), 1)[0]);
    }
    return hasil;
}
function waitFor(cekFn, timeoutMs) {
    return new Promise(function (resolve) {
        var mulai = Date.now();
        (function poll() {
            if (cekFn() || Date.now() - mulai > timeoutMs) return resolve();
            setTimeout(poll, 150);
        })();
    });
}

var DUMMY_MATERI = ['Ide Pokok Paragraf', 'Kisah Teladan Umar bin Khattab R.A.', 'Perkalian dan Pembagian Pecahan', 'Siklus Air dan Manfaatnya', 'Struktur Teks Deskripsi', 'Sistem Pernapasan Manusia', 'Keragaman Budaya Indonesia', 'Bangun Ruang Sisi Datar', 'Perubahan Wujud Benda', 'Norma dan Aturan di Masyarakat'];
var DUMMY_FASE = ['A / I', 'A / II', 'B / III', 'B / IV', 'C / V', 'C / VI'];
var DUMMY_ALOKASI = ['2 x 35 Menit', '3 x 35 Menit', '4 x 35 Menit', '2 x 40 Menit'];
var DUMMY_KALIMAT = [
    'Murid mampu memahami konsep dasar materi dengan baik.',
    'Peserta didik menunjukkan antusiasme tinggi dalam pembelajaran.',
    'Guru memberikan contoh konkret yang relevan dengan kehidupan sehari-hari.',
    'Pembelajaran dilakukan secara berkelompok untuk melatih kolaborasi.',
    'Murid diminta menyampaikan hasil diskusi di depan kelas.',
    'Evaluasi dilakukan melalui tanya-jawab dan lembar kerja.',
    'Materi dikaitkan dengan nilai-nilai karakter Panca Cinta.',
    'Guru menggunakan media visual untuk mempermudah pemahaman.',
    'Murid mengerjakan tugas secara mandiri sebagai bentuk penguatan.',
    'Refleksi dilakukan di akhir sesi untuk mengukur ketercapaian tujuan.',
];
var DUMMY_SUBMATERI = ['Pengertian dan Ciri-Ciri', 'Contoh dalam Kehidupan Sehari-hari', 'Latihan Soal dan Pembahasan', 'Penerapan dalam Proyek Sederhana', 'Diskusi Kelompok dan Presentasi', 'Rangkuman dan Kesimpulan'];

function dummyDaftar() { return pickMany(DUMMY_KALIMAT, randInt(2, 4)).join('\n'); }
function dummyParagraf() { return '<p>' + pickMany(DUMMY_KALIMAT, randInt(2, 3)).join(' ') + '</p>'; }
function dummySingkat() { return pickOne(DUMMY_KALIMAT).split(' ').slice(0, 4).join(' '); }

async function isiDataDummy() {
    var $btn = $('#btn-dummy').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Mengisi...');

    // Guru & Lembaga dipilih sebagai SATU pasangan yang konsisten: opsi Guru
    // (option[data-lembaga]) selalu berisi guru dari lembaga aktif sesi apapun
    // pilihan dropdown Lembaga saat ini, jadi Lembaga diikutkan dari atribut
    // data-lembaga milik guru yang dipilih — bukan diacak sendiri — supaya
    // field Lembaga yang wajib diisi tidak tertinggal kosong (kalau kosong,
    // validasi HTML5 diam-diam menolak submit tanpa pesan apapun).
    var mapelOpts = [];
    for (var percobaan = 0; percobaan < 8 && mapelOpts.length === 0; percobaan++) {
        var guruAll = $('#f-guru_id option[data-lembaga]').get();
        if (!guruAll.length) break;
        var guruEl = pickOne(guruAll);
        var lembagaId = $(guruEl).data('lembaga');
        $('#f-lembaga_id').val(lembagaId).trigger('change');
        $('#f-guru_id').val(guruEl.value).trigger('change');
        await waitFor(function () { return $('#f-mapel_id option:first').text().indexOf('Memuat') === -1; }, 3000);
        mapelOpts = $('#f-mapel_id option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
    }
    if (mapelOpts.length) $('#f-mapel_id').val(pickOne(mapelOpts));

    var tahunOpts = $('select[name="tahun_pelajaran_id"] option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
    if (tahunOpts.length) $('select[name="tahun_pelajaran_id"]').val(pickOne(tahunOpts));
    var semesterOpts = $('select[name="semester_id"] option').filter(function () { return $(this).val(); }).map(function () { return $(this).val(); }).get();
    if (semesterOpts.length) $('select[name="semester_id"]').val(pickOne(semesterOpts));

    $('input[name="fase_kelas"]').val(pickOne(DUMMY_FASE));
    $('input[name="alokasi_waktu"]').val(pickOne(DUMMY_ALOKASI));
    $('input[name="materi"]').val(pickOne(DUMMY_MATERI) + ' (Dummy ' + randInt(100, 999) + ')');

    // Submateri: reset ke 1 baris lalu isi ulang beberapa baris acak
    $('#submateri-table tbody tr').slice(1).remove();
    $('#submateri-table tbody tr:first input').val('');
    var subItems = pickMany(DUMMY_SUBMATERI, randInt(2, 4));
    subItems.forEach(function (_, i) { if (i > 0) $('#btn-tambah-submateri').trigger('click'); });
    $('#submateri-table tbody tr').each(function (i) { $(this).find('input').val(subItems[i] || ''); });

    // Model Pembelajaran → langsung memicu render ulang blok Inti
    var modelOpts = $('#model_pembelajaran_id option').map(function () { return $(this).val(); }).get();
    if (modelOpts.length) $('#model_pembelajaran_id').val(pickOne(modelOpts)).trigger('change');

    // Poin tipe teks (satu baris) — nama persis "poin[<id>]" tanpa index tambahan
    $('input[type="text"][name^="poin["]').filter(function () {
        return /^poin\[\d+\]$/.test($(this).attr('name'));
    }).each(function () { $(this).val(dummySingkat()); });

    // Poin tipe daftar_poin
    $('textarea[name^="poin["]:not(.rpp-wysiwyg)').filter(function () {
        return /^poin\[\d+\]$/.test($(this).attr('name'));
    }).each(function () { $(this).val(dummyDaftar()); });

    // Poin tipe pasangan_kolom
    $('.pasangan-kolom-table').each(function () {
        var poinId = $(this).data('poin-id');
        var $tbody = $(this).find('tbody');
        var jumlahBaris = randInt(1, 2);
        while ($tbody.find('tr').length < jumlahBaris) {
            $('.btn-add-row[data-poin-id="' + poinId + '"]').trigger('click');
        }
        $tbody.find('tr').each(function () {
            $(this).find('input').eq(0).val(dummySingkat());
            $(this).find('input').eq(1).val(dummySingkat());
        });
    });

    // Poin tipe pilih_master — kelompokkan checkbox per nama field, pilih acak
    var kelompokCheckbox = {};
    $('input[type="checkbox"][name^="poin["]').each(function () {
        var nama = $(this).attr('name');
        (kelompokCheckbox[nama] = kelompokCheckbox[nama] || []).push(this);
    });
    Object.keys(kelompokCheckbox).forEach(function (nama) {
        var kotak = kelompokCheckbox[nama];
        kotak.forEach(function (k) { k.checked = false; });
        pickMany(kotak, randInt(1, Math.min(3, kotak.length))).forEach(function (k) { k.checked = true; });
    });

    // Poin tipe teks_panjang (WYSIWYG) — tunggu semua editor siap dulu
    await waitFor(function () {
        return $('.rpp-wysiwyg').toArray().every(function (el) { return rppEditors[el.id]; });
    }, 4000);
    $('.rpp-wysiwyg').each(function () {
        var editor = rppEditors[this.id];
        if (editor) { editor.setData(dummyParagraf()); } else { $(this).val(dummyParagraf()); }
    });

    // Blok Inti (muncul sinkron setelah trigger('change') Model Pembelajaran di atas)
    $('#inti-container textarea').each(function () { $(this).val(dummyDaftar()); });

    $btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i>Isi Data Dummy');
}

$('#btn-dummy').on('click', isiDataDummy);

// ── Submit ─────────────────────────────────────────────────────────────────
$('#form-rpp').on('submit', function (e) {
    e.preventDefault();
    syncWysiwygFields();
    $('#form-alert').addClass('d-none');
    var $btn = $('#btn-submit').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Menyimpan...');

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function (res) {
            if (res.status === 200) {
                toastr.success(res.message);
                window.location.href = '{{ route('admin.akademik.rpp.index') }}';
            } else {
                showAlert('#form-alert', 'danger', res.message);
            }
        },
        error: function (xhr) {
            var msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan.';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            showAlert('#form-alert', 'danger', msg);
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Simpan');
        },
    });
});
</script>
@endpush
