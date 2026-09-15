<?php

namespace App\Filament\Resources\AssetCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(150),
            Select::make('depreciation_method')->options([
                'straight_line' => 'Straight Line', 'declining_balance' => 'Declining Balance',
            ])->default('straight_line')->required(),
            TextInput::make('useful_life_months')->numeric()->default(36)->required()->suffix('months'),
        ])->columns(2);
    }
}
