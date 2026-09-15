<?php

namespace App\Filament\Resources\IncomingPayments\Pages;

use App\Filament\Resources\IncomingPayments\IncomingPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIncomingPayment extends CreateRecord
{
    protected static string $resource = IncomingPaymentResource::class;
}
