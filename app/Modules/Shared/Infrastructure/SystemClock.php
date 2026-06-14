<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure;

use App\Modules\Shared\Domain\Contracts\Clock;
use DateTimeImmutable;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable;
    }
}
