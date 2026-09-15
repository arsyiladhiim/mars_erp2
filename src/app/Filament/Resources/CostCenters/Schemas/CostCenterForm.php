<?php

namespace App\Filament\Resources\CostCenters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CostCenterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('department_id')->relationship('department', 'name')->searchable(),
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(150),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
