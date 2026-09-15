<?php

namespace App\Filament\Resources\ChartOfAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChartOfAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
            TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
            TextInput::make('name')->required()->maxLength(150),
            Select::make('account_type')->options([
                'asset' => 'Asset', 'liability' => 'Liability', 'equity' => 'Equity',
                'revenue' => 'Revenue', 'expense' => 'Expense', 'cogs' => 'Cost of Goods Sold',
            ])->required(),
            Select::make('parent_id')->relationship('parent', 'name')->searchable()->label('Parent Account'),
            Toggle::make('is_control_account'),
            Select::make('control_type')->options([
                'ar' => 'Accounts Receivable', 'ap' => 'Accounts Payable',
                'inventory' => 'Inventory', 'bank' => 'Bank', 'cash' => 'Cash',
            ])->visible(fn ($get) => $get('is_control_account')),
            Toggle::make('is_tax_account'),
            Toggle::make('requires_cost_center'),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
