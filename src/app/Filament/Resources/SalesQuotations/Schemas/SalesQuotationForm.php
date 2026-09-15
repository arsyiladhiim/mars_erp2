<?php

namespace App\Filament\Resources\SalesQuotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesQuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quotation Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('branch_id')->relationship('branch', 'name')->searchable(),
                    Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()
                        ->required()->label('Customer'),
                    TextInput::make('number')->required()->maxLength(50),
                    DatePicker::make('quotation_date')->required(),
                    DatePicker::make('validity_date'),
                    TextInput::make('currency')->default('IDR')->maxLength(3),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted',
                        'declined' => 'Declined', 'expired' => 'Expired', 'converted' => 'Converted',
                    ])->default('draft')->required(),
                    Textarea::make('terms')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Lines')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(6)
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
            Section::make('Totals')
                ->columns(3)
                ->components([
                    TextInput::make('subtotal')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('tax_total')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('grand_total')->numeric()->prefix('Rp')->default(0),
                ]),
        ]);
    }
}
