<?php

namespace App\Filament\Resources\NumberSeries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NumberSeriesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('branch_id')->relationship('branch', 'name')->searchable(),
            TextInput::make('document_type')->required()->maxLength(50)
                ->helperText('e.g. purchase_request, purchase_order, goods_receipt, sales_order, invoice'),
            TextInput::make('prefix')->required()->maxLength(10),
            TextInput::make('format')->default('{PREFIX}-{YEAR}-{NUMBER}')->required(),
            TextInput::make('next_number')->numeric()->default(1)->required(),
            TextInput::make('padding')->numeric()->default(6)->required(),
            Toggle::make('reset_yearly')->default(true),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
