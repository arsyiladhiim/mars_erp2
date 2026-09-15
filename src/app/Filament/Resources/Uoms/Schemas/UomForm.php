<?php

namespace App\Filament\Resources\Uoms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(10)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(50),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
