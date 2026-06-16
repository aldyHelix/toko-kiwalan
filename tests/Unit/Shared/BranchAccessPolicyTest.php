<?php

declare(strict_types=1);

use App\Modules\Shared\Domain\Access\BranchAccessPolicy;

beforeEach(function () {
    $this->policy = new BranchAccessPolicy;
});

it('treats super-admin and admin as unscoped', function () {
    expect($this->policy->hasUnscopedAccess(['super-admin']))->toBeTrue()
        ->and($this->policy->hasUnscopedAccess(['admin']))->toBeTrue()
        ->and($this->policy->hasUnscopedAccess(['branch-manager']))->toBeFalse()
        ->and($this->policy->hasUnscopedAccess([]))->toBeFalse();
});

it('lets unscoped roles access any branch', function () {
    expect($this->policy->canAccessBranch(['admin'], null, 99))->toBeTrue()
        ->and($this->policy->canAccessBranch(['super-admin'], 1, 99))->toBeTrue();
});

it('limits a branch-manager to their assigned branch', function () {
    expect($this->policy->canAccessBranch(['branch-manager'], 5, 5))->toBeTrue()
        ->and($this->policy->canAccessBranch(['branch-manager'], 5, 6))->toBeFalse();
});

it('denies a branch-manager with no assigned branch', function () {
    expect($this->policy->canAccessBranch(['branch-manager'], null, 5))->toBeFalse();
});
