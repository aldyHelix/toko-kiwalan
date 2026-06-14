<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Exceptions;

use InvalidArgumentException;

final class InvalidSlug extends InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        return new self("Invalid slug: \"{$value}\". Expected lowercase letters, numbers and single hyphens.");
    }
}
