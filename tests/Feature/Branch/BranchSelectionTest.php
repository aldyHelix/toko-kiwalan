<?php

declare(strict_types=1);

use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Presentation\Http\Controllers\BranchSelectionController;
use Inertia\Testing\AssertableInertia;

it('persists the selected branch in the session', function () {
    $branch = Branch::factory()->create();

    $this->post('/branch/select', ['branch_id' => $branch->id])
        ->assertRedirect();

    expect(session(BranchSelectionController::SESSION_KEY))->toBe($branch->id);
});

it('rejects selecting an inactive branch', function () {
    $branch = Branch::factory()->inactive()->create();

    $this->post('/branch/select', ['branch_id' => $branch->id])
        ->assertSessionHasErrors('branch_id');

    expect(session(BranchSelectionController::SESSION_KEY))->toBeNull();
});

it('rejects selecting a branch that does not exist', function () {
    $this->post('/branch/select', ['branch_id' => 999])
        ->assertSessionHasErrors('branch_id');
});

it('exposes active branches and the resolved active branch as inertia shared props', function () {
    Branch::factory()->create(['name' => 'Alpha']);
    $beta = Branch::factory()->create(['name' => 'Beta']);

    $this->withSession([BranchSelectionController::SESSION_KEY => $beta->id])
        ->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Home')
            ->has('branch.available', 2)
            ->where('branch.active.id', $beta->id)
            ->where('branch.active.name', 'Beta'));
});

it('defaults the active shared branch to the first active branch when none is selected', function () {
    $alpha = Branch::factory()->create(['name' => 'Alpha']);
    Branch::factory()->create(['name' => 'Beta']);

    $this->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('branch.active.id', $alpha->id));
});

it('exposes no active branch when none exist', function () {
    $this->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('branch.available', 0)
            ->where('branch.active', null));
});
