<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRolesAndPermissions
{
    /**
     * Cache for roles and permissions to avoid N+1 queries during a single request.
     */
    protected $memoizedRoles = null;
    protected $memoizedPermissions = null;

    /**
     * User belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id', 'id_user', 'id');
    }

    /**
     * User can have direct permissions (exceptions/addons).
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission', 'user_id', 'permission_id', 'id_user', 'id');
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        if ($this->memoizedRoles === null) {
            $this->memoizedRoles = $this->roles->pluck('name')->toArray();
        }

        return in_array($roleName, $this->memoizedRoles);
    }

    /**
     * Check if the user has a specific permission.
     * Searches both roles' permissions and user's direct permissions.
     *
     * @param string|null $permissionName
     * @return bool
     */
    public function hasPermissionTo(?string $permissionName): bool
    {
        if (empty($permissionName)) {
            return false;
        }

        if ($this->memoizedPermissions === null) {
            $this->loadPermissionsIntoMemory();
        }

        return in_array($permissionName, $this->memoizedPermissions);
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param array $permissionNames
     * @return bool
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        if ($this->memoizedPermissions === null) {
            $this->loadPermissionsIntoMemory();
        }

        foreach ($permissionNames as $permissionName) {
            if (in_array($permissionName, $this->memoizedPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load all permissions into memory to prevent N+1 queries.
     * Includes both Role-based permissions and Direct permissions.
     */
    protected function loadPermissionsIntoMemory(): void
    {
        // Get permissions from all roles the user has
        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('permission_name')
            ->toArray();

        // Get direct user permissions
        $directPermissions = $this->permissions()
            ->get()
            ->pluck('permission_name')
            ->toArray();

        $this->memoizedPermissions = array_unique(array_merge($rolePermissions, $directPermissions));
    }
}
