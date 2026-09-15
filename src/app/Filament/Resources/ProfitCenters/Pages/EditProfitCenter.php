<?php

namespace App\Filament\Resources\ProfitCenters\Pages;

use App\Filament\Resources\ProfitCenters\ProfitCenterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProfitCenter extends EditRecord
{
    protected static string $resource = ProfitCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
