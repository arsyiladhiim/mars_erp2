<?php

namespace App\Filament\Resources\ProfitCenters;

use App\Filament\Resources\ProfitCenters\Pages\CreateProfitCenter;
use App\Filament\Resources\ProfitCenters\Pages\EditProfitCenter;
use App\Filament\Resources\ProfitCenters\Pages\ListProfitCenters;
use App\Filament\Resources\ProfitCenters\Schemas\ProfitCenterForm;
use App\Filament\Resources\ProfitCenters\Tables\ProfitCentersTable;
use App\Models\Core\ProfitCenter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfitCenterResource extends Resource
{
    protected static ?string $model = ProfitCenter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return ProfitCenterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfitCentersTable::configure($table);
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
            'index' => ListProfitCenters::route('/'),
            'create' => CreateProfitCenter::route('/create'),
            'edit' => EditProfitCenter::route('/{record}/edit'),
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
