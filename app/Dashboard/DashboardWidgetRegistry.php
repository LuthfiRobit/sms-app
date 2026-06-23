<?php

namespace App\Dashboard;

use Illuminate\Support\Collection;

class DashboardWidgetRegistry
{
    private array $widgets = [];

    public function register(DashboardWidget ...$ws): void
    {
        foreach ($ws as $w) {
            $this->widgets[$w->id()] = $w;
        }
    }

    public function all(): Collection
    {
        return collect($this->widgets)
            ->filter->shouldRender()
            ->sortBy->order()
            ->values();
    }

    public function find(string $id): ?DashboardWidget
    {
        return $this->widgets[$id] ?? null;
    }
}
