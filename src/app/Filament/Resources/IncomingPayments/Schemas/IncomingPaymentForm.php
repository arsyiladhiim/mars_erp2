<?php

namespace App\Filament\Resources\IncomingPayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IncomingPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()->required()
                ->label('Customer'),
            Select::make('customer_invoice_id')->relationship('customerInvoice', 'number')->searchable()
                ->required()->label('Applied to Invoice'),
            Select::make('bank_account_id')->relationship('bankAccount', 'account_name')->searchable()
                ->required()->label('Bank / Cash Account'),
            TextInput::make('number')->maxLength(50)
                ->disabled(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (string $operation) => $operation !== 'create')
                ->required(fn (string $operation) => $operation === 'edit')
                ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
            DatePicker::make('payment_date')->required(),
            Select::make('method')->options([
                'cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'giro' => 'Giro',
                'credit_card' => 'Credit Card', 'other' => 'Other',
            ])->default('bank_transfer')->required(),
            TextInput::make('reference_number')->maxLength(100),
            TextInput::make('amount')->numeric()->prefix('Rp')->required(),
            Select::make('status')->options([
                'draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
            ])->default('draft')->required()->disabled()->dehydrated()
                ->helperText('Managed via the Post action.'),
        ])->columns(2);
    }
}
