<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Branch create/edit form. `code` is validated unique (ignoring the current
 * record on edit), satisfying Fase 2 AC #1.
 */
class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Cabang')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Kode')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->helperText('Kode unik cabang, mis. JKT-001.'),
                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->maxLength(500),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
