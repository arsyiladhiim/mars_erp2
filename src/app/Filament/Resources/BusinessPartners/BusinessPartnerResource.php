<?php

namespace App\Filament\Resources\BusinessPartners;

use App\Filament\Resources\BusinessPartners\Pages\CreateBusinessPartner;
use App\Filament\Resources\BusinessPartners\Pages\EditBusinessPartner;
use App\Filament\Resources\BusinessPartners\Pages\ListBusinessPartners;
use App\Filament\Resources\BusinessPartners\Schemas\BusinessPartnerForm;
use App\Filament\Resources\BusinessPartners\Tables\BusinessPartnersTable;
use App\Models\Master\BusinessPartner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessPartnerResource extends Resource
{
    protected static ?string $model = BusinessPartner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return BusinessPartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessPartnersTable::configure($table);
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
            'index' => ListBusinessPartners::route('/'),
            'create' => CreateBusinessPartner::route('/create'),
            'edit' => EditBusinessPartner::route('/{record}/edit'),
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
