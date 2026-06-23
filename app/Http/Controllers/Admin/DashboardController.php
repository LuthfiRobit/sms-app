<?php

namespace App\Http\Controllers\Admin;

use App\Dashboard\DashboardWidgetRegistry;
use App\Http\Controllers\Controller;
use App\Models\Master\Lembaga;
use App\Models\Transaksi\Pendaftaran;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $lid     = app('active_lembaga_id');
        $widgets = app(DashboardWidgetRegistry::class)->all();

        $lembagaAktifNama = $lid
            ? Lembaga::find($lid)?->nama ?? 'Lembaga'
            : 'Semua Lembaga';

        $needsAction = Pendaftaran::when($lid, fn ($q) => $q->where('lembaga_id', $lid))
            ->whereIn('status', ['submit', 'verifikasi'])
            ->count();

        return view('admin.dashboard.index', compact('widgets', 'lembagaAktifNama', 'needsAction'));
    }

    public function widget(string $id): JsonResponse
    {
        $widget = app(DashboardWidgetRegistry::class)->find($id);
        abort_unless($widget?->shouldRender(), 404);

        return response()->json(['html' => $widget->render()]);
    }
}
