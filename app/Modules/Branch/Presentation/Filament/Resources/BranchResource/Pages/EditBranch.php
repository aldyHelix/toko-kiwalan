<?php

declare(strict_types=1);

namespace App\Modules\Branch\Presentation\Filament\Resources\BranchResource\Pages;

use App\Modules\Branch\Application\Actions\UpdateBranch as UpdateBranchAction;
use App\Modules\Branch\Application\DTO\BranchData;
use App\Modules\Branch\Domain\Models\Branch;
use App\Modules\Branch\Presentation\Filament\Resources\BranchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    /**
     * @return array<int, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Delegate persistence to the Application Action (architecture §2).
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Branch $record */
        return app(UpdateBranchAction::class)->handle($record, BranchData::from($data));
    }
}
