<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all named routes into permissions table without duplicates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routeNames = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName())
            ->map(fn($route) => $route->getName())
            ->unique()
            ->values()
            ->toArray();

        $existingPermissions = Permission::all();
        $existingNames = $existingPermissions->pluck('permission_name')->toArray();

        $newCount = 0;
        $reactivated = 0;
        $deactivated = 0;

        // Tambah permission baru + aktifkan ulang yang ada
        foreach ($routeNames as $name) {
            $permission = $existingPermissions->firstWhere('permission_name', $name);

            if (!$permission) {
                Permission::create([
                    'permission_name' => $name,
                    'permission_description' => ucwords(str_replace(['.', '-'], ' ', $name)),
                    'is_active' => true
                ]);
                $newCount++;
                $this->info("✅ Created: $name");
            } else {
                if (!$permission->is_active) {
                    $permission->update(['is_active' => true]);
                    $reactivated++;
                    $this->info("♻️ Reactivated: $name");
                }
            }
        }

        // Nonaktifkan permission yang tidak ditemukan di route
        foreach ($existingPermissions as $permission) {
            if (!in_array($permission->permission_name, $routeNames) && $permission->is_active) {
                $permission->update(['is_active' => false]);
                $deactivated++;
                $this->warn("❌ Deactivated: {$permission->permission_name}");
            }
        }

        $this->line("\n=== Summary ===");
        $this->info("🆕 New: $newCount");
        $this->info("♻️ Reactivated: $reactivated");
        $this->info("❌ Deactivated: $deactivated");
        $this->info("✅ Total active: " . Permission::where('is_active', true)->count());
    }
}
