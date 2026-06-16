<?php

declare(strict_types=1);

use App\Modules\Settings\Domain\Settings\GeneralSettings;
use App\Modules\Settings\Domain\Settings\PaymentSettings;
use App\Modules\Settings\Domain\Settings\SeoSettings;
use App\Modules\Settings\Domain\Settings\ThemeSettings;
use Illuminate\Support\Facades\DB;

it('exposes the seeded general settings defaults in the domain', function () {
    $settings = app(GeneralSettings::class);

    expect($settings->store_name)->toBe('Toko Kiwalan')
        ->and($settings->currency)->toBe('IDR')
        ->and($settings->tax_percent)->toBe(11);
});

it('resolves every settings group with its seeded defaults', function () {
    expect(app(GeneralSettings::class)::group())->toBe('general')
        ->and(app(SeoSettings::class)::group())->toBe('seo')
        ->and(app(SeoSettings::class)->robots)->toBe('index,follow')
        ->and(app(ThemeSettings::class)::group())->toBe('theme')
        ->and(app(ThemeSettings::class)->primary_color)->toBe('#f59e0b')
        ->and(app(ThemeSettings::class)->active_bundle)->toBeNull()
        ->and(app(PaymentSettings::class)::group())->toBe('payment');
});

it('keeps payment secrets out of settings (env-only)', function () {
    $settings = app(PaymentSettings::class);

    expect($settings->default_gateway)->toBe('midtrans')
        ->and($settings->enabled_methods)->toBe(['snap'])
        ->and($settings->midtrans_is_production)->toBeFalse()
        // No secret/client key properties exist on the settings object.
        ->and(property_exists($settings, 'midtrans_server_key'))->toBeFalse();
});

it('persists and reloads updated settings', function () {
    $settings = app(GeneralSettings::class);
    $settings->store_name = 'Kiwalan Furniture';
    $settings->tax_percent = 12;
    $settings->save();

    // Rebuild the instance so the value is re-read, not the in-memory copy.
    app()->forgetScopedInstances();

    $reloaded = app(GeneralSettings::class);

    expect($reloaded->store_name)->toBe('Kiwalan Furniture')
        ->and($reloaded->tax_percent)->toBe(12);
});

it('caches settings so repeated reads do not hit the database', function () {
    expect(config('settings.cache.enabled'))->toBeTrue();

    // First access loads from the DB and populates the cache.
    app(GeneralSettings::class)->store_name;
    app()->forgetScopedInstances();

    DB::enableQueryLog();
    $name = app(GeneralSettings::class)->store_name;
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($name)->toBe('Toko Kiwalan')
        ->and($queries)->toBeEmpty();
});
