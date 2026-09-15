<?php

namespace App\Filament\Resources\ChartOfAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChartOfAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('account_type')->badge(),
                TextColumn::make('parent.name')->label('Parent')->toggleable(),
                IconColumn::make('is_control_account')->boolean()->label('Control'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('account_type')->options([
                    'asset' => 'Asset', 'liability' => 'Liability', 'equity' => 'Equity',
                    'revenue' => 'Revenue', 'expense' => 'Expense', 'cogs' => 'COGS',
                ]),
            ])
            ->defaultSort('code')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
