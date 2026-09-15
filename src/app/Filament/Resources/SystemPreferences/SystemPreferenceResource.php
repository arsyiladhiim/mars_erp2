<?php

namespace App\Filament\Resources\SystemPreferences;

use App\Filament\Resources\SystemPreferences\Pages\CreateSystemPreference;
use App\Filament\Resources\SystemPreferences\Pages\EditSystemPreference;
use App\Filament\Resources\SystemPreferences\Pages\ListSystemPreferences;
use App\Filament\Resources\SystemPreferences\Schemas\SystemPreferenceForm;
use App\Filament\Resources\SystemPreferences\Tables\SystemPreferencesTable;
use App\Models\Core\SystemPreference;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemPreferenceResource extends Resource
{
    protected static ?string $model = SystemPreference::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return SystemPreferenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemPreferencesTable::configure($table);
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
            'index' => ListSystemPreferences::route('/'),
            'create' => CreateSystemPreference::route('/create'),
            'edit' => EditSystemPreference::route('/{record}/edit'),
        ];
    }
}
