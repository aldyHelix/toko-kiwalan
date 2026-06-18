<?php

declare(strict_types=1);

use App\Modules\Branch\Application\Queries\ListActiveBranches;
use App\Modules\Branch\Application\Queries\ResolveActiveBranch;
use App\Modules\Branch\Domain\Models\Branch;

it('lists only active branches', function () {
    Branch::factory()->count(2)->create();
    Branch::factory()->inactive()->create();

    expect(app(ListActiveBranches::class)->handle())->toHaveCount(2);
});

it('resolves the session-selected branch when it is active', function () {
    $a = Branch::factory()->create(['name' => 'Alpha']);
    $b = Branch::factory()->create(['name' => 'Beta']);
    $active = app(ListActiveBranches::class)->handle();

    expect(app(ResolveActiveBranch::class)->handle($active, $b->id)->id)->toBe($b->id)
        ->and(app(ResolveActiveBranch::class)->handle($active, $a->id)->id)->toBe($a->id);
});

it('falls back to the first active branch when the session id is unknown or null', function () {
    Branch::factory()->create(['name' => 'Alpha']);
    Branch::factory()->create(['name' => 'Beta']);
    $active = app(ListActiveBranches::class)->handle();

    // active() orders by name → Alpha first.
    expect(app(ResolveActiveBranch::class)->handle($active, 9999)->name)->toBe('Alpha')
        ->and(app(ResolveActiveBranch::class)->handle($active, null)->name)->toBe('Alpha');
});

it('returns null when there are no active branches', function () {
    $active = app(ListActiveBranches::class)->handle();

    expect(app(ResolveActiveBranch::class)->handle($active, 5))->toBeNull();
});
