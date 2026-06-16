<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Exceptions;

use DomainException;

/**
 * Thrown when an arithmetic or comparison operation is attempted between two
 * Money instances of different currencies — money of distinct currencies is
 * never directly comparable.
 */
final class CurrencyMismatch extends DomainException
{
    public static function between(string $left, string $right): self
    {
        return new self("Cannot operate on money of different currencies: {$left} vs {$right}.");
    }
}
