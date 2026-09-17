<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Journal Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('accounting_period_id')->relationship('accountingPeriod', 'name')->searchable()
                        ->required(),
                    TextInput::make('number')->maxLength(50)
                        ->disabled(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (string $operation) => $operation !== 'create')
                        ->required(fn (string $operation) => $operation === 'edit')
                        ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
                    DatePicker::make('entry_date')->required(),
                    Select::make('source_type')->options([
                        'manual' => 'Manual', 'goods_receipt' => 'Goods Receipt',
                        'supplier_invoice' => 'Supplier Invoice', 'customer_invoice' => 'Customer Invoice',
                        'delivery' => 'Delivery', 'incoming_payment' => 'Incoming Payment',
                        'outgoing_payment' => 'Outgoing Payment', 'stock_adjustment' => 'Stock Adjustment',
                        'depreciation' => 'Depreciation', 'opening_balance' => 'Opening Balance',
                    ])->default('manual')->required(),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'posted' => 'Posted', 'reversed' => 'Reversed',
                    ])->default('draft')->required()->disabled()->dehydrated()
                        ->helperText('Managed via the Post action.'),
                    Textarea::make('memo')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Journal Lines')
                ->description('Total debit must equal total credit before posting (PRD §11.2, Key Rule #2).')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(4)
                        ->schema([
                            Select::make('chart_of_account_id')->relationship('chartOfAccount', 'name')
                                ->searchable()->required()->columnSpan(2),
                            Select::make('cost_center_id')->relationship('costCenter', 'name')->searchable(),
                            TextInput::make('description'),
                            TextInput::make('debit')->numeric()->default(0)->prefix('Rp'),
                            TextInput::make('credit')->numeric()->default(0)->prefix('Rp'),
                        ])
                        ->defaultItems(2)
                        ->addActionLabel('Add Line'),
                ]),
        ]);
    }
}
