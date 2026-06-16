<?php

declare(strict_types=1);

use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Support\Rupiah;

it('formats integer minor units with thousands separators', function () {
    expect(Rupiah::format(1_500_000))->toBe('Rp 1.500.000')
        ->and(Rupiah::format(0))->toBe('Rp 0');
});

it('formats a Money value object', function () {
    expect(Rupiah::format(Money::idr(25_000)))->toBe('Rp 25.000');
});

it('prefixes negative amounts with a sign', function () {
    expect(Rupiah::format(-2_500))->toBe('-Rp 2.500');
});
