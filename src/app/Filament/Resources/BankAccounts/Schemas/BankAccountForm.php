<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            Select::make('chart_of_account_id')->relationship('chartOfAccount', 'name')->searchable()
                ->label('GL Account'),
            TextInput::make('account_name')->required()->maxLength(150),
            TextInput::make('account_number')->required()->maxLength(50),
            TextInput::make('bank_name')->required()->maxLength(150),
            TextInput::make('branch')->maxLength(150),
            TextInput::make('currency')->default('IDR')->maxLength(3),
            TextInput::make('opening_balance')->numeric()->prefix('Rp')->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
