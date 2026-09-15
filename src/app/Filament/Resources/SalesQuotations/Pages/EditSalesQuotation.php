<?php

namespace App\Filament\Resources\SalesQuotations\Pages;

use App\Filament\Resources\SalesQuotations\SalesQuotationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesQuotation extends EditRecord
{
    protected static string $resource = SalesQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
