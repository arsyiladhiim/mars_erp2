<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(3)->unique(ignoreRecord: true)->label('ISO Code'),
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('symbol')->maxLength(10),
            TextInput::make('exchange_rate_to_base')->numeric()->default(1)->required(),
            Toggle::make('is_base')->label('Base Currency'),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
