<?php

namespace App\Filament\Resources\BatchSerials;

use App\Filament\Resources\BatchSerials\Pages\CreateBatchSerial;
use App\Filament\Resources\BatchSerials\Pages\EditBatchSerial;
use App\Filament\Resources\BatchSerials\Pages\ListBatchSerials;
use App\Filament\Resources\BatchSerials\Schemas\BatchSerialForm;
use App\Filament\Resources\BatchSerials\Tables\BatchSerialsTable;
use App\Models\Inventory\BatchSerial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BatchSerialResource extends Resource
{
    protected static ?string $model = BatchSerial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return BatchSerialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatchSerialsTable::configure($table);
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
            'index' => ListBatchSerials::route('/'),
            'create' => CreateBatchSerial::route('/create'),
            'edit' => EditBatchSerial::route('/{record}/edit'),
        ];
    }
}
