<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Adjustment Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    TextInput::make('number')->required()->maxLength(50),
                    Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->required(),
                    DatePicker::make('adjustment_date')->required(),
                    Select::make('reason')->options([
                        'damage' => 'Damage', 'loss' => 'Loss', 'found' => 'Found', 'correction' => 'Correction',
                        'other' => 'Other',
                    ])->default('correction')->required(),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                        'posted' => 'Posted', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
                    Textarea::make('notes')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Items')
                ->description('Quantity may be positive (increase) or negative (decrease).')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(3)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable(),
                            TextInput::make('quantity')->numeric()->required(),
                            TextInput::make('unit_cost')->numeric()->prefix('Rp'),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
