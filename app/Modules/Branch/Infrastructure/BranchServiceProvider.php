<?php

declare(strict_types=1);

namespace App\Modules\Branch\Infrastructure;

use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Infrastructure\Policies\BranchPolicy;
use App\Modules\Branch\Infrastructure\Repositories\EloquentBranchRepository;
use App\Modules\Branch\Infrastructure\Scopes\BranchScope;
use App\Modules\Shared\Infrastructure\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Service provider for the Branch module. Binds the repository contract, wires
 * the branch authorization policy, and attaches branch-scoping to the Branch
 * aggregate (architecture §7). Migrations under Database/Migrations are loaded
 * by the base provider.
 */
class BranchServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string
    {
        // app/Modules/Branch/Infrastructure → app/Modules/Branch
        return dirname(__DIR__);
    }

    protected function registerBindings(): void
    {
        $this->app->bind(BranchRepository::class, EloquentBranchRepository::class);
    }

    protected function bootModule(): void
    {
        Gate::policy(Branch::class, BranchPolicy::class);

        // Scope the aggregate by its own primary key; later branch-owned tables
        // reuse BranchScope with their own column (e.g. 'branch_id').
        Branch::addGlobalScope(new BranchScope('id'));
    }
}
