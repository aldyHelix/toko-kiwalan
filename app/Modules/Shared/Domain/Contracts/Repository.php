<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Base persistence contract every module repository follows. Concrete
 * repositories live in `Infrastructure/Repositories`, are bound to this (or a
 * narrower module-specific) interface in the module service provider, and may
 * add domain finders beyond this CRUD surface (architecture §8 — DIP/ISP).
 *
 * @template TModel of Model
 */
interface Repository
{
    /**
     * @return Collection<int, TModel>
     */
    public function findAll(): Collection;

    /**
     * @return TModel|null
     */
    public function findById(int|string $id): ?Model;

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function update(int|string $id, array $attributes): Model;

    public function delete(int|string $id): bool;
}
