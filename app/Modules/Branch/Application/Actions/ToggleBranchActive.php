<?php

declare(strict_types=1);

namespace App\Modules\Branch\Application\Actions;

use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Flips a branch's active flag. Deactivating hides it from the storefront
 * selector without deleting its data/history.
 */
final class ToggleBranchActive
{
    public function __construct(private readonly BranchRepository $branches) {}

    public function handle(Branch|int $branch): Branch
    {
        $model = $branch instanceof Branch
            ? $branch
            : $this->branches->findById($branch);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel(Branch::class, [$branch]);
        }

        return $this->branches->update($model->getKey(), [
            'is_active' => ! $model->is_active,
        ]);
    }
}
