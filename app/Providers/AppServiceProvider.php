<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Admin;
use App\Support\Rbac;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // super-admin bypasses every authorization check.
        Gate::before(function (Authenticatable $user, string $ability): ?bool {
            if ($user instanceof Admin && $user->hasRole(Rbac::SUPER_ADMIN)) {
                return true;
            }

            return null;
        });
    }
}
