<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Access;

/**
 * Pure decision logic for per-branch access — the branch-scoping skeleton.
 *
 * Fase 1 ships only this framework-free policy; Fase 2 wires it into an Eloquent
 * global scope and a Laravel policy once `branches` and the admin-branch relation
 * exist. Kept self-contained (its own role constants, mirroring the App\Support
 * Rbac catalogue) so the Domain layer imports nothing outward.
 */
final class BranchAccessPolicy
{
    /**
     * Roles that may see/manage every branch.
     *
     * @var array<int, string>
     */
    public const UNSCOPED_ROLES = ['super-admin', 'admin'];

    /**
     * @param  array<int, string>  $roles  the admin's role names
     */
    public function hasUnscopedAccess(array $roles): bool
    {
        return array_intersect($roles, self::UNSCOPED_ROLES) !== [];
    }

    /**
     * Whether an admin with the given roles and assigned branch may act on a
     * target branch. Unscoped roles may act on any; otherwise only their own.
     *
     * @param  array<int, string>  $roles
     */
    public function canAccessBranch(array $roles, ?int $assignedBranchId, int $targetBranchId): bool
    {
        if ($this->hasUnscopedAccess($roles)) {
            return true;
        }

        return $assignedBranchId !== null && $assignedBranchId === $targetBranchId;
    }
}
