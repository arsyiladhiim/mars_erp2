<?php

namespace App\Filament\Resources\CustomerInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('businessPartner.name')->label('Customer')->searchable(),
                TextColumn::make('invoice_date')->date()->sortable(),
                TextColumn::make('due_date')->date(),
                TextColumn::make('grand_total')->money('IDR'),
                TextColumn::make('paid_amount')->money('IDR')->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'paid' => 'success',
                    'overdue', 'cancelled' => 'danger',
                    'partially_paid' => 'warning',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'posted' => 'Posted', 'partially_paid' => 'Partially Paid',
                    'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
