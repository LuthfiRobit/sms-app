<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

/** Placeholder — akan diimplementasi pada Milestone 6 (Alur Pendaftaran). */
class PendaftaranPesertaController extends Controller
{
    public function index()       { return view('portal.coming_soon', ['fitur' => 'Pendaftaran Saya']); }
    public function pilihJalur()  { return view('portal.coming_soon', ['fitur' => 'Pilih Jalur']); }
    public function store()       { return back()->with('info', 'Fitur dalam pengembangan.'); }
    public function show($id)     { return view('portal.coming_soon', ['fitur' => 'Detail Pendaftaran']); }
    public function saveFormulir($id)           { return back()->with('info', 'Fitur dalam pengembangan.'); }
    public function uploadDokumen($id, $syaratId)   { return back()->with('info', 'Fitur dalam pengembangan.'); }
    public function hapusDokumen($id, $dokumenId)   { return back()->with('info', 'Fitur dalam pengembangan.'); }
    public function submit($id)   { return back()->with('info', 'Fitur dalam pengembangan.'); }
}
