<?php

namespace App\Filament\Resources\Deliveries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Delivery Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('sales_order_id')->relationship('salesOrder', 'number')->searchable(),
                    Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()
                        ->required()->label('Customer'),
                    Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->required(),
                    TextInput::make('number')->required()->maxLength(50),
                    DatePicker::make('delivery_date')->required(),
                    TextInput::make('tracking_number')->maxLength(100),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
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
