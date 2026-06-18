<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages;

use App\Modules\Branch\Application\Actions\CreateBranch as CreateBranchAction;
use App\Modules\Branch\Application\DTO\BranchData;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    /**
     * Delegate persistence to the Application Action so the panel never runs
     * business-logic writes directly (architecture §2).
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateBranchAction::class)->handle(BranchData::from($data));
    }
}
