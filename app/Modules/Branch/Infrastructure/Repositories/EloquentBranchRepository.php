<?php

declare(strict_types=1);

namespace App\Modules\Branch\Infrastructure\Repositories;

use App\Modules\Branch\Domain\Contracts\BranchRepository;
use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Infrastructure\Scopes\BranchScope;
use Illuminate\Support\Collection;

/**
 * Eloquent-backed {@see BranchRepository}. The only place Branch persistence
 * details live; Application Actions/Queries depend on the contract, not this.
 */
final class EloquentBranchRepository implements BranchRepository
{
    public function findAll(): Collection
    {
        return Branch::query()->orderBy('name')->get();
    }

    public function findById(int|string $id): ?Branch
    {
        return Branch::query()->find($id);
    }

    public function findByCode(string $code): ?Branch
    {
        return Branch::query()->where('code', $code)->first();
    }

    public function active(): Collection
    {
        // The public, storefront-selectable set is never branch-scoped: it must
        // list every active branch regardless of any admin guard state on the
        // request (e.g. an admin browsing the storefront in the same session).
        return Branch::query()
            ->withoutGlobalScope(BranchScope::class)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $attributes): Branch
    {
        return Branch::query()->create($attributes);
    }

    public function update(int|string $id, array $attributes): Branch
    {
        $branch = Branch::query()->findOrFail($id);
        $branch->update($attributes);

        return $branch->refresh();
    }

    public function delete(int|string $id): bool
    {
        return (bool) Branch::query()->whereKey($id)->delete();
    }
}
