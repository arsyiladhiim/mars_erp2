<?php

namespace App\Filament\Resources\ItemCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('parent_id')->relationship('parent', 'name')->searchable()->label('Parent Category'),
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(100),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
