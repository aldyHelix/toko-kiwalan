<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Exceptions;

use InvalidArgumentException;

final class InvalidSku extends InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        return new self("Invalid SKU: \"{$value}\". Expected alphanumeric characters, dashes or underscores.");
    }
}
