<?php

namespace App\Filament\Resources\IncomingPayments\Pages;

use App\Filament\Resources\IncomingPayments\IncomingPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIncomingPayments extends ListRecords
{
    protected static string $resource = IncomingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
