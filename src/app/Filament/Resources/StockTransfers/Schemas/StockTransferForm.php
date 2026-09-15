<?php

namespace App\Filament\Resources\StockTransfers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Transfer Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    TextInput::make('number')->required()->maxLength(50),
                    Select::make('from_warehouse_id')->relationship('fromWarehouse', 'name')->searchable()
                        ->required(),
                    Select::make('to_warehouse_id')->relationship('toWarehouse', 'name')->searchable()->required(),
                    DatePicker::make('transfer_date')->required(),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                        'in_transit' => 'In Transit', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
                    Textarea::make('reason')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Items')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(3)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable(),
                            TextInput::make('quantity')->numeric()->required(),
                            TextInput::make('batch_number'),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
