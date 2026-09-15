<?php

namespace App\Filament\Resources\CashBankTransactions;

use App\Filament\Resources\CashBankTransactions\Pages\CreateCashBankTransaction;
use App\Filament\Resources\CashBankTransactions\Pages\EditCashBankTransaction;
use App\Filament\Resources\CashBankTransactions\Pages\ListCashBankTransactions;
use App\Filament\Resources\CashBankTransactions\Schemas\CashBankTransactionForm;
use App\Filament\Resources\CashBankTransactions\Tables\CashBankTransactionsTable;
use App\Models\Finance\CashBankTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashBankTransactionResource extends Resource
{
    protected static ?string $model = CashBankTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return CashBankTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashBankTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashBankTransactions::route('/'),
            'create' => CreateCashBankTransaction::route('/create'),
            'edit' => EditCashBankTransaction::route('/{record}/edit'),
        ];
    }
}
