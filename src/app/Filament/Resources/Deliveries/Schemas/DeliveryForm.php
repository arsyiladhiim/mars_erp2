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
                    TextInput::make('number')->maxLength(50)
                        ->disabled(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (string $operation) => $operation !== 'create')
                        ->required(fn (string $operation) => $operation === 'edit')
                        ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
                    DatePicker::make('delivery_date')->required(),
                    TextInput::make('tracking_number')->maxLength(100),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required()->disabled()->dehydrated()
                        ->helperText('Managed via the Post action.'),
                ]),
            Section::make('Items')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(4)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable(),
                            Select::make('sales_order_line_id')->relationship('salesOrderLine', 'id')
                                ->label('Order Line')
                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->item?->name.' ('.$record->quantity.' ordered, '.$record->delivered_quantity.' delivered)')
                                ->searchable(),
                            TextInput::make('quantity')->numeric()->required(),
                            TextInput::make('batch_number'),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
