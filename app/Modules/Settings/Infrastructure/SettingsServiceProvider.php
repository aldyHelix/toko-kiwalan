<?php

declare(strict_types=1);

namespace App\Modules\Settings\Infrastructure;

use App\Modules\Shared\Infrastructure\ModuleServiceProvider;

/**
 * Service provider for the Settings module.
 *
 * The typed settings groups and their migration path are wired through
 * `config/settings.php` (spatie/laravel-settings); this provider exists to keep
 * the module self-contained and to host any future bindings. Filament admin
 * pages are registered via {@see Presentation\Filament\SettingsPlugin} in
 * AdminPanelProvider, not here.
 */
class SettingsServiceProvider extends ModuleServiceProvider
{
    protected function modulePath(): string
    {
        // app/Modules/Settings/Infrastructure → app/Modules/Settings
        return dirname(__DIR__);
    }
}
