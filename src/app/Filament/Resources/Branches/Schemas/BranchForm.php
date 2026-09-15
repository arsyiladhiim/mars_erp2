<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Core\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->relationship('company', 'name')
                ->options(fn () => Company::query()->pluck('name', 'id'))
                ->searchable()->required(),
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(150),
            TextInput::make('phone')->tel()->maxLength(30),
            Textarea::make('address')->rows(3)->columnSpanFull(),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
