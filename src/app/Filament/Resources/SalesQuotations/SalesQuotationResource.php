<?php

namespace App\Filament\Resources\SalesQuotations;

use App\Filament\Resources\SalesQuotations\Pages\CreateSalesQuotation;
use App\Filament\Resources\SalesQuotations\Pages\EditSalesQuotation;
use App\Filament\Resources\SalesQuotations\Pages\ListSalesQuotations;
use App\Filament\Resources\SalesQuotations\Schemas\SalesQuotationForm;
use App\Filament\Resources\SalesQuotations\Tables\SalesQuotationsTable;
use App\Models\Sales\SalesQuotation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesQuotationResource extends Resource
{
    protected static ?string $model = SalesQuotation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return SalesQuotationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesQuotationsTable::configure($table);
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
            'index' => ListSalesQuotations::route('/'),
            'create' => CreateSalesQuotation::route('/create'),
            'edit' => EditSalesQuotation::route('/{record}/edit'),
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
