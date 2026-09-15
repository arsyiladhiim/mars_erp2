<?php

namespace App\Filament\Resources\GoodsReceipts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GoodsReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Receipt Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('purchase_order_id')->relationship('purchaseOrder', 'number')->searchable(),
                    Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()
                        ->label('Supplier'),
                    TextInput::make('number')->required()->maxLength(50),
                    Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->required(),
                    DatePicker::make('receipt_date')->required(),
                    TextInput::make('supplier_reference')->label('DO / Surat Jalan No.')->maxLength(100),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
                ]),
            Section::make('Received Items')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(6)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('quantity')->numeric()->required(),
                            Select::make('uom_id')->relationship('uom', 'name')->searchable(),
                            TextInput::make('unit_cost')->numeric()->prefix('Rp'),
                            TextInput::make('batch_number'),
                            DatePicker::make('expiry_date'),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
