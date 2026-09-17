<?php

namespace App\Filament\Resources\GlAccountMappings;

use App\Filament\Resources\GlAccountMappings\Pages\EditGlAccountMapping;
use App\Filament\Resources\GlAccountMappings\Pages\ListGlAccountMappings;
use App\Filament\Resources\GlAccountMappings\Schemas\GlAccountMappingForm;
use App\Filament\Resources\GlAccountMappings\Tables\GlAccountMappingsTable;
use App\Models\Finance\GlAccountMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * G/L Account Determination (PRD §11.2 postings, architecture decision to
 * make this admin-configurable rather than hardcoded). Fixed set of rows
 * (see GlAccountMapping::keys(), seeded once per company) — only editing
 * which account each key maps to is allowed, no create/delete.
 */
class GlAccountMappingResource extends Resource
{
    protected static ?string $model = GlAccountMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'G/L Account Determination';

    protected static ?string $modelLabel = 'G/L Account Mapping';

    protected static ?string $pluralModelLabel = 'G/L Account Determination';

    public static function form(Schema $schema): Schema
    {
        return GlAccountMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GlAccountMappingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGlAccountMappings::route('/'),
            'edit' => EditGlAccountMapping::route('/{record}/edit'),
        ];
    }
}
