<?php

namespace App\Filament\Resources\TaxCodes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('rate')->numeric()->suffix('%')->required()->helperText('e.g. 11 for PPN 11%'),
            Select::make('type')->options([
                'output' => 'Output (Sales)', 'input' => 'Input (Purchase)', 'both' => 'Both',
            ])->default('both')->required(),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
