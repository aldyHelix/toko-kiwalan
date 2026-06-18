<?php

declare(strict_types=1);

namespace App\Modules\Branch\Domain\Contracts;

use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Shared\Domain\Contracts\Repository;
use Illuminate\Support\Collection;

/**
 * Persistence contract for branches. Extends the Shared base CRUD surface with
 * the domain finders the module needs; bound to {@see EloquentBranchRepository}
 * in the Branch service provider (architecture §8 — DIP).
 *
 * @extends Repository<Branch>
 */
interface BranchRepository extends Repository
{
    public function findByCode(string $code): ?Branch;

    /**
     * Active branches, ordered by name — the storefront-selectable set.
     *
     * @return Collection<int, Branch>
     */
    public function active(): Collection;
}
