<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('businessPartner.name')->label('Supplier')->searchable(),
                TextColumn::make('order_date')->date()->sortable(),
                TextColumn::make('delivery_date')->date(),
                TextColumn::make('grand_total')->money('IDR'),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'approved', 'received' => 'success',
                    'rejected', 'cancelled' => 'danger',
                    'pending_approval', 'submitted', 'partially_received' => 'warning',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'submitted' => 'Submitted', 'pending_approval' => 'Pending Approval',
                    'approved' => 'Approved', 'sent' => 'Sent', 'partially_received' => 'Partially Received',
                    'received' => 'Received', 'closed' => 'Closed', 'cancelled' => 'Cancelled',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->defaultSort('order_date', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
