<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

/** Placeholder — akan diimplementasi pada Milestone 7 (Payment Gateway). */
class PembayaranPesertaController extends Controller
{
    public function index($id)    { return view('portal.coming_soon', ['fitur' => 'Pembayaran']); }
    public function getToken($id) { return response()->json(['message' => 'Fitur dalam pengembangan.'], 503); }
}
