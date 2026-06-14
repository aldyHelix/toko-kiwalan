<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Contracts;

use DateTimeImmutable;

/**
 * Abstraction over "the current time" so domain logic stays deterministic and
 * testable — production binds `SystemClock`, tests bind a frozen fake. This is the
 * canonical Domain\Contracts -> Infrastructure -> ServiceProvider binding pattern
 * every module follows. (Domain must not import Infrastructure, hence no `@see`.)
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
