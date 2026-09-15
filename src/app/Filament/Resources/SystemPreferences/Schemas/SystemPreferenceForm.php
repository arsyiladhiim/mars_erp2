<?php

namespace App\Filament\Resources\SystemPreferences\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SystemPreferenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()
                ->helperText('Leave empty for a global/system-wide preference.'),
            TextInput::make('key')->required()->maxLength(150)->unique(ignoreRecord: true),
            Textarea::make('value')->rows(3),
            Select::make('type')->options([
                'string' => 'String', 'bool' => 'Boolean', 'int' => 'Integer', 'json' => 'JSON',
            ])->default('string')->required(),
            TextInput::make('group')->default('general')->maxLength(50),
        ])->columns(2);
    }
}
