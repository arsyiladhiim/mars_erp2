<?php

namespace App\Filament\Resources\OutgoingPayments\Pages;

use App\Filament\Resources\OutgoingPayments\OutgoingPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOutgoingPayment extends EditRecord
{
    protected static string $resource = OutgoingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
