<?php

namespace App\Filament\Resources\CashBankTransactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CashBankTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('bank_account_id')->relationship('bankAccount', 'account_name')->searchable()->required(),
            TextInput::make('number')->required()->maxLength(50),
            Select::make('type')->options([
                'deposit' => 'Deposit', 'withdrawal' => 'Withdrawal',
                'transfer_in' => 'Transfer In', 'transfer_out' => 'Transfer Out',
            ])->default('deposit')->required(),
            DatePicker::make('transaction_date')->required(),
            TextInput::make('amount')->numeric()->prefix('Rp')->required(),
            Textarea::make('description')->rows(2)->columnSpanFull(),
            Select::make('status')->options([
                'draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled',
            ])->default('draft')->required(),
        ])->columns(2);
    }
}
