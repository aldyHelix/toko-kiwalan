<?php

declare(strict_types=1);

namespace App\Modules\Branch\Infrastructure\Policies;

use App\Models\Admin;
use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Shared\Domain\Access\BranchAccessPolicy;
use App\Support\Rbac;

/**
 * Authorizes admin access to branches. Gates on the MANAGE_BRANCHES permission,
 * then layers the pure {@see BranchAccessPolicy} branch-scoping decision on top
 * so a branch-scoped admin is limited to their own branch. super-admin bypasses
 * all of this via the Gate::before rule (AppServiceProvider).
 *
 * Mutations (create/delete) are reserved for unscoped roles — branches are a
 * store-wide configuration concern, not a per-branch one.
 */
final class BranchPolicy
{
    public function __construct(private readonly BranchAccessPolicy $access) {}

    public function viewAny(Admin $admin): bool
    {
        return $admin->can(Rbac::MANAGE_BRANCHES);
    }

    public function view(Admin $admin, Branch $branch): bool
    {
        return $admin->can(Rbac::MANAGE_BRANCHES)
            && $this->access->canAccessBranch($admin->roleNames(), $admin->branchId(), (int) $branch->getKey());
    }

    public function create(Admin $admin): bool
    {
        return $admin->can(Rbac::MANAGE_BRANCHES)
            && $this->access->hasUnscopedAccess($admin->roleNames());
    }

    public function update(Admin $admin, Branch $branch): bool
    {
        return $this->view($admin, $branch);
    }

    public function delete(Admin $admin, Branch $branch): bool
    {
        return $this->create($admin);
    }

    /**
     * Gates Filament's bulk-delete toolbar action (which authorizes via
     * `deleteAny`, not `delete`).
     */
    public function deleteAny(Admin $admin): bool
    {
        return $this->create($admin);
    }
}
