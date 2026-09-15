<?php

namespace App\Filament\Resources\SupplierQuotations;

use App\Filament\Resources\SupplierQuotations\Pages\CreateSupplierQuotation;
use App\Filament\Resources\SupplierQuotations\Pages\EditSupplierQuotation;
use App\Filament\Resources\SupplierQuotations\Pages\ListSupplierQuotations;
use App\Filament\Resources\SupplierQuotations\Schemas\SupplierQuotationForm;
use App\Filament\Resources\SupplierQuotations\Tables\SupplierQuotationsTable;
use App\Models\Procurement\SupplierQuotation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierQuotationResource extends Resource
{
    protected static ?string $model = SupplierQuotation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Purchasing';

    public static function form(Schema $schema): Schema
    {
        return SupplierQuotationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierQuotationsTable::configure($table);
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
            'index' => ListSupplierQuotations::route('/'),
            'create' => CreateSupplierQuotation::route('/create'),
            'edit' => EditSupplierQuotation::route('/{record}/edit'),
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
