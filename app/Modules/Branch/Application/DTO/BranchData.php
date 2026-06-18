<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\DTO;

use App\Modules\Shared\Application\DTO\Data;

/**
 * Immutable payload for creating/updating a branch. Crosses the
 * Presentation→Application boundary so layers never pass raw arrays
 * (architecture §5 / coding-style immutability).
 */
final class BranchData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $address = null,
        public readonly bool $is_active = true,
    ) {}
}
