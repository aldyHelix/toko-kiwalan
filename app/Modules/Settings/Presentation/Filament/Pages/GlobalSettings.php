<?php

declare(strict_types=1);

namespace App\Modules\Settings\Presentation\Filament\Pages;

use App\Modules\Settings\Domain\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Admin page for store-wide {@see GeneralSettings}. The plugin's SettingsPage
 * fills the form from the typed settings object and persists it on save.
 */
class GlobalSettings extends SettingsPage
{
    protected static string $settings = GeneralSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    public static function getNavigationLabel(): string
    {
        return 'Umum';
    }

    public function getTitle(): string
    {
        return 'Pengaturan Umum';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('store_name')
                    ->label('Nama Toko')
                    ->required()
                    ->maxLength(255),
                TextInput::make('store_email')
                    ->label('Email Kontak')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('store_phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(50),
                TextInput::make('currency')
                    ->label('Mata Uang')
                    ->required()
                    ->maxLength(3)
                    ->helperText('Kode ISO 4217, mis. IDR.'),
                TextInput::make('tax_percent')
                    ->label('Pajak (%)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100),
            ]);
    }
}
