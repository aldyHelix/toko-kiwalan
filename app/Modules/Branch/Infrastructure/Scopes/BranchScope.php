<?php

declare(strict_types=1);

namespace App\Modules\Branch\Infrastructure\Scopes;

use App\Models\Admin;
use App\Modules\Shared\Domain\Access\BranchAccessPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Constrains branch-owned queries to the authenticated admin's branch.
 *
 * Attached to branch-owned models from their module service provider (so the
 * Domain layer imports nothing from Infrastructure). Unscoped roles
 * (super-admin/admin) and non-admin contexts (the storefront `web` guard) see
 * everything; a branch-scoped admin sees only rows for their assigned branch,
 * and nothing at all if they have no branch assigned.
 *
 * The constrained column is configurable so later branch-owned tables
 * (branch_stock, orders) reuse this verbatim — `new BranchScope('branch_id')` —
 * while the Branch aggregate scopes on its own primary key.
 */
final class BranchScope implements Scope
{
    public function __construct(private readonly string $column = 'branch_id') {}

    public function apply(Builder $builder, Model $model): void
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin instanceof Admin) {
            return;
        }

        $policy = new BranchAccessPolicy;

        if ($policy->hasUnscopedAccess($admin->roleNames())) {
            return;
        }

        $branchId = $admin->branchId();

        if ($branchId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->getTable().'.'.$this->column, $branchId);
    }
}
