<?php

namespace App\Filament\Resources\StockLedgerEntries\Pages;

use App\Filament\Resources\StockLedgerEntries\StockLedgerEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListStockLedgerEntries extends ListRecords
{
    protected static string $resource = StockLedgerEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
