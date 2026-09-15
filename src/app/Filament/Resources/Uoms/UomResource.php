<?php

namespace App\Filament\Resources\Uoms;

use App\Filament\Resources\Uoms\Pages\CreateUom;
use App\Filament\Resources\Uoms\Pages\EditUom;
use App\Filament\Resources\Uoms\Pages\ListUoms;
use App\Filament\Resources\Uoms\Schemas\UomForm;
use App\Filament\Resources\Uoms\Tables\UomsTable;
use App\Models\Master\Uom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UomResource extends Resource
{
    protected static ?string $model = Uom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return UomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UomsTable::configure($table);
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
            'index' => ListUoms::route('/'),
            'create' => CreateUom::route('/create'),
            'edit' => EditUom::route('/{record}/edit'),
        ];
    }
}
