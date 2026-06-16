<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // General
        $this->migrator->add('general.store_name', 'Toko Kiwalan');
        $this->migrator->add('general.store_email', 'hello@example.com');
        $this->migrator->add('general.store_phone', '');
        $this->migrator->add('general.currency', 'IDR');
        $this->migrator->add('general.tax_percent', 11);

        // SEO
        $this->migrator->add('seo.meta_title', 'Toko Kiwalan');
        $this->migrator->add('seo.meta_description', 'Boilerplate e-commerce dengan 3D product viewer + AR.');
        $this->migrator->add('seo.og_image', null);
        $this->migrator->add('seo.robots', 'index,follow');

        // Theme
        $this->migrator->add('theme.active_bundle', null);
        $this->migrator->add('theme.primary_color', '#f59e0b');
        $this->migrator->add('theme.font_family', 'Inter');

        // Payment (non-secret config only)
        $this->migrator->add('payment.default_gateway', 'midtrans');
        $this->migrator->add('payment.enabled_methods', ['snap']);
        $this->migrator->add('payment.midtrans_is_production', false);
        $this->migrator->add('payment.midtrans_merchant_id', null);
    }
};
