<?php

namespace App\Filament\Resources\FiscalYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FiscalYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            TextInput::make('code')->required()->maxLength(20),
            DatePicker::make('start_date')->required(),
            DatePicker::make('end_date')->required(),
            Select::make('status')->options([
                'open' => 'Open',
                'processing' => 'Processing',
                'closed' => 'Closed',
                'locked' => 'Locked',
            ])->default('open')->required(),
        ])->columns(2);
    }
}
