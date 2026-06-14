<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\InvalidSlug;
use Illuminate\Support\Str;
use Stringable;

/**
 * Immutable URL slug. Either built from an arbitrary title (slugified) or
 * validated from an existing slug string.
 */
final class Slug implements Stringable
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private function __construct(private readonly string $value) {}

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if (preg_match(self::PATTERN, $normalized) !== 1) {
            throw InvalidSlug::forValue($value);
        }

        return new self($normalized);
    }

    public static function fromTitle(string $title): self
    {
        $slug = Str::slug($title);

        if ($slug === '') {
            throw InvalidSlug::forValue($title);
        }

        return new self($slug);
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
