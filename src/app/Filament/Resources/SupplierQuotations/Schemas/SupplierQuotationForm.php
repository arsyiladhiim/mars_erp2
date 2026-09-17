<?php

namespace App\Filament\Resources\SupplierQuotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierQuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quotation Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()
                        ->required()->label('Supplier'),
                    TextInput::make('number')->maxLength(50)
                        ->disabled(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (string $operation) => $operation !== 'create')
                        ->required(fn (string $operation) => $operation === 'edit')
                        ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
                    Select::make('rfq_id')->relationship('rfq', 'number')->searchable(),
                    DatePicker::make('validity_date'),
                    TextInput::make('lead_time_days')->numeric()->suffix('days'),
                    TextInput::make('payment_term_days')->numeric()->suffix('days'),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'received' => 'Received', 'selected' => 'Selected',
                        'rejected' => 'Rejected',
                    ])->default('draft')->required(),
                ]),
            Section::make('Lines')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(5)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('quantity')->numeric()->required(),
                            Select::make('uom_id')->relationship('uom', 'name')->searchable(),
                            TextInput::make('unit_price')->numeric()->prefix('Rp')->required(),
                            TextInput::make('discount_percent')->numeric()->suffix('%'),
                            Select::make('tax_code_id')->relationship('taxCode', 'name')->searchable(),
                        ])
                        ->addActionLabel('Add Line'),
                ]),
        ]);
    }
}
