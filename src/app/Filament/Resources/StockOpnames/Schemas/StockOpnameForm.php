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
                    TextInput::make('number')->maxLength(50)
                        ->disabled(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (string $operation) => $operation !== 'create')
                        ->required(fn (string $operation) => $operation === 'edit')
                        ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
                    Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->required(),
                    DatePicker::make('count_date')->required(),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'counting' => 'Counting', 'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required()->disabled()->dehydrated()
                        ->helperText('Managed via the Post action.'),
                ]),
            Section::make('Count Sheet')
                ->description('Leave the system quantity blank — it, and the variance, are reconciled against the live stock balance when you post.')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(4)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('system_quantity')->numeric()->disabled()->dehydrated()
                                ->helperText('Computed on posting.'),
                            TextInput::make('physical_quantity')->numeric()->required(),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
