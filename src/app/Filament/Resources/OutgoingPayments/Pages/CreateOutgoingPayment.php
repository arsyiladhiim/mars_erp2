<?php

namespace App\Filament\Resources\OutgoingPayments\Pages;

use App\Filament\Resources\OutgoingPayments\OutgoingPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOutgoingPayment extends CreateRecord
{
    protected static string $resource = OutgoingPaymentResource::class;
}
