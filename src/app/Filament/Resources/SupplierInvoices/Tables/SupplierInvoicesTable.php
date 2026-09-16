<?php

namespace App\Filament\Resources\SupplierInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('businessPartner.name')->label('Supplier')->searchable(),
                TextColumn::make('invoice_date')->date()->sortable(),
                TextColumn::make('due_date')->date(),
                TextColumn::make('grand_total')->money('IDR'),
                TextColumn::make('paid_amount')->money('IDR')->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                    'posted' => 'Posted', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid',
                    'cancelled' => 'Cancelled',
                ]),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
