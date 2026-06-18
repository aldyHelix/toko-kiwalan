<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\Queries;

use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;
use Illuminate\Support\Collection;

/**
 * Read model: the active branches a customer may select from (CQRS-lite —
 * architecture §2). Keeps the storefront out of Eloquent directly.
 */
final class ListActiveBranches
{
    public function __construct(private readonly BranchRepository $branches) {}

    /**
     * @return Collection<int, Branch>
     */
    public function handle(): Collection
    {
        return $this->branches->active();
    }
}
