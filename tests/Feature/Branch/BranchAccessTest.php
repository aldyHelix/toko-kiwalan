<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Modules\Branch\Application\Queries\ListActiveBranches;
use App\Modules\Branch\Domain\Models\Branch;
use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    // Seed so the catalogued roles carry their permissions.
    $this->seed(RolePermissionSeeder::class);
});

/*
 * Policy wiring (BranchPolicy → BranchAccessPolicy).
 */

it('lets an unscoped admin view and manage any branch', function () {
    $admin = Admin::factory()->admin()->create();
    $branch = Branch::factory()->create();

    expect(Gate::forUser($admin)->allows('view', $branch))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $branch))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', Branch::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $branch))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('deleteAny', Branch::class))->toBeTrue();
});

it('limits a branch-scoped admin to their own branch', function () {
    $own = Branch::factory()->create();
    $other = Branch::factory()->create();

    // A branch-scoped admin (branch-manager role) assigned to one branch. We
    // grant MANAGE_BRANCHES directly to isolate the branch dimension from the
    // permission dimension.
    $admin = Admin::factory()->branchManager()->create(['branch_id' => $own->id]);
    $admin->givePermissionTo(Rbac::MANAGE_BRANCHES);

    expect(Gate::forUser($admin)->allows('view', $own))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $own))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view', $other))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('update', $other))->toBeFalse()
        // Mutations stay with unscoped roles only.
        ->and(Gate::forUser($admin)->allows('create', Branch::class))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('deleteAny', Branch::class))->toBeFalse();
});

it('denies a default branch-manager any branch access (no manage-branches permission)', function () {
    $admin = Admin::factory()->branchManager()->create();
    $branch = Branch::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', Branch::class))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $branch))->toBeFalse();
});

/*
 * Global scope (BranchScope) — the data-layer half of branch scoping.
 */

it('hides other branches from a branch-scoped admin via the global scope', function () {
    $own = Branch::factory()->create();
    Branch::factory()->create();
    Branch::factory()->create();

    $this->actingAs(Admin::factory()->branchManager()->create(['branch_id' => $own->id]), 'admin');

    expect(Branch::query()->pluck('id')->all())->toBe([$own->id]);
});

it('hides every branch from a branch-scoped admin with no branch assigned', function () {
    Branch::factory()->count(2)->create();

    $this->actingAs(Admin::factory()->branchManager()->create(['branch_id' => null]), 'admin');

    expect(Branch::query()->count())->toBe(0);
});

it('shows all branches to an unscoped admin', function () {
    Branch::factory()->count(3)->create();

    $this->actingAs(Admin::factory()->admin()->create(), 'admin');

    expect(Branch::query()->count())->toBe(3);
});

it('does not scope branches on the storefront (no admin authenticated)', function () {
    Branch::factory()->count(2)->create();

    expect(Branch::query()->count())->toBe(2);
});

it('lists every active branch publicly even when a branch-scoped admin is authenticated', function () {
    $own = Branch::factory()->create();
    Branch::factory()->create();
    Branch::factory()->create();

    // Admin browsing the storefront in the same session must not narrow the
    // public branch list (active() bypasses the branch scope).
    $this->actingAs(Admin::factory()->branchManager()->create(['branch_id' => $own->id]), 'admin');

    expect(app(ListActiveBranches::class)->handle())->toHaveCount(3);
});
