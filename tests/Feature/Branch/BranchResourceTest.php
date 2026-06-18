<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages\CreateBranch;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages\EditBranch;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages\ListBranches;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->actingAs(Admin::factory()->admin()->create(), 'admin');
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('lists branches for an authorized admin', function () {
    $branch = Branch::factory()->create(['code' => 'JKT-001']);

    $this->get('/admin/branches')
        ->assertOk()
        ->assertSee('JKT-001');
});

it('creates a branch through the resource form', function () {
    Livewire::test(CreateBranch::class)
        ->fillForm([
            'name' => 'Cabang Jakarta',
            'code' => 'JKT-001',
            'address' => 'Jl. Sudirman',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('branches', ['code' => 'JKT-001', 'name' => 'Cabang Jakarta']);
});

it('validates that the branch code is unique', function () {
    Branch::factory()->create(['code' => 'JKT-001']);

    Livewire::test(CreateBranch::class)
        ->fillForm([
            'name' => 'Cabang Lain',
            'code' => 'JKT-001',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('updates a branch through the resource form', function () {
    $branch = Branch::factory()->create(['name' => 'Lama', 'code' => 'OLD-1']);

    Livewire::test(EditBranch::class, ['record' => $branch->getRouteKey()])
        ->fillForm([
            'name' => 'Baru',
            'code' => 'OLD-1',
            'address' => null,
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($branch->refresh()->name)->toBe('Baru');
});

it('toggles a branch active state through the table action', function () {
    $branch = Branch::factory()->create(['is_active' => true]);

    Livewire::test(ListBranches::class)
        ->callAction(TestAction::make('toggleActive')->table($branch));

    expect($branch->refresh()->is_active)->toBeFalse();
});

it('forbids the branch resource for a manager without the manage-branches permission', function () {
    $this->actingAs(Admin::factory()->branchManager()->create(), 'admin');

    $this->get('/admin/branches')->assertForbidden();
});
