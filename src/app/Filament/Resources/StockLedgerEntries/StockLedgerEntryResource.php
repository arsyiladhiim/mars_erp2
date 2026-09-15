<?php

namespace App\Filament\Resources\StockLedgerEntries;

use App\Filament\Resources\StockLedgerEntries\Pages\ListStockLedgerEntries;
use App\Filament\Resources\StockLedgerEntries\Tables\StockLedgerEntriesTable;
use App\Models\Inventory\StockLedgerEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Immutable movement ledger — the single source of truth for stock quantity (PRD §9, Key Rule #10).
 * View-only: entries are generated only by posting transactions (GR, Delivery, Transfer, Adjustment, Opname).
 */
class StockLedgerEntryResource extends Resource
{
    protected static ?string $model = StockLedgerEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    public static function table(Table $table): Table
    {
        return StockLedgerEntriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockLedgerEntries::route('/'),
        ];
    }
}
