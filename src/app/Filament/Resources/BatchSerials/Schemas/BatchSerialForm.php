<?php

namespace App\Filament\Resources\BatchSerials\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchSerialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('item_id')->relationship('item', 'name')->searchable()->required(),
            Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->required(),
            Select::make('type')->options(['batch' => 'Batch', 'serial' => 'Serial'])->default('batch')->required(),
            TextInput::make('batch_number'),
            TextInput::make('serial_number'),
            TextInput::make('quantity')->numeric()->default(0)->required(),
            DatePicker::make('manufacture_date'),
            DatePicker::make('expiry_date'),
            Select::make('status')->options([
                'available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold',
                'expired' => 'Expired', 'quarantine' => 'Quarantine',
            ])->default('available')->required(),
        ])->columns(2);
    }
}
