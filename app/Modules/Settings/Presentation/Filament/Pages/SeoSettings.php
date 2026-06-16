<?php

declare(strict_types=1);

namespace App\Modules\Settings\Presentation\Filament\Pages;

use App\Modules\Settings\Domain\Settings\SeoSettings as SeoSettingsStore;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Admin page for global {@see SeoSettingsStore} defaults.
 */
class SeoSettings extends SettingsPage
{
    protected static string $settings = SeoSettingsStore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    public static function getNavigationLabel(): string
    {
        return 'SEO';
    }

    public function getTitle(): string
    {
        return 'Pengaturan SEO';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('meta_title')
                    ->label('Judul Meta Default')
                    ->required()
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->label('Deskripsi Meta Default')
                    ->required()
                    ->rows(3)
                    ->maxLength(500),
                TextInput::make('og_image')
                    ->label('OG Image URL')
                    ->url()
                    ->maxLength(2048),
                Select::make('robots')
                    ->label('Robots')
                    ->required()
                    ->options([
                        'index,follow' => 'index, follow',
                        'noindex,follow' => 'noindex, follow',
                        'index,nofollow' => 'index, nofollow',
                        'noindex,nofollow' => 'noindex, nofollow',
                    ]),
            ]);
    }
}
