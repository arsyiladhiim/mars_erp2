<?php

namespace App\Filament\Resources\SupplierInvoices;

use App\Filament\Resources\SupplierInvoices\Pages\CreateSupplierInvoice;
use App\Filament\Resources\SupplierInvoices\Pages\EditSupplierInvoice;
use App\Filament\Resources\SupplierInvoices\Pages\ListSupplierInvoices;
use App\Filament\Resources\SupplierInvoices\Schemas\SupplierInvoiceForm;
use App\Filament\Resources\SupplierInvoices\Tables\SupplierInvoicesTable;
use App\Models\Finance\SupplierInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierInvoiceResource extends Resource
{
    protected static ?string $model = SupplierInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return SupplierInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierInvoicesTable::configure($table);
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
            'index' => ListSupplierInvoices::route('/'),
            'create' => CreateSupplierInvoice::route('/create'),
            'edit' => EditSupplierInvoice::route('/{record}/edit'),
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
