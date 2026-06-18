<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\DTO;

use App\Modules\Shared\Application\DTO\Data;

/**
 * Lightweight, immutable branch view exposed to the storefront (Inertia shared
 * props) — never the Eloquent model. Carries only what the branch switcher and
 * active-branch indicator need.
 */
final class BranchSummaryData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name,
    ) {}
}
