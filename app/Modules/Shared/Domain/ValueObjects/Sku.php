<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\InvalidSku;
use Stringable;

/**
 * Immutable stock-keeping unit identifier. Normalised to upper-case and
 * validated on construction so an invalid SKU can never enter the domain.
 */
final class Sku implements Stringable
{
    private const PATTERN = '/^[A-Z0-9][A-Z0-9\-_]*$/';

    private function __construct(private readonly string $value) {}

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw InvalidSku::forValue($value);
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
