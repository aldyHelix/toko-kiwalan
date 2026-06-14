<?php

declare(strict_types=1);

use App\Modules\Shared\Domain\Exceptions\InvalidSlug;
use App\Modules\Shared\Domain\ValueObjects\Slug;

it('builds a slug from an arbitrary title', function () {
    expect(Slug::fromTitle('Kursi Kayu Jati!')->value())->toBe('kursi-kayu-jati');
});

it('accepts an already-valid slug string', function () {
    expect(Slug::fromString('kursi-kayu-jati')->value())->toBe('kursi-kayu-jati');
});

it('treats slugs with the same value as equal', function () {
    expect(Slug::fromString('meja-makan')->equals(Slug::fromTitle('Meja Makan')))->toBeTrue();
});

it('casts to its string value', function () {
    expect((string) Slug::fromString('lampu-led'))->toBe('lampu-led');
});

it('rejects malformed slug strings', function (string $value) {
    Slug::fromString($value);
})->throws(InvalidSlug::class)->with(['', 'Has Spaces', 'UPPER', 'trailing-', '-leading', 'double--hyphen', 'with_underscore']);

it('rejects a title that slugifies to empty', function () {
    Slug::fromTitle('!!!');
})->throws(InvalidSlug::class);
