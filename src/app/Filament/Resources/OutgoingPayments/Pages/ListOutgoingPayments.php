<?php

namespace App\Filament\Resources\OutgoingPayments\Pages;

use App\Filament\Resources\OutgoingPayments\OutgoingPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutgoingPayments extends ListRecords
{
    protected static string $resource = OutgoingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
