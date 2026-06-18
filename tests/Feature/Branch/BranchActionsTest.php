<?php

declare(strict_types=1);

use App\Modules\Branch\Application\Actions\CreateBranch;
use App\Modules\Branch\Application\Actions\ToggleBranchActive;
use App\Modules\Branch\Application\Actions\UpdateBranch;
use App\Modules\Branch\Application\DTO\BranchData;
use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('creates a branch from a data payload', function () {
    $branch = app(CreateBranch::class)->handle(
        new BranchData(name: 'Cabang Jakarta', code: 'JKT-1', address: 'Jl. Sudirman', is_active: true),
    );

    expect($branch->exists)->toBeTrue()
        ->and($branch->name)->toBe('Cabang Jakarta')
        ->and($branch->code)->toBe('JKT-1')
        ->and($branch->is_active)->toBeTrue();

    $this->assertDatabaseHas('branches', ['code' => 'JKT-1', 'address' => 'Jl. Sudirman']);
});

it('updates an existing branch', function () {
    $branch = Branch::factory()->create(['name' => 'Lama', 'code' => 'OLD-1']);

    $updated = app(UpdateBranch::class)->handle(
        $branch,
        new BranchData(name: 'Baru', code: 'OLD-1', address: null, is_active: false),
    );

    expect($updated->name)->toBe('Baru')
        ->and($updated->is_active)->toBeFalse();
    $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'Baru']);
});

it('toggles the active flag back and forth', function () {
    $branch = Branch::factory()->create(['is_active' => true]);

    $off = app(ToggleBranchActive::class)->handle($branch);
    expect($off->is_active)->toBeFalse();

    $on = app(ToggleBranchActive::class)->handle($branch->refresh());
    expect($on->is_active)->toBeTrue();
});

it('throws when toggling a branch that does not exist', function () {
    app(ToggleBranchActive::class)->handle(999);
})->throws(ModelNotFoundException::class);

it('returns active branches ordered by name', function () {
    Branch::factory()->create(['name' => 'Zeta', 'is_active' => true]);
    Branch::factory()->create(['name' => 'Alpha', 'is_active' => true]);
    Branch::factory()->inactive()->create(['name' => 'Beta']);

    $active = app(BranchRepository::class)->active();

    expect($active)->toHaveCount(2)
        ->and($active->first()->name)->toBe('Alpha');
});

it('finds a branch by its code', function () {
    $branch = Branch::factory()->create(['code' => 'ABC-1']);

    expect(app(BranchRepository::class)->findByCode('ABC-1')->is($branch))->toBeTrue()
        ->and(app(BranchRepository::class)->findByCode('NOPE'))->toBeNull();
});

it('lists every branch ordered by name', function () {
    Branch::factory()->create(['name' => 'Zeta']);
    Branch::factory()->inactive()->create(['name' => 'Alpha']);

    $all = app(BranchRepository::class)->findAll();

    expect($all)->toHaveCount(2)
        ->and($all->first()->name)->toBe('Alpha');
});

it('finds a branch by its id', function () {
    $branch = Branch::factory()->create();

    expect(app(BranchRepository::class)->findById($branch->id)->is($branch))->toBeTrue()
        ->and(app(BranchRepository::class)->findById(999))->toBeNull();
});

it('deletes a branch by id', function () {
    $branch = Branch::factory()->create();

    expect(app(BranchRepository::class)->delete($branch->id))->toBeTrue();
    $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
});
