<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure;

use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for every bounded-context module.
 *
 * It wires the conventions described in docs/planning/01-architecture.md §7:
 * each module auto-loads its own migrations from `Database/Migrations` and binds
 * its Domain contracts to Infrastructure implementations. Concrete modules extend
 * this, return their root path from {@see modulePath()}, and override the hooks.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Absolute path to the module root — the directory that contains
     * Domain/, Application/, Infrastructure/ and Presentation/.
     */
    abstract protected function modulePath(): string;

    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $migrations = $this->modulePath().'/Database/Migrations';

        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }

        $this->bootModule();
    }

    /**
     * Bind module Domain contracts to their Infrastructure implementations.
     * Override in the concrete provider.
     */
    protected function registerBindings(): void {}

    /**
     * Register routes, policies, views, scheduled tasks, etc.
     * Override in the concrete provider.
     */
    protected function bootModule(): void {}
}
