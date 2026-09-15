<?php

namespace App\Filament\Resources\CustomerInvoices;

use App\Filament\Resources\CustomerInvoices\Pages\CreateCustomerInvoice;
use App\Filament\Resources\CustomerInvoices\Pages\EditCustomerInvoice;
use App\Filament\Resources\CustomerInvoices\Pages\ListCustomerInvoices;
use App\Filament\Resources\CustomerInvoices\Schemas\CustomerInvoiceForm;
use App\Filament\Resources\CustomerInvoices\Tables\CustomerInvoicesTable;
use App\Models\Sales\CustomerInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerInvoiceResource extends Resource
{
    protected static ?string $model = CustomerInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return CustomerInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerInvoicesTable::configure($table);
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
            'index' => ListCustomerInvoices::route('/'),
            'create' => CreateCustomerInvoice::route('/create'),
            'edit' => EditCustomerInvoice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
