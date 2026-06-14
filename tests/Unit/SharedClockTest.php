<?php

declare(strict_types=1);

use App\Modules\Shared\Infrastructure\SystemClock;

it('returns the current time as an immutable value', function () {
    $clock = new SystemClock;

    expect($clock->now())->toBeInstanceOf(DateTimeImmutable::class);
});
