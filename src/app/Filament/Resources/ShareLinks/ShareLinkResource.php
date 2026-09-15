<?php

namespace App\Filament\Resources\ShareLinks;

use App\Filament\Resources\ShareLinks\Pages\CreateShareLink;
use App\Filament\Resources\ShareLinks\Pages\EditShareLink;
use App\Filament\Resources\ShareLinks\Pages\ListShareLinks;
use App\Filament\Resources\ShareLinks\Schemas\ShareLinkForm;
use App\Filament\Resources\ShareLinks\Tables\ShareLinksTable;
use App\Models\Document\ShareLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShareLinkResource extends Resource
{
    protected static ?string $model = ShareLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|\UnitEnum|null $navigationGroup = 'Productivity';

    public static function form(Schema $schema): Schema
    {
        return ShareLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShareLinksTable::configure($table);
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
            'index' => ListShareLinks::route('/'),
            'create' => CreateShareLink::route('/create'),
            'edit' => EditShareLink::route('/{record}/edit'),
        ];
    }
}
