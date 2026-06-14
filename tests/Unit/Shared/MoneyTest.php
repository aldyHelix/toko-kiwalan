<?php

declare(strict_types=1);

use App\Modules\Shared\Domain\Exceptions\CurrencyMismatch;
use App\Modules\Shared\Domain\ValueObjects\Money;

it('exposes integer minor units and an explicit currency', function () {
    $money = Money::of(15_000, 'IDR');

    expect($money->amount())->toBe(15_000)
        ->and($money->currency())->toBe('IDR');
});

it('defaults to IDR and uppercases the currency code', function () {
    expect(Money::of(100)->currency())->toBe('IDR')
        ->and(Money::idr(250)->currency())->toBe('IDR')
        ->and(Money::of(100, 'usd')->currency())->toBe('USD');
});

it('adds and subtracts amounts of the same currency immutably', function () {
    $a = Money::idr(10_000);
    $b = Money::idr(2_500);

    expect($a->add($b)->amount())->toBe(12_500)
        ->and($a->subtract($b)->amount())->toBe(7_500)
        // originals are untouched (immutability).
        ->and($a->amount())->toBe(10_000)
        ->and($b->amount())->toBe(2_500);
});

it('multiplies by an integer factor', function () {
    expect(Money::idr(1_500)->multiply(3)->amount())->toBe(4_500);
});

it('rejects adding money of a different currency', function () {
    Money::idr(1_000)->add(Money::of(1_000, 'USD'));
})->throws(CurrencyMismatch::class);

it('rejects subtracting money of a different currency', function () {
    Money::idr(1_000)->subtract(Money::of(1_000, 'USD'));
})->throws(CurrencyMismatch::class);

it('rejects comparing money of a different currency', function () {
    Money::idr(1_000)->greaterThan(Money::of(1_000, 'USD'));
})->throws(CurrencyMismatch::class);

it('treats equal amount and currency as equal', function () {
    expect(Money::idr(5_000)->equals(Money::idr(5_000)))->toBeTrue()
        ->and(Money::idr(5_000)->equals(Money::idr(6_000)))->toBeFalse();
});

it('is never equal across currencies even for the same amount', function () {
    expect(Money::idr(5_000)->equals(Money::of(5_000, 'USD')))->toBeFalse();
});

it('compares ordering within the same currency', function () {
    $small = Money::idr(1_000);
    $large = Money::idr(9_000);

    expect($large->greaterThan($small))->toBeTrue()
        ->and($small->lessThan($large))->toBeTrue()
        ->and($large->greaterThanOrEqual(Money::idr(9_000)))->toBeTrue();
});

it('reports sign correctly', function () {
    expect(Money::zero()->isZero())->toBeTrue()
        ->and(Money::idr(1)->isPositive())->toBeTrue()
        ->and(Money::idr(-1)->isNegative())->toBeTrue();
});

it('renders a string of amount and currency', function () {
    expect((string) Money::idr(7_500))->toBe('7500 IDR');
});
