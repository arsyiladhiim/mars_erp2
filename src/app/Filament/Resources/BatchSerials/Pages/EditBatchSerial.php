<?php

namespace App\Filament\Resources\BatchSerials\Pages;

use App\Filament\Resources\BatchSerials\BatchSerialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBatchSerial extends EditRecord
{
    protected static string $resource = BatchSerialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
