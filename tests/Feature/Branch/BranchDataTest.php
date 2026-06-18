<?php

declare(strict_types=1);

use App\Modules\Branch\Application\DTO\BranchData;
use App\Modules\Branch\Application\DTO\BranchSummaryData;
use App\Modules\Branch\Domain\Models\Branch;

it('builds a branch data payload from an array', function () {
    $data = BranchData::from(['name' => 'Cabang A', 'code' => 'A-1', 'address' => 'Jl. A', 'is_active' => false]);

    expect($data->name)->toBe('Cabang A')
        ->and($data->code)->toBe('A-1')
        ->and($data->address)->toBe('Jl. A')
        ->and($data->is_active)->toBeFalse();
});

it('defaults address to null and is_active to true', function () {
    $data = BranchData::from(['name' => 'Cabang A', 'code' => 'A-1']);

    expect($data->address)->toBeNull()
        ->and($data->is_active)->toBeTrue();
});

it('maps a branch model into a lightweight summary', function () {
    $branch = Branch::factory()->create(['name' => 'Cabang A', 'code' => 'A-1']);

    $summary = BranchSummaryData::from($branch);

    expect($summary->id)->toBe($branch->id)
        ->and($summary->code)->toBe('A-1')
        ->and($summary->name)->toBe('Cabang A');
});
