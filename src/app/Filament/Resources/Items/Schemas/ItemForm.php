<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Item Information')
                ->columns(2)
                ->components([
                    TextInput::make('sku')->required()->maxLength(50)->unique(ignoreRecord: true)->label('SKU / Item Code'),
                    TextInput::make('name')->required()->maxLength(150),
                    Select::make('item_category_id')->relationship('category', 'name')->searchable(),
                    TextInput::make('brand')->maxLength(100),
                    Select::make('uom_id')->relationship('uom', 'name')->searchable()->required(),
                    TextInput::make('barcode')->maxLength(50),
                    Select::make('type')->options([
                        'inventory' => 'Inventory Item', 'service' => 'Service', 'non_inventory' => 'Non-Inventory',
                    ])->default('inventory')->required(),
                    Select::make('tax_code_id')->relationship('taxCode', 'name')->searchable(),
                ]),
            Section::make('Costing & Pricing')
                ->columns(3)
                ->components([
                    Select::make('costing_method')->options([
                        'moving_average' => 'Moving Average', 'standard' => 'Standard Cost',
                    ])->default('moving_average')->required(),
                    TextInput::make('standard_cost')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('average_cost')->numeric()->prefix('Rp')->default(0)->disabled()
                        ->helperText('Maintained automatically from the stock ledger.'),
                    TextInput::make('selling_price')->numeric()->prefix('Rp')->default(0),
                ]),
            Section::make('Inventory Control')
                ->columns(3)
                ->components([
                    TextInput::make('minimum_stock')->numeric()->default(0),
                    TextInput::make('maximum_stock')->numeric(),
                    TextInput::make('reorder_point')->numeric()->default(0),
                    Toggle::make('is_batch_tracked'),
                    Toggle::make('is_serial_tracked'),
                    Toggle::make('has_expiry'),
                ]),
            Section::make('Other')
                ->columns(2)
                ->components([
                    FileUpload::make('image_path')->image()->directory('items'),
                    Toggle::make('is_active')->default(true),
                ]),
        ]);
    }
}
