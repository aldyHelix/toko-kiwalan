<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Structural RBAC seeder (not demo data): creates the admin-guard permissions
 * and roles and wires the role→permission map. Idempotent — safe to re-run.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Rbac::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, Rbac::GUARD);
        }

        // super-admin gets no explicit permissions (granted via Gate::before).
        Role::findOrCreate(Rbac::SUPER_ADMIN, Rbac::GUARD);

        foreach (Rbac::rolePermissions() as $roleName => $permissions) {
            Role::findOrCreate($roleName, Rbac::GUARD)
                ->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
