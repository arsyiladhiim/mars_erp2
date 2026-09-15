<?php

namespace App\Filament\Resources\ProfitCenters\Pages;

use App\Filament\Resources\ProfitCenters\ProfitCenterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfitCenters extends ListRecords
{
    protected static string $resource = ProfitCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
