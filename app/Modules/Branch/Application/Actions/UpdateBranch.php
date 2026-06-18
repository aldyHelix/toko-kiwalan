<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\Actions;

use App\Modules\Branch\Application\DTO\BranchData;
use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;

/**
 * Updates an existing branch from a validated {@see BranchData} payload.
 */
final class UpdateBranch
{
    public function __construct(private readonly BranchRepository $branches) {}

    public function handle(Branch|int $branch, BranchData $data): Branch
    {
        $id = $branch instanceof Branch ? $branch->getKey() : $branch;

        return $this->branches->update($id, [
            'name' => $data->name,
            'code' => $data->code,
            'address' => $data->address,
            'is_active' => $data->is_active,
        ]);
    }
}
