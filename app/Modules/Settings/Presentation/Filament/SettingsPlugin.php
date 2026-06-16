<?php

declare(strict_types=1);

namespace App\Modules\Settings\Presentation\Filament;

use App\Modules\Settings\Presentation\Filament\Pages\GlobalSettings;
use App\Modules\Settings\Presentation\Filament\Pages\PaymentSettings;
use App\Modules\Settings\Presentation\Filament\Pages\SeoSettings;
use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Registers the Settings module's Filament pages with the admin panel.
 *
 * This is the convention every module follows to contribute admin UI: expose a
 * Filament Plugin from `Presentation/Filament` and add it via `->plugin(...)`
 * in AdminPanelProvider, keeping the panel decoupled from module internals.
 */
final class SettingsPlugin implements Plugin
{
    public static function make(): self
    {
        return new self;
    }

    public function getId(): string
    {
        return 'settings';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            GlobalSettings::class,
            SeoSettings::class,
            PaymentSettings::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
