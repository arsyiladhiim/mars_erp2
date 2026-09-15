<?php

namespace App\Filament\Resources\ItAssets;

use App\Filament\Resources\ItAssets\Pages\CreateItAsset;
use App\Filament\Resources\ItAssets\Pages\EditItAsset;
use App\Filament\Resources\ItAssets\Pages\ListItAssets;
use App\Filament\Resources\ItAssets\Schemas\ItAssetForm;
use App\Filament\Resources\ItAssets\Tables\ItAssetsTable;
use App\Models\Asset\ItAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItAssetResource extends Resource
{
    protected static ?string $model = ItAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static string|\UnitEnum|null $navigationGroup = 'Asset';

    public static function form(Schema $schema): Schema
    {
        return ItAssetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItAssetsTable::configure($table);
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
            'index' => ListItAssets::route('/'),
            'create' => CreateItAsset::route('/create'),
            'edit' => EditItAsset::route('/{record}/edit'),
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
