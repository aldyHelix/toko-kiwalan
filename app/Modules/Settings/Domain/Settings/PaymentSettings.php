<?php

declare(strict_types=1);

namespace App\Modules\Settings\Domain\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Non-secret payment configuration. Gateway secret/client keys live in `.env`
 * (MIDTRANS_*) and are never stored here — this holds only operator choices
 * such as the active gateway and enabled methods (Payment module, Fase 6).
 */
class PaymentSettings extends Settings
{
    public string $default_gateway;

    /**
     * Enabled payment methods, e.g. ["snap", "va", "qris"].
     *
     * @var array<int, string>
     */
    public array $enabled_methods;

    public bool $midtrans_is_production;

    public ?string $midtrans_merchant_id;

    public static function group(): string
    {
        return 'payment';
    }
}
