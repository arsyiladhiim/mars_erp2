<?php

namespace App\Filament\Resources\OutgoingPayments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OutgoingPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('businessPartner.name')->label('Supplier')->searchable(),
                TextColumn::make('payment_date')->date(),
                TextColumn::make('method')->badge(),
                TextColumn::make('amount')->money('IDR'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                    'posted' => 'Posted', 'cancelled' => 'Cancelled',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
