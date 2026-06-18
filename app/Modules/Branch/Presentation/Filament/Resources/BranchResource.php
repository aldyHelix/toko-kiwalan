<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Filament\Resources;

use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Infrastructure\Policies\BranchPolicy;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages\CreateBranch;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages\EditBranch;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages\ListBranches;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Schemas\BranchForm;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Tables\BranchesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Admin CRUD for store branches. Access is gated by {@see BranchPolicy}
 * (MANAGE_BRANCHES + branch scope); record creation/update delegate to the
 * Application Actions so business rules are never duplicated in the panel.
 */
class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen Toko';

    protected static ?string $modelLabel = 'Cabang';

    protected static ?string $pluralModelLabel = 'Cabang';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }
}
