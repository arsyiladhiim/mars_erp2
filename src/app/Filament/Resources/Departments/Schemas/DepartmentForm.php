<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('branch_id')->relationship('branch', 'name')->searchable(),
            Select::make('parent_id')->relationship('parent', 'name')->searchable()->label('Parent Department'),
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(150),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
