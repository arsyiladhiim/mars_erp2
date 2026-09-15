<?php

namespace App\Filament\Resources\BusinessPartners\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BusinessPartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')
                ->columns(2)
                ->components([
                    TextInput::make('code')->required()->maxLength(30)->unique(ignoreRecord: true),
                    TextInput::make('name')->required()->maxLength(150),
                    Select::make('type')->options([
                        'customer' => 'Customer', 'supplier' => 'Supplier', 'both' => 'Customer & Supplier',
                    ])->default('customer')->required(),
                    TextInput::make('tax_id')->label('NPWP')->maxLength(30),
                    TextInput::make('email')->email()->maxLength(150),
                    TextInput::make('phone')->tel()->maxLength(30),
                    TextInput::make('contact_person')->maxLength(150),
                    TextInput::make('currency')->default('IDR')->maxLength(3),
                ]),
            Section::make('Commercial Terms')
                ->columns(3)
                ->components([
                    TextInput::make('payment_term_days')->numeric()->suffix('days'),
                    TextInput::make('credit_limit')->numeric()->prefix('Rp'),
                    Select::make('default_tax_code_id')->relationship('defaultTaxCode', 'name')->searchable(),
                ]),
            Section::make('Address')
                ->columns(2)
                ->components([
                    Textarea::make('billing_address')->rows(3),
                    Textarea::make('shipping_address')->rows(3),
                ]),
            Toggle::make('is_active')->default(true),
        ]);
    }
}
