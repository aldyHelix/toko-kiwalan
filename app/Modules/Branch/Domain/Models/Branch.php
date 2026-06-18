<?php

declare(strict_types=1);

namespace App\Modules\Branch\Domain\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A physical store location — "one store, many branches" (architecture §3).
 *
 * Acts as the Branch bounded-context aggregate root: per-branch stock and
 * pricing (Catalog, Fase 3) and order routing (Ordering, Fase 5) hang off it.
 * Branch-scoping (the BranchScope global scope) is attached from the module
 * service provider, so this Domain model imports nothing from Infrastructure.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $address
 * @property bool $is_active
 */
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active',
    ];

    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
