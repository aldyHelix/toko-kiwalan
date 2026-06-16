<?php

declare(strict_types=1);

namespace App\Support;

use App\Modules\Shared\Domain\ValueObjects\Money;

/**
 * Thin presentation helper for formatting integer rupiah amounts for display.
 * Formatting only — no business logic (money arithmetic lives in {@see Money}).
 */
final class Rupiah
{
    public static function format(int|Money $amount, string $symbol = 'Rp'): string
    {
        $minorUnits = $amount instanceof Money ? $amount->amount() : $amount;

        $formatted = number_format(abs($minorUnits), 0, ',', '.');
        $sign = $minorUnits < 0 ? '-' : '';

        return "{$sign}{$symbol} {$formatted}";
    }
}
