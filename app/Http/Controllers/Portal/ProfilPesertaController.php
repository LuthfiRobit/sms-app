<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

/** Placeholder — akan diimplementasi pada Milestone 5 (Profil Peserta). */
class ProfilPesertaController extends Controller
{
    public function index() { return view('portal.coming_soon', ['fitur' => 'Profil Peserta']); }
    public function updateAkun() { return back()->with('info', 'Fitur dalam pengembangan.'); }
    public function updateDapodik() { return back()->with('info', 'Fitur dalam pengembangan.'); }
    public function updatePassword() { return back()->with('info', 'Fitur dalam pengembangan.'); }
}
