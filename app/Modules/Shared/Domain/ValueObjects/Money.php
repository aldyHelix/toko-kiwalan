<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use App\Modules\Shared\Domain\Exceptions\CurrencyMismatch;
use Money\Currency;
use Money\Money as PhpMoney;
use Stringable;

/**
 * Immutable money value object — a thin wrapper over moneyphp/money.
 *
 * Amounts are always stored as integer minor units (e.g. rupiah) with an
 * explicit currency; never as floats (architecture §5 / coding-style). Every
 * operation returns a new instance, and cross-currency arithmetic is rejected.
 */
final class Money implements Stringable
{
    public const DEFAULT_CURRENCY = 'IDR';

    private function __construct(private readonly PhpMoney $money) {}

    public static function of(int $minorUnits, string $currency = self::DEFAULT_CURRENCY): self
    {
        return new self(new PhpMoney($minorUnits, new Currency(strtoupper($currency))));
    }

    public static function idr(int $minorUnits): self
    {
        return self::of($minorUnits, 'IDR');
    }

    public static function zero(string $currency = self::DEFAULT_CURRENCY): self
    {
        return self::of(0, $currency);
    }

    /**
     * Minor units (e.g. the integer rupiah amount).
     */
    public function amount(): int
    {
        return (int) $this->money->getAmount();
    }

    public function currency(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->money->add($other->money));
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->money->subtract($other->money));
    }

    public function multiply(int $multiplier): self
    {
        return new self($this->money->multiply($multiplier));
    }

    public function equals(self $other): bool
    {
        // moneyphp returns false (never throws) when currencies differ.
        return $this->money->equals($other->money);
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->money->greaterThan($other->money);
    }

    public function greaterThanOrEqual(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->money->greaterThanOrEqual($other->money);
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->money->lessThan($other->money);
    }

    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isPositive(): bool
    {
        return $this->money->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency() !== $other->currency()) {
            throw CurrencyMismatch::between($this->currency(), $other->currency());
        }
    }

    public function __toString(): string
    {
        return $this->amount().' '.$this->currency();
    }
}
