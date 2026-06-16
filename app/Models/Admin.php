<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Rbac;
use Database\Factories\AdminFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Staff account for the Filament admin panel.
 *
 * Kept separate from the customer {@see User} model so storefront and admin
 * authentication use distinct guards and credential stores (architecture §4).
 * RBAC roles/permissions are scoped to the `admin` guard (see {@see Rbac}).
 */
class Admin extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    /**
     * Guard whose roles/permissions apply to this model (spatie/laravel-permission).
     */
    protected string $guard_name = Rbac::GUARD;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Gate panel access: only admins holding one of the RBAC roles may enter.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(Rbac::ROLES);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
