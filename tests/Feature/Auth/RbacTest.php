<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('seeds the three admin roles and the permission catalogue', function () {
    expect(Role::where('guard_name', Rbac::GUARD)->pluck('name')->all())
        ->toEqualCanonicalizing(Rbac::ROLES);

    $admin = Role::findByName(Rbac::ADMIN, Rbac::GUARD);
    expect($admin->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(Rbac::PERMISSIONS);
});

it('scopes branch-manager to catalog and orders only', function () {
    $manager = Admin::factory()->branchManager()->create();

    expect($manager->can(Rbac::MANAGE_CATALOG))->toBeTrue()
        ->and($manager->can(Rbac::MANAGE_ORDERS))->toBeTrue()
        ->and($manager->can(Rbac::MANAGE_PAYMENTS))->toBeFalse()
        ->and($manager->can(Rbac::MANAGE_SETTINGS))->toBeFalse();
});

it('grants a plain admin every catalogued permission but not the super-admin bypass', function () {
    $admin = Admin::factory()->admin()->create();

    expect($admin->can(Rbac::MANAGE_SETTINGS))->toBeTrue()
        ->and($admin->can(Rbac::MANAGE_PAYMENTS))->toBeTrue()
        // An ability that is not in the catalogue is denied for a plain admin.
        ->and($admin->can('some uncatalogued ability'))->toBeFalse();
});

it('lets super-admin pass every check via Gate::before', function () {
    $super = Admin::factory()->superAdmin()->create();

    expect($super->can(Rbac::MANAGE_PAYMENTS))->toBeTrue()
        // super-admin holds no explicit permissions yet still passes anything.
        ->and($super->getDirectPermissions())->toBeEmpty()
        ->and($super->can('some uncatalogued ability'))->toBeTrue();
});

it('keeps roles on the admin guard, not the web guard', function () {
    $manager = Admin::factory()->branchManager()->create();

    expect($manager->hasRole(Rbac::BRANCH_MANAGER))->toBeTrue()
        ->and($manager->roles->first()->guard_name)->toBe('admin');
});
