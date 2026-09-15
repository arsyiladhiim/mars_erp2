<?php

namespace App\Filament\Resources\AccountingPeriods\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountingPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('fiscal_year_id')->relationship('fiscalYear', 'code')->searchable()->required(),
            TextInput::make('name')->required()->maxLength(50),
            TextInput::make('period_number')->numeric()->minValue(1)->maxValue(12)->required(),
            DatePicker::make('start_date')->required(),
            DatePicker::make('end_date')->required(),
            Select::make('status')->options([
                'open' => 'Open',
                'processing' => 'Processing',
                'review' => 'Review',
                'closed' => 'Closed',
                'locked' => 'Locked',
            ])->default('open')->required()
                ->helperText('Closed/locked periods cannot receive new postings (PRD §11.7, Key Rule #5).'),
        ])->columns(2);
    }
}
