<?php

namespace App\Filament\Resources\BatchSerials\Pages;

use App\Filament\Resources\BatchSerials\BatchSerialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBatchSerials extends ListRecords
{
    protected static string $resource = BatchSerialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
