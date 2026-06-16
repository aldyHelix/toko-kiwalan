<?php

declare(strict_types=1);

namespace App\Modules\Settings\Presentation\Filament\Pages;

use App\Modules\Settings\Domain\Settings\PaymentSettings as PaymentSettingsStore;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Admin page for non-secret {@see PaymentSettingsStore}. Gateway secret keys
 * stay in `.env` (MIDTRANS_*) — this page never collects them.
 */
class PaymentSettings extends SettingsPage
{
    protected static string $settings = PaymentSettingsStore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    public static function getNavigationLabel(): string
    {
        return 'Pembayaran';
    }

    public function getTitle(): string
    {
        return 'Pengaturan Pembayaran';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('default_gateway')
                    ->label('Gateway Default')
                    ->required()
                    ->options([
                        'midtrans' => 'Midtrans',
                    ]),
                Select::make('enabled_methods')
                    ->label('Metode Aktif')
                    ->multiple()
                    ->options([
                        'snap' => 'Snap',
                        'va' => 'Virtual Account',
                        'ewallet' => 'E-Wallet',
                        'qris' => 'QRIS',
                    ]),
                Toggle::make('midtrans_is_production')
                    ->label('Mode Produksi Midtrans')
                    ->helperText('Nonaktif = sandbox.'),
                TextInput::make('midtrans_merchant_id')
                    ->label('Midtrans Merchant ID')
                    ->maxLength(255),
            ]);
    }
}
