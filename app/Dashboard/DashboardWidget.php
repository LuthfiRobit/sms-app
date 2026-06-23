<?php

namespace App\Dashboard;

use Illuminate\Support\Facades\Cache;

abstract class DashboardWidget
{
    // ── Wajib di-override ────────────────────────────────────────────────────
    abstract public function id(): string;
    abstract public function title(): string;
    abstract public function size(): string;
    abstract public function order(): int;
    abstract public function view(): string;
    abstract protected function build(): array;

    // ── Opsional (ada default) ───────────────────────────────────────────────
    public function permission(): ?string { return null; }
    public function cacheTtl(): int       { return 0; }
    public function lazy(): bool          { return false; }

    // ── Implemented di base — jangan di-override ────────────────────────────

    final public function shouldRender(): bool
    {
        $perm = $this->permission();
        return $perm === null
            || auth()->user()?->hasPermissionTo($perm);
    }

    /**
     * Ambil data widget. Dipakai oleh dashboard view via @include.
     * Menerapkan cache jika cacheTtl() > 0, key-nya unik per widget + lembaga.
     */
    final public function getData(): array
    {
        if ($this->cacheTtl() > 0) {
            $key = 'dash_widget.' . $this->id() . '.' . (app('active_lembaga_id') ?? '0');
            return Cache::remember($key, $this->cacheTtl(), fn () => $this->build());
        }

        return $this->build();
    }

    /**
     * Dipakai khusus untuk lazy-load (AJAX endpoint).
     * Widget yang lazy=true tidak boleh pakai @push('scripts') di blade-nya.
     */
    final public function render(): string
    {
        if (! $this->shouldRender()) {
            return '';
        }

        return view($this->view(), $this->getData())->render();
    }
}
