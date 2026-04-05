<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

/** Placeholder — akan diimplementasi pada Milestone 8 (Seleksi & Pengumuman). */
class PengumumanPesertaController extends Controller
{
    public function index()              { return view('portal.coming_soon', ['fitur' => 'Pengumuman']); }
    public function downloadKartu($id)   { return back()->with('info', 'Fitur dalam pengembangan.'); }
}
