<?php

declare(strict_types=1);

use App\Modules\Shared\Domain\Exceptions\InvalidSku;
use App\Modules\Shared\Domain\ValueObjects\Sku;

it('normalises a sku to upper case and trims whitespace', function () {
    expect(Sku::fromString('  abc-123  ')->value())->toBe('ABC-123');
});

it('treats skus with the same normalised value as equal', function () {
    expect(Sku::fromString('sku-1')->equals(Sku::fromString('SKU-1')))->toBeTrue()
        ->and(Sku::fromString('sku-1')->equals(Sku::fromString('sku-2')))->toBeFalse();
});

it('casts to its string value', function () {
    expect((string) Sku::fromString('TKW-001'))->toBe('TKW-001');
});

it('accepts alphanumerics, dashes and underscores', function (string $value) {
    expect(Sku::fromString($value)->value())->toBe(strtoupper($value));
})->with(['A1', 'TKW-001', 'sku_99', 'P1-A_B2']);

it('rejects empty or malformed skus', function (string $value) {
    Sku::fromString($value);
})->throws(InvalidSku::class)->with(['', '   ', '-leading', 'has space', 'sku!', 'sku/1']);
