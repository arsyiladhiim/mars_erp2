<?php

namespace App\Filament\Resources\StockOpnames\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Count Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    TextInput::make('number')->required()->maxLength(50),
                    Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->required(),
                    DatePicker::make('count_date')->required(),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'counting' => 'Counting', 'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
                ]),
            Section::make('Count Sheet')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(4)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('system_quantity')->numeric()->disabled()->dehydrated(),
                            TextInput::make('physical_quantity')->numeric()->required(),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
