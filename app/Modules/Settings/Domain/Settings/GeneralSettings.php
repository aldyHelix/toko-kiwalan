<?php

declare(strict_types=1);

namespace App\Modules\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Store-wide general settings (name, contact, currency, tax). Typed, persisted
 * and cached via spatie/laravel-settings; read from both the Filament admin
 * page and the domain/storefront. Secrets never live here (see .env).
 */
class GeneralSettings extends Settings
{
    public string $store_name;

    public string $store_email;

    public string $store_phone;

    public string $currency;

    /** Tax rate as a whole percentage, e.g. 11 for 11% PPN. */
    public int $tax_percent;

    public static function group(): string
    {
        return 'general';
    }
}
