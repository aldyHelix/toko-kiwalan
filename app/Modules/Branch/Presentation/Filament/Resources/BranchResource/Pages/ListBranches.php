<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages;

use App\Modules\Branch\Presentation\Filament\Resources\BranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    /**
     * @return array<int, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
