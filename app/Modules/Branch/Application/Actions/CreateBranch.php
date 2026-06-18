<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\Actions;

use App\Modules\Branch\Application\DTO\BranchData;
use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;

/**
 * Creates a branch from a validated {@see BranchData} payload (one use case,
 * one class — architecture §8 / SRP).
 */
final class CreateBranch
{
    public function __construct(private readonly BranchRepository $branches) {}

    public function handle(BranchData $data): Branch
    {
        return $this->branches->create([
            'name' => $data->name,
            'code' => $data->code,
            'address' => $data->address,
            'is_active' => $data->is_active,
        ]);
    }
}
