<?php

namespace App\Filament\Resources\OutgoingPayments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OutgoingPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()->required()
                ->label('Supplier'),
            Select::make('supplier_invoice_id')->relationship('supplierInvoice', 'number')->searchable()
                ->label('Applied to Invoice'),
            Select::make('bank_account_id')->relationship('bankAccount', 'account_name')->searchable(),
            TextInput::make('number')->required()->maxLength(50),
            DatePicker::make('payment_date')->required(),
            Select::make('method')->options([
                'cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'giro' => 'Giro', 'other' => 'Other',
            ])->default('bank_transfer')->required(),
            TextInput::make('reference_number')->maxLength(100),
            TextInput::make('amount')->numeric()->prefix('Rp')->required(),
            Select::make('status')->options([
                'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                'posted' => 'Posted', 'cancelled' => 'Cancelled',
            ])->default('draft')->required(),
        ])->columns(2);
    }
}
