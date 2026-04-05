<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

/** Placeholder — akan diimplementasi pada Milestone 8 (Daftar Ulang). */
class DaftarUlangPesertaController extends Controller
{
    public function index($pendaftaranId) { return view('portal.coming_soon', ['fitur' => 'Daftar Ulang']); }
    public function store($pendaftaranId) { return back()->with('info', 'Fitur dalam pengembangan.'); }
}
