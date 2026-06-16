<?php

declare(strict_types=1);

namespace App\Modules\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Runtime theme/design tokens for the storefront. The full theme-bundle
 * export/import lands in Fase 7; these are the live design tokens it drives.
 */
class ThemeSettings extends Settings
{
    /** Name of the active theme bundle, or null for the built-in default. */
    public ?string $active_bundle;

    public string $primary_color;

    public string $font_family;

    public static function group(): string
    {
        return 'theme';
    }
}
