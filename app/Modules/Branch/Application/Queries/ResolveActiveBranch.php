<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\Queries;

use App\Modules\Branch\Domain\Models\Branch;
use Illuminate\Support\Collection;

/**
 * Resolves the active branch for a request: the session-selected branch if it
 * is still active, otherwise the first active branch as a sensible default.
 *
 * Pure over an already-fetched active set so the request middleware can resolve
 * without issuing a second query.
 */
final class ResolveActiveBranch
{
    /**
     * @param  Collection<int, Branch>  $active  active branches (e.g. from {@see ListActiveBranches})
     */
    public function handle(Collection $active, ?int $sessionBranchId): ?Branch
    {
        if ($sessionBranchId !== null) {
            $selected = $active->firstWhere('id', $sessionBranchId);

            if ($selected instanceof Branch) {
                return $selected;
            }
        }

        return $active->first();
    }
}
